import { TextAnswer } from './text-answer'

export class DateAnswer extends TextAnswer {
    get input() {
        return this.nextPage?.querySelector('input')
    }

    onInputEvent(e) {
        const value = e.target.value

        if (value) {
            this.input.closest('.answer').classList.add('has-value')
        } else {
            this.input.closest('.answer').classList.remove('has-value')
        }
    }

    slug() {
        return 'date'
    }

    jsonGetValue(questionPage) {
        return questionPage.querySelector('input').value
    }
}

DateAnswer.boot()
