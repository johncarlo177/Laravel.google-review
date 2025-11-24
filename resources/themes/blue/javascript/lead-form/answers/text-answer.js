import { BaseLeadFormAnswer } from './base-answer'

export class TextAnswer extends BaseLeadFormAnswer {
    boundInputsMap = new Map()

    /**
     * @type {HTMLInputElement}
     */
    get input() {
        return this.nextPage?.querySelector('input')
    }

    slug() {
        return 'text'
    }

    isInputBound() {
        return this.boundInputsMap.has(this.input)
    }

    bindInputEventIfNeeded() {
        if (this.isInputBound()) return

        this.input.addEventListener('input', this.onInput)

        this.input.addEventListener('on-input', this.onInput)

        this.boundInputsMap.set(this.input, true)
    }

    isNextPageRequired() {
        this.bindInputEventIfNeeded()

        if (!super.isNextPageRequired()) return false

        return this.getValue().length === 0
    }

    hideOKButton() {
        return
    }

    getValue() {
        const value = this.input.value

        if (!value) {
            return ''
        }

        return value.trim()
    }

    shouldUpdateOKButtonDisabledState() {
        return true
    }

    shouldDisableOKButton() {
        return this.getValue().length === 0
    }

    onInputEvent(e) {
        const target = e.target

        if (target !== this.input) {
            return
        }

        this.updateRequiredState()
    }

    onInput = (e) => {
        this.onInputEvent(e)
    }

    jsonGetValue(questionPage) {
        return questionPage.querySelector('input').value
    }
}

TextAnswer.boot()
