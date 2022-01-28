require('./turbolinks-adapter')
require('./bootstrap')
require('./flatpickr')
require('./tailwind-components')
require('./vhScreen')

require('./schedule')

window.livewire.on('server-message', data => {
    window
        .toastify({
            text: data.message,
            type: data.type,
            close: true,
            duration: 3500,
        })
        .showToast()
})

window.clearSavedImages = function() {
    return {
        onInit() {
            localStorage.removeItem('user-avatar')
            localStorage.removeItem('org-avatar')
        },
    }
}

let date = new Date()
date.setMinutes(date.getMinutes() + 5)

let lastTimeMessagesUpdated = date.valueOf()
window.onfocus = function() {
    let messagesUrl = '/messages'

    if (location.href.includes(messagesUrl)) {
        if (lastTimeMessagesUpdated) {
            let now = new Date().valueOf()

            if (lastTimeMessagesUpdated > now) return
        }

        let date = new Date()
        date.setMinutes(date.getMinutes() + 5)
        lastTimeMessagesUpdated = date.valueOf()

        window.livewire.emit('refreshChatSidebarThreads')
    }
}

window.selectText = function(el) {
    var sel, range

    if (window.getSelection && document.createRange) {
        //Browser compatibility
        sel = window.getSelection()
        if (sel.toString() == '') {
            //no text selection
            window.setTimeout(function() {
                range = document.createRange() //range object
                range.selectNodeContents(el) //sets Range
                sel.removeAllRanges() //remove all ranges from selection
                sel.addRange(range) //add Range to a Selection.
            }, 1)
        }
    } else if (document.selection) {
        //older ie
        sel = document.selection.createRange()
        if (sel.text == '') {
            //no text selection
            range = document.body.createTextRange() //Creates TextRange object
            range.moveToElementText(el) //sets Range
            range.select() //make selection.
        }
    }
}

window.userAvatarFunc = function(avatar, imgKey = 'user-avatar') {
    return {
        visibleAvatar: avatar,
        onUserAvatarUpdate($event) {
            let newAvatar = $event.detail

            if (typeof $event.detail == 'string' && $event.detail) {
                this.visibleAvatar = $event.detail
            } else {
                this.visibleAvatar = null
            }

            localStorage.setItem(imgKey, this.visibleAvatar)
        },
        onInit() {
            let savedAvatar = localStorage.getItem(imgKey)

            if (savedAvatar) {
                this.visibleAvatar = savedAvatar
            } else {
                this.visibleAvatar = avatar
            }
        },
    }
}

window.imageUploader = function(defaultImage, width, height, imgKey) {
    return {
        defaultImage: defaultImage,
        image: null,
        modelId: 'model-id-',
        croppie: null,
        width: width,
        height: height,
        mounted() {
            this.modelId = this.modelId + Math.floor(Math.random(1) * 1000)

            this.image = null

            if (imgKey && imgKey != 'null') {
                let localStorageImage = localStorage.getItem(imgKey)

                if (
                    localStorageImage &&
                    localStorageImage != this.defaultImage
                ) {
                    this.defaultImage = localStorageImage
                }
            }
        },
        rotateImage() {
            this.croppie.rotate(90)
        },
        cropImage($dispatch) {
            this.croppie.result('base64').then(img => {
                $dispatch('avatar-input-changed', img)
                $dispatch('input', img)
                this.image = img

                this.croppie.destroy()
                this.croppie = null
            })
        },
        onChanged(files) {
            if (files.length) {
                let reader = new FileReader()

                reader.onload = e => {
                    this.image = e.target.result
                    this.croppie = new window.croppie(this.$refs.image, {
                        url: e.target.result,
                        enableOrientation: true,
                        viewport: {
                            width: this.width,
                            height: this.height,
                            type: 'square',
                        },
                        boundary: {
                            width: this.width + 100,
                            height: this.height + 100,
                        },
                    })
                }

                reader.readAsDataURL(files[0])
            }
        },
        resetCropper() {
            this.image = null
            this.croppie.destroy()
            this.croppie = null
        },
    }
}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', evt => {
        if (evt.data.type === 'redirect') {
            location.replace(evt.data.url)
        }

        if (evt.data.type === 'schedule-approval') {
            window.livewire.emitTo(
                'notifications.index',
                'refreshNotificationBox'
            )

            window
                .toastify({
                    text: evt.data.body,
                    type: 'success',
                    close: true,
                    duration: 3500,
                })
                .showToast()
        }

        if (evt.data.type === 'conversation-message') {
            if (location.href.indexOf('/messages') >= 1) {
                if (evt.data.body.model_id) {
                    window.livewire.emitTo(
                        'messages.sidebar',
                        `newMessageReceivedOnConversation`,
                        evt.data.body.model_id,
                        evt.data.body.message_id
                    )
                }
            } else {
                window
                    .toastify({
                        text:
                            evt.data.body.text1 + '<br>' + evt.data.body.text2,
                        type: 'success',
                        close: true,
                        duration: 3500,
                    })
                    .showToast()
            }
        }
    })
}

window.pwaLoginManager = function(logoutSession, $wire, vapidPublicKey) {
    if (logoutSession) {
        window.localStorage.removeItem('user-token')
    }

    return {
        vapidPublicKey,
        currentEndPoints: [],
        messages: [],
        dispatch: null,
        onMounted(authCheck, $messages, $currentEndPoints = [], $dispatch) {
            this.messages = $messages
            this.currentEndPoints = $currentEndPoints
            this.dispatch = $dispatch
            this.initLoginCheck(authCheck)

            if (authCheck) {
                if (this.messages.isPushEnabled) {
                    this.initialiseServiceWorker('enabled')
                } else {
                    let checkedTime = localStorage.getItem('push-check-time')

                    if (checkedTime) {
                        let now = new Date().valueOf()

                        if (checkedTime > now) {
                            return
                        }
                    }

                    this.initialiseServiceWorker()
                }
            }
        },
        confirmUserLogout($event, isUser, $dispatch) {
            if (this.isInPWAMode() && isUser) {
                $dispatch('pwa-login-manager-confirm-modal-open', $event)
            } else {
                this.afterUserConfirmLogout()
            }
        },
        userLogoutNow(subscription = null) {
            window.localStorage.removeItem('push-check-time')

            $wire.userLogout(subscription)
        },
        serviceWorkerToggle(state) {
            if (typeof state == 'boolean') {
                this.messages.isPushEnabled = state
            } else {
                this.messages.isPushEnabled = false
                state = false
            }

            if (state) {
                this.initialiseServiceWorker('enabled')
            } else {
                this.initialiseServiceWorker('disabled')
            }
        },
        enablePushNotification() {
            navigator.serviceWorker.ready.then(registration => {
                const options = { userVisibleOnly: true }
                const vapidPublicKey = this.vapidPublicKey

                if (vapidPublicKey) {
                    options.applicationServerKey = this.urlBase64ToUint8Array(
                        vapidPublicKey
                    )
                }

                registration.pushManager
                    .subscribe(options)
                    .then(subscription => {
                        this.updatePushNotification(subscription)
                    })
                    .catch(e => {
                        if (Notification.permission === 'denied') {
                            let msg = this.messages['permission-denied']

                            window
                                .toastify({
                                    text: msg,
                                    type: 'error',
                                    close: true,
                                    duration: 3500,
                                })
                                .showToast()
                        } else {
                            let msg = this.messages['unable-to-sub-push']
                            window
                                .toastify({
                                    text: msg,
                                    type: 'error',
                                    close: true,
                                    duration: 3500,
                                })
                                .showToast()
                        }
                    })
            })
        },
        updatePushNotification(subscription) {
            const key = subscription.getKey('p256dh')
            const token = subscription.getKey('auth')
            const contentEncoding = (PushManager.supportedContentEncodings || [
                'aesgcm',
            ])[0]

            const data = {
                endpoint: subscription.endpoint,
                publicKey: key
                    ? btoa(String.fromCharCode.apply(null, new Uint8Array(key)))
                    : null,
                authToken: token
                    ? btoa(
                          String.fromCharCode.apply(null, new Uint8Array(token))
                      )
                    : null,
                contentEncoding,
            }

            $wire.call('updateSubscription', data)
        },
        disablePushNotification(subscription) {
            subscription.unsubscribe().then(() => {
                $wire.call('removeSubscription', subscription)
            })
        },
        removeAllSubscription() {
            $wire.call('removeSubscription')
        },
        checkForEnabledSubscription(subscription) {},
        afterUserConfirmLogout() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(registration => {
                    registration.pushManager
                        .getSubscription()
                        .then(subscription => {
                            if (!subscription) {
                                return this.userLogoutNow(subscription)
                            }

                            subscription
                                .unsubscribe()
                                .then(() => {
                                    this.userLogoutNow(subscription)
                                })
                                .catch(e => {
                                    this.userLogoutNow(subscription)
                                })
                        })
                        .catch(e => {
                            this.userLogoutNow()
                        })
                })
            } else {
                return this.userLogoutNow()
            }
        },
        initLoginCheck(authCheck) {
            if (authCheck && this.isInPWAMode()) {
                let tokenOnStorage = window.localStorage.getItem('user-token')

                if (!tokenOnStorage) {
                    $wire.userAccessToken().then(res => {
                        res && window.localStorage.setItem('user-token', res)
                    })
                }
            }

            if (this.isInPWAMode() && !authCheck) {
                let tokenOnStorage = window.localStorage.getItem('user-token')

                if (tokenOnStorage) {
                    $wire.call('loginUserUsingToken', tokenOnStorage)
                }
            }
        },
        isInPWAMode() {
            return (
                window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone ||
                document.referrer.includes('android-app://')
            )
        },
        initialiseServiceWorker(checkType = 'check') {
            if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
                window
                    .toastify({
                        text: this.messages['not-supported'],
                        type: 'error',
                        close: true,
                        duration: 3500,
                    })
                    .showToast()

                return
            }

            if (Notification.permission === 'denied') {
                window
                    .toastify({
                        text: this.messages['permission-denied'],
                        type: 'error',
                        close: true,
                        duration: 3500,
                    })
                    .showToast()

                return
            }

            if (!('PushManager' in window)) {
                window
                    .toastify({
                        text: this.messages['push-not-supported'],
                        type: 'error',
                        close: true,
                        duration: 3500,
                    })
                    .showToast()

                return
            }

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(res => {
                    res.pushManager.getSubscription().then(subscription => {
                        if (checkType == 'enabled') {
                            if (subscription) {
                                if (
                                    !this.currentEndPoints.includes(
                                        subscription.endpoint
                                    )
                                ) {
                                    this.updatePushNotification(subscription)
                                }
                            } else {
                                this.enablePushNotification(subscription)
                            }
                        } else if (checkType == 'disabled') {
                            if (subscription) {
                                this.disablePushNotification(subscription)
                            } else {
                                this.removeAllSubscription()
                            }
                        } else {
                            if (subscription) {
                                if (this.isPushEnabled) {
                                    this.updatePushNotification(subscription)
                                } else {
                                    this.disablePushNotification(subscription)
                                }
                            } else {
                                return this.dispatch(
                                    'check-push-enabled-confirm-modal-open',
                                    {
                                        title: this.messages['push-confirm'][
                                            'title'
                                        ],
                                        description: this.messages[
                                            'push-confirm'
                                        ]['description'],
                                        cancelText: this.messages[
                                            'push-confirm'
                                        ]['cancel-text'],
                                        confirmText: this.messages[
                                            'push-confirm'
                                        ]['confirm-text'],
                                        event: 'confirmed-route-to-profile',
                                        cancelEvent:
                                            'canceled-route-to-profile',
                                        uuid: null,
                                    }
                                )
                            }

                            this.setLaterDateForPushEnabledCheck()
                        }
                    })
                })
            }
        },
        setLaterDateForPushEnabledCheck(onlyForTwoMinutes = false) {
            let date = new Date()

            if (onlyForTwoMinutes) {
                date.setMinutes(date.getMinutes() + 2)
            } else {
                date.setDate(date.getDate() + 1)
            }

            localStorage.setItem('push-check-time', date.valueOf())
        },
        onConfirmedRouteToProfile(component) {
            this.setLaterDateForPushEnabledCheck(true)

            if (!this.messages.isProfileRoute) {
                component.call('routeToProfile')
            }
        },
        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/')

            const rawData = window.atob(base64)
            const outputArray = new Uint8Array(rawData.length)

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i)
            }

            return outputArray
        },
    }
}

window.beforeInstallPromptData = null
window.appAlreadyInstalled = false
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault()
    window.beforeInstallPromptData = e
})

window.addEventListener('appinstalled', () => {
    window.appAlreadyInstalled = true
})

window.serviceWorkerAppInstall = function() {
    return {
        messages: [],
        deferredPrompt: null,
        needsInstall: false,
        barDisabled: null,
        swCheckKey: 'disable-service-worker-app-install',
        onMounted($messages) {
            this.messages = $messages

            setTimeout(() => {
                this.checkForDisabledBar()

                this.deferredPrompt = window.beforeInstallPromptData

                if (window.appAlreadyInstalled) {
                    this.needsInstall = false
                }

                this.checkIfRunningInAppMode()
            }, 500)
        },
        checkIfRunningInAppMode() {
            if (navigator.standalone) {
                console.log('Launched: Installed (iOS)')
            } else if (matchMedia('(display-mode: standalone)').matches) {
                console.log('Launched: Installed')
            } else {
                console.log('Not in Installed mode')
                this.needsInstall = true
            }
        },
        checkForDisabledBar() {
            let disabledDate = localStorage.getItem(this.swCheckKey)
            this.barDisabled = false

            if (disabledDate) {
                let now = new Date().valueOf()

                if (disabledDate > now) {
                    this.barDisabled = true
                }
            }
        },
        installLater() {
            window
                .toastify({
                    text: this.messages['install-later-msg'],
                    type: 'success',
                    close: true,
                    duration: 3500,
                })
                .showToast()

            this.disableInstallBar()
        },
        disableInstallBar() {
            let date = new Date()
            date.setDate(date.getDate() + 7)

            localStorage.setItem(this.swCheckKey, date.valueOf())
            this.checkForDisabledBar()
        },
        askForAppInstall() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt()

                this.deferredPrompt.userChoice.then(result => {
                    if (result.outcome === 'accepted') {
                        this.barDisabled = true

                        window
                            .toastify({
                                text: this.messages['app-installed'],
                                type: 'success',
                                close: true,
                                duration: 3500,
                            })
                            .showToast()
                    } else {
                        window
                            .toastify({
                                text: this.messages['install-declined'],
                                type: 'error',
                                close: true,
                                duration: 3500,
                            })
                            .showToast()
                    }
                })
            }
        },
    }
}
