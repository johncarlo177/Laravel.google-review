import { BaseLeadFormRenderer } from './base-lead-form-renderer'

export class PageScroller extends BaseLeadFormRenderer {
    pages() {
        const pages = this.f$$('.question-page')

        return pages
    }

    async scrollToPage(pageNumber) {
        let pageHeight = getComputedStyle(this.f$('.questions')).height

        pageHeight = this.pxToNumber(pageHeight)

        const top = -pageHeight * pageNumber

        this.pages().forEach((p) => (p.style = `top: ${top}px`))

        return Promise.all(this.pages().map((p) => this.transitionPromise(p)))
    }

    pxToNumber(value) {
        return +value.replace('px', '')
    }
}

PageScroller.boot()
