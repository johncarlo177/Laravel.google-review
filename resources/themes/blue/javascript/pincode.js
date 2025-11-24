import { BaseRenderer } from './base-renderer'
import { Config } from './config'

class PinCodeRenderer extends BaseRenderer {
    _password = ''

    _loading = false

    get pinCodeLength() {
        return this.getAllowedLength()
    }

    get input() {
        return this.$('input.hidden-input')
    }

    get fakeInputs() {
        return this.$$('.fake-input')
    }

    get password() {
        return this._password
    }

    set password(v) {
        if (!this.inputIsAllowed(v)) return

        this._password = v

        this.updateFakeInput()

        this.submitOnCompletion()
    }

    get loading() {
        return this._loading
    }

    set loading(v) {
        this._loading = v

        this.renderLoading()
    }

    get mainDetails() {
        return this.$('.main-details')
    }

    constructor() {
        super()
    }

    onDomContentLoaded() {
        this.input?.addEventListener('paste', this.onPaste)
        this.autoFocus()
    }

    autoFocus() {
        setTimeout(() => {
            this.focusInput()
        }, 300)
    }

    onDocumentClick(e) {
        this.focusInput()
    }

    onDocumentKeyup(e) {
        this.handleDelete(e)

        this.handleType(e)
    }

    onDocumentKeypress(e) {
        this.preventEnterFromSubmittingForm(e)
    }

    onPaste(e) {
        const data = e.clipboardData.getData('text')

        this.password = data.substring(0, this.pinCodeLength)
    }

    isOnlyNumbersAllowed() {
        const value = Config.get('qrcode.pincode_type')

        return value !== 'any'
    }

    getAllowedLength() {
        const value = Config.get('qrcode.pincode_length')

        return !value || isNaN(value) ? 5 : value
    }

    inputIsAllowed(value) {
        if (this.loading) return false

        if (this.isOnlyNumbersAllowed() && !value.match(/^\d*$/)) return false

        if (value.length > this.pinCodeLength) return false

        return true
    }

    handleType(e) {
        if (e.key.length > 1) return

        this.password += e.key
    }

    handleDelete(e) {
        if (e.key !== 'Backspace') {
            return
        }

        this.password = this.password.substring(
            0,
            Math.max(0, this.password.length - 1)
        )
    }

    updateFakeInput() {
        this.resetFakeInputs()

        const p = this.password.split('')

        p.forEach((c, i) => {
            const elem = this.fakeInputs[i]

            this.setFakeInputText(elem, c)

            elem.classList.add('has-value')
        })
    }

    resetFakeInputs() {
        this.fakeInputs.forEach((elem) => {
            this.setFakeInputText(elem, '0')
            elem.classList.remove('has-value')
        })
    }

    setFakeInputText(elem, text) {
        elem.querySelector('.content').innerText = text
    }

    focusInput() {
        this.input.focus()
    }

    async submitOnCompletion() {
        if (this.password.length != this.pinCodeLength) {
            return
        }

        this.loading = true

        this.input.value = this.password

        this.$('form.pincode-form').submit()
    }

    shouldRun() {
        return !!this.$('form.pincode-form')
    }

    renderLoading() {
        if (this.loading) return this.mainDetails.classList.add('loading')

        this.mainDetails.classList.remove('loading')
    }

    preventEnterFromSubmittingForm(e) {
        if (e.key != 'Enter') return

        if (!e.target.closest('.pincode-form')) return

        e.preventDefault()
        e.stopImmediatePropagation()
    }
}

new PinCodeRenderer()
