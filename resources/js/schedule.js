window.createSchedule = function(options, mainComponent) {
    return {
        ...options,

        hasSchedule() {
            if (this.schedule.start && this.schedule.end) {
                if (
                    this.schedule.start != 'XX:XX' &&
                    this.schedule.end != 'XX:XX'
                ) {
                    return true
                }
            }

            return false
        },

        onCheckOutClickEvent($dispatch) {
            $dispatch('show-check-out-modal', {
                date: this.currentDate,
                cause: this.schedule.check_out,
            })
        },

        refreshCheckOut($dates, cause, status) {
            if ($dates.includes(this.currentDate)) {
                this.disableSubmit = true

                this.schedule.start = null
                this.schedule.end = null
                this.schedule.check_out = cause
                this.schedule.status = status
                this.schedule.available = false

                mainComponent.call('refreshCheckout', this.schedule)
                setTimeout(() => {
                    this.disableSubmit = false
                }, 250)
            }
        },

        saveSchedule(fromWhere, force = false) {
            if (!this.disableSubmit || force) {
                mainComponent.call('save', this.schedule, fromWhere)
            }
        },

        onAvailableChange($dispatch) {
            if (!this.disableSubmit) {
                if (this.schedule.available) {
                    this.schedule.check_out = null
                    this.saveSchedule('available')
                } else {
                    this.onCheckOutClickEvent($dispatch)
                }
            }
        },

        saveTimedSchedule() {
            if (
                this.schedule.start &&
                this.schedule.end &&
                this.schedule.start != 'XX:XX' &&
                this.schedule.end != 'XX:XX'
            ) {
                mainComponent.call('save', this.schedule, 'startOrEnd')
            }
        },

        refreshCurrentData(data) {
            this.disableSubmit = true

            this.schedule.uuid = data.uuid
            this.schedule.status = data.status
            this.schedule.end = data.end
            this.schedule.available = data.available
            this.schedule.check_out = data.check_out
            this.schedule.eats_onsite = data.eats_onsite

            setTimeout(() => {
                this.disableSubmit = false
            }, 250)
        },

        onInit($watch, $dispatch) {
            $watch('schedule.available', val =>
                this.onAvailableChange($dispatch)
            )
            $watch('schedule.eats_onsite.breakfast', val =>
                this.saveSchedule('food')
            )
            $watch('schedule.eats_onsite.lunch', val =>
                this.saveSchedule('food')
            )
            $watch('schedule.eats_onsite.dinner', val =>
                this.saveSchedule('food')
            )
            $watch(
                'schedule.start',
                val => val && val != 'XX:XX' && this.saveTimedSchedule()
            )
            $watch(
                'schedule.end',
                val => val && val != 'XX:XX' && this.saveTimedSchedule()
            )
        },
    }
}
