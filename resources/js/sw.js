const siteUrl = self.location.origin
const currentFileName = 'sw.js'

const precacheVersion = 'SW_CACHE_VERSION'
const precacheName = `precache-v${precacheVersion}`
const assetRegex = /\.(?:js|css|mp3|eot|svg|ttf|woff|woff2|png|jpg|jpeg|bmp|gif|ico)$/

const OFFLINE_URL = '/offline'

const precacheURLs = [
    '/js/app.js',
    '/css/app.css',
    '/manifest.json',
    OFFLINE_URL,
]

/**
 * Service Worker install event
 */
self.addEventListener('install', event => {
    self.skipWaiting()
    event.waitUntil(
        caches.open(precacheName).then(cache => {
            return Promise.all(
                precacheURLs.map(url => {
                    fetch(createCacheBustedRequest(url)).then(res => {
                        return cache.put(url, res)
                    })
                })
            )
        })
    )
})

/**
 * Service Worked Activate Event
 */
self.addEventListener('activate', event => {
    event.waitUntil(
        self.clients.claim().then(() => {
            return caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== precacheName) {
                            return caches.delete(cacheName)
                        }
                    })
                )
            })
        })
    )
})

/**
 * Service Worked Activate Event
 */
self.addEventListener('fetch', async event => {
    let originalUrl = event.request.url
    let url = originalUrl.replace(siteUrl, '')

    let exceptUrls = ['/login', '/dashboard']

    if (
        originalUrl.includes(siteUrl) &&
        event.request.method === 'GET' &&
        !originalUrl.includes(currentFileName)
    ) {
        /**
        if (event.request.mode === 'navigate' || event.request.headers.get('accept').includes('text/html')) {
            event.respondWith(
                fetch(url).then(res => {
                    if (originalUrl.match(OFFLINE_URL)) {
                        return caches.open(precacheName).then(cache => {
                            cache.put(url, res.clone())
                            return res
                        })
                    }

                    return res
                }).catch(err => {
                    console.log('error fetching the request', err)
                    return caches.match(OFFLINE_URL)
                })
            )
        }
        */

        if (event.request.url.match(assetRegex)) {
            event.respondWith(
                caches.open(precacheName).then(cache => {
                    return cache
                        .match(event.request.clone())
                        .then(response => {
                            if (response) {
                                return response
                            }

                            return fetch(createCacheBustedRequest(url)).then(
                                res => {
                                    if (url.match(assetRegex)) {
                                        cache.put(url, res.clone())
                                    }

                                    return res
                                }
                            )
                        })
                        .catch(err => {
                            console.log('error fetching assets', err)
                        })
                })
            )
        }
    }
})

/**
 * Cache Buster Request for Cache Reload
 */
function createCacheBustedRequest(url) {
    let request = new Request(url, { cache: 'reload' })
    // See https://fetch.spec.whatwg.org/#concept-request-mode
    // This is not yet supported in Chrome as of M48, so we need to explicitly check to see
    // if the cache: 'reload' option had any effect.
    if ('cache' in request) {
        return request
    }

    // If {cache: 'reload'} didn't have any effect, append a cache-busting URL parameter instead.
    let bustedUrl = new URL(url, self.location.href)
    bustedUrl.search +=
        (bustedUrl.search ? '&' : '') + 'cachebust=' + Date.now()
    return new Request(bustedUrl)
}

;(() => {
    'use strict'

    const WebPush = {
        init() {
            self.addEventListener('push', this.notificationPush.bind(this))

            self.addEventListener(
                'notificationclick',
                this.notificationClick.bind(this)
            )

            self.addEventListener(
                'notificationclose',
                this.notificationClose.bind(this)
            )
        },

        /**
         * Handle notification push event.
         *
         * https://developer.mozilla.org/en-US/docs/Web/Events/push
         *
         * @param {NotificationEvent} event
         */
        notificationPush(event) {
            if (
                !(
                    self.Notification &&
                    self.Notification.permission === 'granted'
                )
            ) {
                return
            }

            // https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData
            if (event.data) {
                event.waitUntil(this.sendNotification(event.data.json()))
            }
        },

        /**
         * Handle notification click event.
         *
         * https://developer.mozilla.org/en-US/docs/Web/Events/notificationclick
         *
         * @param {NotificationEvent} event
         */
        notificationClick(event) {
            let action = event.action

            const promiseChain = clients
                .matchAll({
                    type: 'all',
                    includeUncontrolled: true,
                })
                .then(winClients => {
                    let matchingClient = null

                    for (let i = 0; i < winClients.length; i++) {
                        const winClient = winClients[i]

                        if (
                            winClient.url.includes(action) &&
                            'focus' in winClient
                        ) {
                            return winClient.focus()
                        }
                    }

                    if (action) {
                        if (winClients.length) {
                            let focusClient = winClients[0]
                            focusClient.postMessage({
                                type: 'redirect',
                                url: action,
                            })

                            return focusClient.focus()
                        } else {
                            return clients.openWindow(action)
                        }
                    }

                    return clients.openWindow('/')
                })

            event.notification.close()
            event.waitUntil(promiseChain)
        },

        sendNotifyToRefreshData(data) {
            let typeWithSpecificPage = {
                'schedule-approval': 'any',
                'conversation-message': '/messages',
            }

            clients
                .matchAll({
                    type: 'all',
                    includeUncontrolled: true,
                })
                .then(winClients => {
                    let hasFocusOnMessagePage = false
                    let isAnyWindowFocused = false
                    let focusClient = null

                    if (winClients && winClients.length) {
                        winClients.forEach(c => {
                            if (c && c.focused) {
                                isAnyWindowFocused = true
                                focusClient = c

                                if (
                                    typeWithSpecificPage[data.data.type] ==
                                        'any' ||
                                    c.url.indexOf(
                                        typeWithSpecificPage[data.data.type]
                                    ) >= 1
                                ) {
                                    hasFocusOnMessagePage = true
                                }
                            }
                        })
                    }

                    if (
                        focusClient &&
                        (isAnyWindowFocused || hasFocusOnMessagePage)
                    ) {
                        focusClient.postMessage({
                            type: data.data.type,
                            body: data.data.body,
                        })
                    } else {
                        return self.registration.showNotification(
                            data.title,
                            data
                        )
                    }
                })
        },

        /**
         * Handle notification close event (Chrome 50+, Firefox 55+).
         *
         * https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerGlobalScope/onnotificationclose
         *
         * @param {NotificationEvent} event
         */
        notificationClose(event) {
            self.registration.pushManager
                .getSubscription()
                .then(subscription => {
                    if (subscription) {
                        this.dismissNotification(event, subscription)
                    }
                })
        },

        /**
         * Send notification to the user.
         *
         * https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/showNotification
         *
         * @param {PushMessageData|Object} data
         */
        sendNotification(data) {
            let notificationToRefreshData = [
                'schedule-approval',
                'conversation-message',
            ]
            if (notificationToRefreshData.includes(data.data.type)) {
                return this.sendNotifyToRefreshData(data)
            }

            return self.registration.showNotification(data.title, data)
        },

        /**
         * Send request to server to dismiss a notification.
         *
         * @param  {NotificationEvent} event
         * @param  {String} subscription.endpoint
         * @return {Response}
         */
        dismissNotification({ notification }, { endpoint }) {
            if (!notification.data || !notification.data.id) {
                return
            }

            const data = new FormData()
            data.append('endpoint', endpoint)

            // Send a request to the server to mark the notification as read.
            // fetch(`/notifications/${notification.data.id}/dismiss`, {
            //     method: 'POST',
            //     body: data,
            // })
        },
    }

    WebPush.init()
})()
