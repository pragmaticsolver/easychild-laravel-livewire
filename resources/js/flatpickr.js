import flatpickr from 'flatpickr'
require('flatpickr/dist/l10n/de')
flatpickr.l10ns.default.rangeSeparator = ' ~ '
flatpickr.l10ns.en.rangeSeparator = ' ~ '
flatpickr.l10ns.de.rangeSeparator = ' ~ '

window.rangeFlatPicker = function(
    visibleFormat,
    dbFormat,
    locale,
    startDate,
    endDate,
    options
) {
    return {
        dateFormat: dbFormat,
        altFormat: visibleFormat,
        init($el, $dispatch) {
            let lcDbFormat = this.dateFormat
            let lcVisibleFormat = this.altFormat

            let defaultDate = []

            if (startDate && endDate) {
                defaultDate = [startDate, endDate]
            }

            let opts = {
                enableTime: false,
                altInput: true,
                locale,
                defaultDate,
                dateFormat: lcDbFormat,
                altFormat: lcVisibleFormat,
                disableMobile: true,
                mode: 'range',
                onChange: (selectedDates, dateStr, instance) => {
                    if (dateStr && dateStr.includes('~')) {
                        $dispatch('date-range-changed', dateStr)
                    }
                },
            }

            this.instance = flatpickr($el, opts)
        },
        ...options,
    }
}

window.singleFlatPicker = function(visibleFormat, dbFormat, locale, options) {
    return {
        dateFormat: dbFormat,
        altFormat: visibleFormat,
        notEnableTime: true,
        init($el, $watch) {
            this.setInstance($el)

            $watch('value', val => this.instance.setDate(val, false))
            $watch('notEnableTime', val => this.setInstance($el))
        },
        setInstance($el) {
            let lcDbFormat = this.dateFormat
            let lcVisibleFormat = this.altFormat

            if (!this.notEnableTime) {
                lcDbFormat = `${lcDbFormat} H:i`
                lcVisibleFormat = `${lcVisibleFormat} H:i`
            }

            let options = {
                enableTime: !this.notEnableTime,
                altInput: true,
                locale,
                dateFormat: lcDbFormat,
                altFormat: lcVisibleFormat,
                disableMobile: true,
            }

            this.instance = flatpickr($el, options)
        },
        ...options,
    }
}
