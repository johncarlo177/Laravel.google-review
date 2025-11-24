import { BodyScrollBlocker } from '../body-scroll-blocker'

import { BaseLeadFormRenderer } from './base-lead-form-renderer'

export class CloseLeadForm extends BaseLeadFormRenderer {
    static shouldBootOnForm(form) {
        return form.matches('.inline')
    }

    constructor(f) {
        super(f)

        document.addEventListener(
            CloseLeadForm.EVENT_REQUEST_CLOSE,
            this.onCloseRequested
        )
    }

    onDocumentClick(e) {
        if (e.target.closest('.lead-form') != this.form) return

        if (e.target.closest('.lead-form-close-button')) {
            this.onCloseButtonClick(e)
        }
    }

    onCloseButtonClick() {
        this.doClose()
    }

    onCloseRequested = (e) => {
        if (e.detail.form != this.form) return

        this.doClose()
    }

    async doClose() {
        BodyScrollBlocker.unblock()

        await new Promise((r) => setTimeout(r))

        const trigger = this.findTriggerButton()

        this.addClosingClassToForm()

        this.removeOpenedClassFromForm()

        this.syncFormRectWithTrigger(trigger)

        await this.transitionPromise(this.form)

        setTimeout(() => {
            this.addClosedClassToForm()
        }, 50)
    }

    addClosedClassToForm() {
        this.form.classList.add('closed')
    }

    findTriggerButton() {
        const triggers = this.$$('.lead-form-trigger')

        const trigger = triggers.find((button) => {
            return this.form.matches(button.dataset.target)
        })

        return trigger
    }

    syncFormRectWithTrigger(trigger) {
        const px = (v) => `${v}px`

        const { height, width, top, left } = trigger.getBoundingClientRect()

        this.form.style.height = px(height)

        this.form.style.width = px(width)

        this.form.style.top = px(top)

        this.form.style.left = px(left)
    }

    addClosingClassToForm() {
        this.form.classList.add('closing')
    }

    removeOpenedClassFromForm() {
        this.form.classList.remove('opened')
    }
}

CloseLeadForm.boot()
