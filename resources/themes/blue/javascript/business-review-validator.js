import { BaseRenderer } from './base-renderer'

export class BusinessReviewValidator extends BaseRenderer {
    shouldRun() {
        return this.$('.qrcode-type-business-review .business-review-form')
    }

    onDomContentLoaded() {
        if (!this.shouldRun()) {
            return
        }

        this.getForm().addEventListener('submit', this.onFormSubmit)
    }

    getStarsContainer() {
        return this.$('.stars-container')
    }

    highlightStarsContainer() {
        this.getStarsContainer().classList.add('danger')

        window.scrollTo({
            top: this.getStarsContainer().offsetTop,
        })

        setTimeout(() => {
            this.getStarsContainer().classList.remove('danger')
        }, 1500)
    }

    /**
     *
     * @param {SubmitEvent} e
     */
    onFormSubmit = (e) => {
        if (this.getForm().hasAttribute('novalidate')) {
            return
        }

        if (!this.isFormValid()) {
            e.preventDefault()
            e.stopPropagation()

            if (!this.isNumberOfStarsValid()) {
                this.highlightStarsContainer()
            }
        }
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onDocumentClick(e) {
        const target = e.composedPath()[0]

        this.disableSubmitButton(target)
    }

    /**
     *
     * @returns {HTMLFormElement}
     */
    getForm() {
        return this.$('form.business-review-form')
    }

    isFormValid() {
        return this.getForm().checkValidity() && this.isNumberOfStarsValid()
    }

    getNumberOfStars() {
        return this.$('[name=stars]').value
    }

    isNumberOfStarsValid() {
        const n = this.getNumberOfStars()

        const isValid = !isNaN(n) && n > 0

        return isValid
    }

    disableSubmitButton(target) {
        const button = target.closest('.button')

        if (!button) {
            return
        }

        setTimeout(() => {
            this.setButtonDisabled(true)
        }, 10)

        if (!this.isFormValid()) {
            setTimeout(() => {
                this.setButtonDisabled(false)
            }, 1000)
        }
    }

    /**
     * @return {HTMLElement}
     */
    getButton() {
        return this.$('.button.submit-review')
    }

    setButtonDisabled(disabled) {
        if (disabled) {
            this.getButton().setAttribute('disabled', 'true')
        } else {
            this.getButton().removeAttribute('disabled')
        }
    }
}

BusinessReviewValidator.boot()
