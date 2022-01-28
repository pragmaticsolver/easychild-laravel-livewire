window.TailwindComponents = {
    customSelect(options) {
        console.log(options)
        return {
            init() {
                this.optionCount = this.$refs.listbox.children.length
                this.chooseByValue(this.value)

                this.$watch('selected', value => {
                    if (!this.open) return

                    if (this.selected === null) {
                        this.activeDescendant = ''
                        return
                    }

                    this.activeDescendant = this.$refs.listbox.children[
                        this.selected
                    ].id
                })
            },
            chooseByValue(value) {
                this.selectItems.forEach((item, index) => {
                    if (item[this.valueKey] == value) {
                        this.value = item[this.valueKey]
                        this.selected = index
                    }
                })
            },
            chooseByIndex(idx) {
                this.selected = idx
                this.selectItems.forEach((item, index) => {
                    if (idx == index) {
                        this.value = item[this.valueKey]
                    }
                })
            },
            selectItems: [],
            valueKey: 'value',
            textKey: 'text',
            activeDescendant: null,
            optionCount: null,
            open: false,
            selected: null,
            value: null,
            choose(option) {
                this.chooseByValue(option)
                this.open = false
            },
            onButtonClick() {
                if (this.open) return
                // this.chooseByValue(this.value)
                this.open = true
                this.$nextTick(() => {
                    this.$refs.listbox.focus()
                    this.$refs.listbox.children[this.selected].scrollIntoView({
                        block: 'nearest',
                    })
                })
            },
            onOptionSelect() {
                if (this.selected != null) {
                    this.chooseByIndex(this.selected)
                }
                this.open = false
                this.$refs.button.focus()
            },
            onEscape() {
                this.open = false
                this.$refs.button.focus()
            },
            onArrowUp() {
                this.selected =
                    this.selected - 1 < 0
                        ? this.optionCount - 1
                        : this.selected - 1
                this.$refs.listbox.children[this.selected].scrollIntoView({
                    block: 'nearest',
                })
            },
            onArrowDown() {
                this.selected =
                    this.selected + 1 > this.optionCount - 1
                        ? 1
                        : this.selected + 1
                this.$refs.listbox.children[this.selected].scrollIntoView({
                    block: 'nearest',
                })
            },
            ...options,
        }
    },
}
