import { BaseLeadFormRenderer } from '../base-lead-form-renderer'
import { QuestionPageRenderer } from '../question-page-renderer'

export class BaseLeadFormAnswer extends BaseLeadFormRenderer {
    constructor(formElem) {
        super(formElem)

        document.addEventListener(
            BaseLeadFormAnswer.EVENT_BEFORE_PAGE_CHANGE,
            this.onBaseBeforePageChange
        )

        document.addEventListener(
            BaseLeadFormAnswer.EVENT_SHOULD_ENABLE_NAVIGATE_DOWN,
            this.onShouldEnableNavigationDown
        )

        document.addEventListener(
            QuestionPageRenderer.EVENT_DATA_VALIDATION,
            this.onDataValidationRequested
        )
    }

    get okButton() {
        return this.nextPage?.querySelector('.ok-button .button')
    }

    shouldRun() {
        return this.f$(`.answer.${this.slug()}`)
    }

    slug() {}

    /**
     *
     * @param {CustomEvent} e
     */
    onDataValidationRequested = (e) => {
        const { page, formElement } = e.detail

        if (!this.shouldRunOnQuestionPage(page)) {
            return
        }

        if (formElement != this.formElement) {
            return
        }

        if (!this.validateData()) {
            e.preventDefault()
        }
    }

    validateData() {
        return true
    }

    onBaseBeforePageChange = (e) => {
        this.onBeforePageChange(e)
    }

    onBeforePageChange(e) {
        const { currentPage, nextPage } = e.detail

        if (!this.isChildOfBoundForm(nextPage)) {
            return
        }

        this.currentPage = currentPage

        this.nextPage = nextPage

        if (this.shouldPrepareNextPage()) {
            this.prepareNextPage()
            this.updateRequiredState()
        }
    }

    shouldRunOnQuestionPage(questionPage) {
        return !!questionPage?.querySelector(`.${this.slug()}`)
    }

    shouldPrepareNextPage() {
        return this.shouldRunOnQuestionPage(this.nextPage)
    }

    prepareNextPage() {
        this.synkOKButton()
    }

    synkOKButton() {
        if (this.isNextPageRequired()) {
            this.hideOKButton()
        } else {
            this.showOKButton()
        }
    }

    hideOKButton() {
        if (!this.okButton) return

        if (this.okButton.closest('.ok-button').matches('.submit')) return

        this.okButton.style = 'display: none'
    }

    showOKButton() {
        if (!this.okButton) return

        this.okButton.style = ''
    }

    isNextPageRequired() {
        return this.nextPage.matches('.required')
    }

    onShouldEnableNavigationDown = (e) => {
        if (!this.shouldPrepareNextPage()) return

        if (this.isNextPageRequired()) {
            e.preventDefault()
        }
    }

    requestUpdateNavigationButtons() {
        document.dispatchEvent(
            new CustomEvent(
                BaseLeadFormAnswer.EVENT_REQUEST_UPDATE_NAVIGATION_BUTTONS
            )
        )
    }

    updateRequiredState() {
        this.requestUpdateNavigationButtons()
        this.prepareNextPage()
        this.updateOKButtonDisabledState()
    }

    shouldUpdateOKButtonDisabledState() {
        return false
    }

    shouldDisableOKButton() {
        return false
    }

    updateOKButtonDisabledState() {
        if (!this.shouldUpdateOKButtonDisabledState()) {
            return
        }

        if (!this.okButton) return

        if (this.shouldDisableOKButton() && this.isNextPageRequired()) {
            this.okButton.setAttribute('disabled', 'true')
        } else {
            this.okButton.removeAttribute('disabled')
        }
    }

    formatText(text) {
        if (typeof text !== 'string') return text

        return text.replace(/\s+/g, ' ').replace(/^ /, '')
    }

    jsonGetQuestion(questionPage) {
        return questionPage.querySelector('.question-text').textContent
    }

    jsonGetDescription(questionPage) {
        return questionPage.querySelector('.question-description')?.textContent
    }

    jsonGetValue(questionPage) {
        throw new Error('jsonGetValue is not implemented in ' + this.slug())
    }

    toJson(questionPage) {
        if (!this.shouldRunOnQuestionPage(questionPage)) {
            return null
        }

        const data = {
            question: this.jsonGetQuestion(questionPage),
            description: this.jsonGetDescription(questionPage),
            value: this.jsonGetValue(questionPage),
        }

        return Object.keys(data).reduce((result, key) => {
            result[key] = this.formatText(data[key])

            return result
        }, {})
    }
}
