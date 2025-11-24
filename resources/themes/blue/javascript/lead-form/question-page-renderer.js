import { BaseLeadFormRenderer } from './base-lead-form-renderer'
import { PageScroller } from './page-scroller'

export class QuestionPageRenderer extends BaseLeadFormRenderer {
    static EVENT_DATA_VALIDATION = 'question-page-renderer:data-validation'

    constructor(formElement) {
        super(formElement)

        if (!this.shouldRun()) return

        this.currentPage = 0

        document.addEventListener(
            QuestionPageRenderer.EVENT_REQUEST_SCROLL_DOWN,
            this.scrollDown
        )

        document.addEventListener(
            QuestionPageRenderer.EVENT_REQUEST_SCROLL_UP,
            this.scrollUp
        )

        document.addEventListener(
            QuestionPageRenderer.EVENT_REQUEST_UPDATE_NAVIGATION_BUTTONS,
            this.onUpdateNavigationButtonsRequested
        )

        document.addEventListener(
            QuestionPageRenderer.EVENT_AFTER_FORM_OPEN,
            this.onAfterFormOpen
        )
    }

    get totalPageCount() {
        return this.f$$('.question-page').length
    }

    getCurrentPage() {
        return this.getPageByIndex(this.currentPage)
    }

    getPageByIndex(pageIndex) {
        return this.f$(`.question-page:nth-child(${pageIndex + 1})`)
    }

    onDomContentLoaded() {
        this.setCurrentPage(0)
    }

    onDocumentWheelEvent = (e) => {
        const trigger = 1000

        if (isNaN(this._wheelDeltaY)) {
            this._wheelDeltaY = 0
        }

        this._wheelDeltaY += e.deltaY

        if (this._wheelDeltaY > trigger) {
            this.scrollDown()
        }

        if (this._wheelDeltaY < -trigger) {
            this.scrollUp()
        }

        setTimeout(() => {
            this._wheelDeltaY = 0
        }, 200)
    }

    onDocumentClick(e) {
        if (e.target.closest('.navigation .down')) {
            this.onNavigateDownClick(e)
        }

        if (e.target.closest('.navigation .up')) {
            this.onNavigationUpClick(e)
        }

        if (e.target.closest('.ok-button .button')) {
            this.onOKButtonClick(e)
        }
    }

    onNavigationUpClick(e) {
        this.scrollUp()
    }

    onNavigateDownClick(e) {
        this.scrollDown()
    }

    async onOKButtonClick(e) {
        const btn = e.target.closest('.ok-button').querySelector('.button')

        await this.flashAnimate(btn)

        this.scrollDown()
    }

    scrollDown = () => {
        this.scrollToPage(this.currentPage + 1)
    }

    scrollUp = () => {
        this.scrollToPage(this.currentPage - 1)
    }

    async validateData() {
        const result = document.dispatchEvent(
            new CustomEvent(QuestionPageRenderer.EVENT_DATA_VALIDATION, {
                cancelable: true,
                detail: {
                    page: this.getCurrentPage(),
                    formElement: this.formElement,
                },
            })
        )

        if (!result) {
            return Promise.reject()
        }

        return Promise.resolve()
    }

    async scrollToPage(pageIndex) {
        await this.validateData()

        this.setCurrentPage(pageIndex)

        const scroller = this.getRenderer(PageScroller)

        scroller.scrollToPage(this.currentPage)
    }

    setCurrentPage(pageIndex) {
        pageIndex = Math.max(0, Math.min(pageIndex, this.totalPageCount - 1))

        this.onBeforePageChange({
            currentPage: this.getPageByIndex(this.currentPage),
            nextPage: this.getPageByIndex(pageIndex),
        })

        this.currentPage = pageIndex

        this.onAfterPageChange({
            currentPage: this.getCurrentPage(),
        })
    }

    onBeforePageChange({ currentPage, nextPage }) {
        document.dispatchEvent(
            new CustomEvent(QuestionPageRenderer.EVENT_BEFORE_PAGE_CHANGE, {
                detail: {
                    currentPage,
                    nextPage,
                },
            })
        )
    }

    onAfterPageChange({ currentPage }) {
        document.dispatchEvent(
            new CustomEvent(QuestionPageRenderer.EVENT_AFTER_PAGE_CHANGE, {
                detail: {
                    currentPage,
                },
            })
        )

        this.onCurrentPageChanged()
    }

    onCurrentPageChanged() {
        this.updateNavigationButtonsDisabledState()
    }

    onUpdateNavigationButtonsRequested = () => {
        this.updateNavigationButtonsDisabledState()
    }

    updateNavigationButtonsDisabledState() {
        if (!this.canNavigateDown()) {
            this.f$('.navigation .down').setAttribute('disabled', 'disabled')
        } else {
            this.f$('.navigation .down').removeAttribute('disabled')
        }

        if (!this.canNavigateUp()) {
            this.f$('.navigation .up').setAttribute('disabled', 'disabled')
        } else {
            this.f$('.navigation .up').removeAttribute('disabled')
        }
    }

    canNavigateDown() {
        const shouldEnable = this.currentPage < this.totalPageCount - 1

        return shouldEnable && this.dispatchNavigationRenderEvent('down')
    }

    canNavigateUp() {
        const shouldEnable = this.currentPage > 0

        return shouldEnable && this.dispatchNavigationRenderEvent('up')
    }

    dispatchNavigationRenderEvent(name) {
        let event = null

        switch (name) {
            case 'up':
                event = QuestionPageRenderer.EVENT_SHOULD_ENABLE_NAVIGATE_UP
                break
            case 'down':
                event = QuestionPageRenderer.EVENT_SHOULD_ENABLE_NAVIGATE_DOWN
                break
            default:
                event = QuestionPageRenderer.EVENT_SHOULD_ENABLE_NAVIGATE_DOWN
                break
        }

        return document.dispatchEvent(
            new CustomEvent(event, {
                cancelable: true,
            })
        )
    }

    onQuestionsScroll = (e) => {}

    onAfterFormOpen = () => {
        this.scrollToPage(0)
    }
}

QuestionPageRenderer.boot()
