import { BaseLeadFormAnswer } from './base-answer'

export class ChoicesAnswer extends BaseLeadFormAnswer {
    get selectedChoices() {
        return Array.from(this.nextPage?.querySelectorAll('.choice.checked'))
    }

    slug() {
        return 'choices'
    }

    onDocumentClick(e) {
        if (!this.isChildOfBoundForm(e.target)) return

        if (e.target.closest('.choice')) {
            this.onChoiceClick(e)
        }
    }

    isChoiceMultiple(choice) {
        const answer = choice.closest('.answer')

        return answer.classList.contains('multiple')
    }

    isNextPageMultiple() {
        return this.nextPage.querySelector('.answer').matches('.multiple')
    }

    isNextPageSingleChoice() {
        return !this.isNextPageMultiple()
    }

    isNextPageRequired() {
        if (!super.isNextPageRequired()) return false

        if (this.isNextPageSingleChoice()) return true

        return this.selectedChoices.length == 0
    }

    async onChoiceClick(e) {
        const choice = e.target.closest('.choice')

        if (!choice) return

        if (this.isChoiceMultiple(choice)) {
            this.onMultipleChoiceClick(e)
        } else {
            this.onSingleChoiceClick(e)
        }
    }

    onMultipleChoiceClick(e) {
        const choice = e.target.closest('.choice')

        this.toggleClass(choice, 'checked')

        this.updateRequiredState()
    }

    onBeforePageChange(e) {
        super.onBeforePageChange(e)

        if (!this.shouldPrepareNextPage()) return

        this.updateRequiredState()
    }

    shouldUpdateOKButtonDisabledState() {
        return true
    }

    shouldDisableOKButton() {
        if (this.isNextPageSingleChoice()) {
            return false
        }

        return this.selectedChoices.length === 0
    }

    hideOKButton() {
        if (this.isNextPageMultiple()) return

        return super.hideOKButton()
    }

    async onSingleChoiceClick(e) {
        const choice = e.target.closest('.choice')

        this.resetChoicesInPage(choice.closest('.question-page'))

        await this.flashAnimate(choice)

        choice.classList.add('checked')

        this.requestScrollDown()
    }

    resetChoicesInPage(questionPage) {
        const elems = Array.from(questionPage.querySelectorAll('.choice'))

        elems.forEach((elem) => {
            elem.classList.remove('checked')
        })
    }

    hasSelectedChoice(questionPage) {
        return this.jsonGetValue(questionPage).length > 0
    }

    jsonGetValue(questionPage) {
        return Array.from(
            questionPage.querySelectorAll('.choice.checked .choice-text')
        )
            .map((choice) => {
                return choice.textContent
            })
            .map((s) => s.trim())
            .join(' | ')
    }
}

ChoicesAnswer.boot()
