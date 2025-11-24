import { BaseRenderer } from '../base-renderer'
import { QuestionPageRenderer } from './question-page-renderer'

export class BaseLeadFormRenderer extends BaseRenderer {
    static get EVENT_REQUEST_SCROLL_DOWN() {
        return 'lead-form:request-scroll-down'
    }

    static get EVENT_REQUEST_SCROLL_UP() {
        return 'lead-form:request-scroll-up'
    }

    static get EVENT_BEFORE_PAGE_CHANGE() {
        return 'lead-form:before-page-change'
    }

    static get EVENT_AFTER_PAGE_CHANGE() {
        return 'lead-form:after-page-change'
    }

    static get EVENT_SHOULD_ENABLE_NAVIGATE_DOWN() {
        return 'lead-form:should-enable-navigate-down'
    }

    static get EVENT_SHOULD_ENABLE_NAVIGATE_UP() {
        return 'lead-form:should-enable-navigate-up'
    }

    static get EVENT_REQUEST_UPDATE_NAVIGATION_BUTTONS() {
        return 'lead-form:request-update-navigation-buttons'
    }

    static get EVENT_ON_SUCCESS() {
        return 'lead-form:on-success'
    }

    static get EVENT_REQUEST_CLOSE() {
        return 'lead-form:request-close'
    }

    static get EVENT_AFTER_FORM_OPEN() {
        return 'lead-form:after-open'
    }

    static boot() {
        const forms = Array.from(document.querySelectorAll('.lead-form'))

        forms.forEach((f) => {
            if (!this.shouldBootOnForm(f)) return

            const renderer = new this(f)

            f.renderers = f.renderers ?? []

            f.renderers = [...f.renderers, renderer]
        })
    }

    static shouldBootOnForm(form) {
        return true
    }

    constructor(formElement) {
        super()

        this.formElement = formElement
    }

    getRenderer(Type) {
        return this.formElement.renderers.find((r) => r instanceof Type)
    }

    get form() {
        return this.formElement
    }

    f$$(selector) {
        return Array.from(this.formElement.querySelectorAll(selector))
    }

    f$(selector) {
        return this.formElement.querySelector(selector)
    }

    shouldRun() {
        return true
    }

    getLeadFormId() {
        return this.form.dataset.id
    }

    isFullPage() {
        return this.form.classList.contains('full-page')
    }

    isChildOfBoundForm(elem) {
        if (!elem) return false

        return elem.closest('.lead-form') === this.formElement
    }

    async flashAnimate(elem) {
        elem.classList.add('flash-animate')

        await this.animationPromise(elem)

        elem.classList.remove('flash-animate')
    }

    requestScrollDown() {
        document.dispatchEvent(
            new CustomEvent(QuestionPageRenderer.EVENT_REQUEST_SCROLL_DOWN)
        )
    }
}
