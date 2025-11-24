import { TextAnswer } from './text-answer'

export class TextareaAnswer extends TextAnswer {
    get input() {
        return this.nextPage?.querySelector('textarea')
    }

    slug() {
        return 'textarea'
    }

    jsonGetValue(questionPage) {
        return questionPage.querySelector('textarea').value
    }
}

TextareaAnswer.boot()
