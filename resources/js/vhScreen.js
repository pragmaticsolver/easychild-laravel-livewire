function updateVhValue() {
    let vh = window.innerHeight * 0.01
    document.documentElement.style.setProperty('--vh', `${vh}px`)
}

window.addEventListener('orientationchange', function() {
    updateVhValue()
})
window.addEventListener('resize', function() {
    updateVhValue()
})

document.addEventListener('DOMContentLoaded', function() {
    if (location.href.match('/offline') && navigator.onLine) {
        location.replace('/')
    }

    updateVhValue()
})
