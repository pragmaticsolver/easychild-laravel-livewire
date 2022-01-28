@php
    $vapIdPublic = config('webpush.vapid.public_key');
@endphp

<div
    x-data="registerServiceWorker()"
    x-init="
        mounted(@this, '{{ $vapIdPublic }}');
        $watch('pushNotificationCheck', (value) => {
            if (! stopWatcher) {
                toggleSubscription(value);
            }
        });
    "
>
    {{-- <div style="display: none;" x-show="isPushEnabled">
        <strong>{{ trans('users.subscribed') }}</strong> <a class="underline text-blue-500 hover:no-underline" href="#" x-on:click.prevent="unsubscribe()">{{ trans('users.unsubscribe') }}</a>
    </div>

    <div style="display: none;" x-show="!pushButtonDisabled && !isPushEnabled">
        <strong>{{ trans('users.not-subscribed') }}</strong> <a class="underline text-blue-500 hover:no-underline" href="#" x-on:click.prevent="subscribe()">{{ trans('users.subscribe') }}</a>
    </div> --}}

    <div class="relative pt-2 flex">
        <div class="flex items-center h-5">
            <input
                id="notification-push"
                x-model="pushNotificationCheck"
                type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out cursor-pointer" :disabled="notSupported || subProcessWorking"
            >
        </div>

        <label for="notification-push" class="ml-3 flex flex-col cursor-pointer">
            <span class="block text-sm leading-5 font-medium">
                {{ trans('users.notification.push_notification') }}
            </span>
        </label>
    </div>
</div>

@push('scripts')
    <script>
        window.registerServiceWorker = function() {
            return {
                subProcessWorking: false,
                notSupported: false,
                isPushEnabled: false,
                pushNotificationCheck: false,
                pushButtonDisabled: true,
                component: null,
                stopWatcher: false,
                vapidPublicKey: null,
                mounted(component, vapidPublicKey) {
                    this.component = component
                    this.vapidPublicKey = vapidPublicKey

                    this.initialiseServiceWorker()
                },
                initialiseServiceWorker() {
                    this.subProcessWorking = true

                    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
                        let msg = '{{ trans("users.notification.not-supported") }}'
                        this.notSupported = true
                        window
                            .toastify({
                                text: msg,
                                type: 'error',
                                close: true,
                                duration: 3500,
                            })
                            .showToast()

                        return
                    }

                    if (Notification.permission === 'denied') {
                        let msg = '{{ trans("users.notification.disabled") }}'
                        window
                            .toastify({
                                text: msg,
                                type: 'error',
                                close: true,
                                duration: 3500,
                            })
                            .showToast()

                        return
                    }

                    if (!('PushManager' in window)) {
                        let msg = '{{ trans("users.notification.push-not-supported") }}'
                        window
                            .toastify({
                                text: msg,
                                type: 'error',
                                close: true,
                                duration: 3500,
                            })
                            .showToast()

                        return
                    }

                    navigator.serviceWorker.ready.then(res => {
                        res.pushManager.getSubscription()
                            .then(subscription => {
                                this.pushButtonDisabled = false

                                if (!subscription) {
                                    this.subProcessWorking = false

                                    return
                                }

                                this.updateSubscription(subscription)
                                this.isPushEnabled = true

                                this.stopWatcher = true
                                this.pushNotificationCheck = this.isPushEnabled

                                setTimeout(() => {
                                    this.stopWatcher = false
                                }, 500)

                                this.subProcessWorking = false
                            })
                            .catch(e => {
                                let msg = '{{ trans("users.notification.error-getting-sub") }}'
                                window
                                    .toastify({
                                        text: msg,
                                        type: 'error',
                                        close: true,
                                        duration: 3500,
                                    })
                                    .showToast()
                            })
                    })
                },
                updateSubscription(subscription) {
                    if (! this.component) {
                        return
                    }

                    const key = subscription.getKey('p256dh')
                    const token = subscription.getKey('auth')
                    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

                    const data = {
                        endpoint: subscription.endpoint,
                        publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                        authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                        contentEncoding
                    }

                    this.component.call('updateSubscription', data)
                },
                deleteSubscription(subscription) {
                    if (! this.component) {
                        return
                    }

                    this.component.call('removeSubscription', {endpoint: subscription.endpoint});
                },
                toggleSubscription(status) {
                    if (status) {
                        this.subscribe()
                    } else {
                        this.unsubscribe()
                    }
                },
                subscribe() {
                    this.subProcessWorking = true

                    navigator.serviceWorker.ready.then(registration => {
                        const options = { userVisibleOnly: true }
                        const vapidPublicKey = this.vapidPublicKey

                        if (vapidPublicKey) {
                            options.applicationServerKey = this.urlBase64ToUint8Array(vapidPublicKey)
                        }

                        registration.pushManager.subscribe(options)
                        .then(subscription => {
                            this.isPushEnabled = true
                            this.pushButtonDisabled = false

                            // this.stopWatcher = true
                            // this.pushNotificationCheck = this.isPushEnabled

                            // setTimeout(() => {
                            //     this.stopWatcher = false
                            // }, 500)

                            this.updateSubscription(subscription)
                            this.subProcessWorking = false
                        })
                        .catch(e => {
                            if (Notification.permission === 'denied') {
                                let msg = '{{ trans("users.notification.permission-denied") }}'
                                window
                                    .toastify({
                                        text: msg,
                                        type: 'error',
                                        close: true,
                                        duration: 3500,
                                    })
                                    .showToast()

                                this.pushButtonDisabled = true
                            } else {
                                let msg = '{{ trans("users.notification.unable-to-sub-push") }}'
                                window
                                    .toastify({
                                        text: msg,
                                        type: 'error',
                                        close: true,
                                        duration: 3500,
                                    })
                                    .showToast()

                                this.pushButtonDisabled = false
                            }

                            this.subProcessWorking = false
                        })
                    })
                },
                unsubscribe() {
                    this.subProcessWorking = true

                    navigator.serviceWorker.ready.then(registration => {
                        registration.pushManager.getSubscription().then(subscription => {
                            if (!subscription) {
                                this.isPushEnabled = false
                                this.pushButtonDisabled = false

                                // this.stopWatcher = true
                                // this.pushNotificationCheck = this.isPushEnabled

                                // setTimeout(() => {
                                //     this.stopWatcher = false
                                // }, 500)
                                this.subProcessWorking = false

                                return
                            }

                            subscription.unsubscribe().then(() => {
                                this.deleteSubscription(subscription)

                                this.isPushEnabled = false
                                this.pushButtonDisabled = false

                                this.subProcessWorking = false

                                // this.stopWatcher = true
                                // this.pushNotificationCheck = this.isPushEnabled

                                // setTimeout(() => {
                                //     this.stopWatcher = false
                                // }, 500)
                            }).catch(e => {
                                let msg = '{{ trans("users.notification.un-sub-error") }}'
                                window
                                    .toastify({
                                        text: msg,
                                        type: 'error',
                                        close: true,
                                        duration: 3500,
                                    })
                                    .showToast()

                                this.pushButtonDisabled = false
                                this.subProcessWorking = false
                            })
                        }).catch(e => {
                            let msg = '{{ trans("users.notification.error-while-sub") }}'
                            window
                                .toastify({
                                    text: msg,
                                    type: 'error',
                                    close: true,
                                    duration: 3500,
                                })
                                .showToast()

                            this.subProcessWorking = false
                        })
                    })
                },
                urlBase64ToUint8Array (base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4)
                    const base64 = (base64String + padding)
                        .replace(/\-/g, '+')
                        .replace(/_/g, '/')

                    const rawData = window.atob(base64)
                    const outputArray = new Uint8Array(rawData.length)

                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i)
                    }

                    return outputArray
                }
            }
        }
    </script>
@endpush
