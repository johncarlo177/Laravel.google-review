import { BaseRenderer } from './base-renderer'

export class BusinessReview extends BaseRenderer {
    onDomContentLoaded() {
        if (!this.shouldRun()) return

        this.$('.stars-container').addEventListener(
            'mousemove',
            this.onStarsContainerMouseMove
        )
    }

    shouldRun() {
        return this.$('.qrcode-type-business-review .stars-container')
    }

    onDocumentClick(e) {
        if (!this.shouldRun()) return

        const elem = e.composedPath()[0]

        if (elem.matches('.star')) {
            return this.onStarClick(elem)
        }
    }

    onStarClick(elem) {
        this.activateStar(elem)

        const stars = this.getIndexOf(elem) + 1

        this.setInputValue(stars)

        if (stars >= window.__BUSINESS_REVIEW_STARS_BEFORE_REDIRECT__) {
            // High rating (4-5 stars): Show success message, hide form, and redirect to Google
            this.showSuccessMessageAndHideTheForm()
            this.openLinkInNewTab(window.__BUSINESS_REVIEW_FINAL_URL__)
        } else {
            // Low rating (1-3 stars): Show low rating message in same box, keep form visible
            this.showLowRatingMessage()
            this.scrollToForm()
        }
    }

    showSuccessMessageAndHideTheForm() {
        const successMessage = this.$('.success-message')
        
        if (!successMessage) return

        // Get high rating message text
        const highRatingText = successMessage.getAttribute('data-high-rating-text') || "We're glad you had a great experience! If you'd like, you can share it on Google."
        const googleLinkUrl = successMessage.getAttribute('data-google-link-url') || '#'
        const googleLinkText = successMessage.getAttribute('data-google-link-text') || 'share your experience on Google'

        // Build message with Google link - replace "share it on Google" with clickable link
        const linkHTML = `<a href="${googleLinkUrl}" target="_blank" style="color: #0066cc; font-weight: 600; text-decoration: underline;">${googleLinkText}</a>`
        const messageHTML = highRatingText.replace(/share it on Google|share your experience on Google/gi, linkHTML)

        successMessage.innerHTML = messageHTML

        // Hide any low-rating message (for backward compatibility)
        const lowRatingMessage = this.$('.low-rating-message')
        if (lowRatingMessage) {
            lowRatingMessage.classList.add('hidden')
        }

        // Show success message and hide form
        successMessage.classList.remove('hidden')

        const elemsToHide = this.$$('.business-review-form, .stars-container')
        elemsToHide.forEach((elem) => {
            elem.classList.add('hidden')
        })
    }

    /**
     * Show low rating message for 1-3 star ratings in the same success message box
     * Google link is ALWAYS visible for compliance
     */
    showLowRatingMessage() {
        const successMessage = this.$('.success-message')
        
        if (!successMessage) return

        // Hide any old low-rating message (for backward compatibility)
        const lowRatingMessage = this.$('.low-rating-message')
        if (lowRatingMessage) {
            lowRatingMessage.classList.add('hidden')
        }

        // Get low rating content from data attributes
        const title = successMessage.getAttribute('data-low-rating-title') || 'Thank you for your feedback — we want to make this right.'
        const message = successMessage.getAttribute('data-low-rating-message') || 'Tell us what happened so we can fix it.'
        const googleLinkUrl = successMessage.getAttribute('data-google-link-url') || '#'
        const googleLinkText = successMessage.getAttribute('data-google-link-text') || 'share your experience on Google'

        // Build the message HTML - Google link is ALWAYS shown for compliance
        const messageHTML = `<strong>${title}</strong><br><br>${message}<br><br>You can also <a href="${googleLinkUrl}" target="_blank" style="color: #0066cc; font-weight: 600; text-decoration: underline;">${googleLinkText}</a>.`

        // Set the content
        successMessage.innerHTML = messageHTML

        // Show the message (but keep form visible)
        successMessage.classList.remove('hidden')
    }

    /**
     * Scroll to the form smoothly
     */
    scrollToForm() {
        const form = this.getForm()
        
        if (form) {
            setTimeout(() => {
                form.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                })
            }, 300)
        }
    }

    /**
     *
     * @returns {HTMLFormElement}
     */
    getForm() {
        return this.$('form.business-review-form')
    }

    submitFormOnPositiveFeedback() {
        this.getForm().setAttribute('novalidate', 'true')
        this.getForm().requestSubmit()
    }

    activateStar(star) {
        this.clearActiveStars()

        star.classList.add('active')

        this.forEachStarUntil(star, (s) => s.classList.add('active'))
    }

    syncValue() {
        let value = this.$('[name=stars]').value

        if (isNaN(value)) {
            value = 0
        }

        if (!value) return

        this.activateStar(this.findStarByDomIndex(value))
    }

    findStarByDomIndex(i) {
        return this.$('.star:nth-child(' + i + ')')
    }

    setInputValue(value) {
        this.$('[name=stars]').value = value
    }

    forEachStarUntil(star, callback) {
        const index = this.getIndexOf(star)

        const children = [...star.parentElement.children]

        for (let i = 0; i < index; i++) {
            const currentStar = children[i]

            callback(currentStar)
        }
    }

    onStarsContainerMouseMove = (e) => {
        const elem = e.composedPath()[0]

        if (!elem.matches('.star')) {
            return this.clearStarHover()
        }

        this.doHoverStar(elem)
    }

    clearStarClass(name) {
        this.$$('.star.' + name).forEach((e) => e.classList.remove(name))
    }

    clearStarHover() {
        this.clearStarClass('hover')
    }

    clearActiveStars() {
        this.clearStarClass('active')
    }

    getIndexOf(elem) {
        return [...elem.parentElement.children].indexOf(elem)
    }

    /**
     *
     * @param {HTMLElement} star
     */
    doHoverStar(star) {
        this.clearStarHover()
        this.forEachStarUntil(star, (s) => s.classList.add('hover'))
    }
}

BusinessReview.boot()
