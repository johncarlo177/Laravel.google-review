import { TextAnswer } from './text-answer'

export class PhoneAnswer extends TextAnswer {
    boundInputsMap = new Map()

    get input() {
        return this.nextPage?.querySelector('qrcg-mobile-input')
    }

    slug() {
        return 'phone'
    }

    getValue() {
        const valueObject = this.input.value

        return this.getFormattedMobileNumber(valueObject)
    }

    getFormattedMobileNumber(valueObject) {
        const { mobile_number, calling_code } = valueObject ?? {}

        if (!mobile_number) {
            return ''
        }

        const value = '+' + calling_code + (mobile_number ?? '')

        if (!value) {
            return ''
        }

        return value.trim()
    }

    jsonGetValue(questionPage) {
        const valueObject =
            questionPage.querySelector('qrcg-mobile-input').value

        return this.getFormattedMobileNumber(valueObject)
    }
}

PhoneAnswer.boot()
