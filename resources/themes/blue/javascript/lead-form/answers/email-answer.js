import { TextAnswer } from './text-answer'

export class EmailAnswer extends TextAnswer {
    slug() {
        return 'email'
    }

    validateData() {
        if (!this.input) {
            return true
        }

        const result = !this.input?.validity?.typeMismatch

        if (!result) {
            window.showToast('You will have to enter a valid email address')
        }

        return result
    }
}

EmailAnswer.boot()
