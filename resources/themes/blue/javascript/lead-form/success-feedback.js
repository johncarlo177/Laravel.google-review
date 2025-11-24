import { FrontendToast } from '../frontend-toast'
import { BaseLeadFormRenderer } from './base-lead-form-renderer'

export class SuccessFeedback extends BaseLeadFormRenderer {
    constructor(f) {
        super(f)

        document.addEventListener(
            SuccessFeedback.EVENT_ON_SUCCESS,
            this.onSuccess
        )
    }

    isInlineForm() {
        return this.form.matches('.inline')
    }

    onSuccess = (e) => {
        if (e.detail.form != this.form) return

        if (this.isInlineForm()) {
            this.onInlineSuccess()
        } else {
            this.onFullPageSuccess()
        }

        this.redirectToAfterSubmitUrlIfNeeded()
    }

    redirectToAfterSubmitUrlIfNeeded() {
        const url = this.form.dataset.afterSubmitUrl

        if (!url || !url.length) {
            return
        }

        window.location = url
    }

    onFullPageSuccess() {
        this.form.classList.add('success')
    }

    onInlineSuccess() {
        document.dispatchEvent(
            new CustomEvent(SuccessFeedback.EVENT_REQUEST_CLOSE, {
                detail: {
                    form: this.form,
                },
            })
        )

        const message =
            window.__LEAD_FORM_SUCCESS_MESSAGE__ ??
            'Thank you for submitting the form'

        FrontendToast.show(message)
    }
}

SuccessFeedback.boot()
