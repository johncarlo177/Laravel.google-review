import { BaseRenderer } from './base-renderer'

export class iFrameBlockRenderer extends BaseRenderer {
    destination = null

    /**
     *
     * @param {MouseEvent} e
     */
    onDocumentClick(e) {
        this.openIframeIfNeeded(e)

        this.closeIframeIfNeeded(e)
    }

    /**
     *
     * @param {MouseEvent} e
     */
    closeIframeIfNeeded(e) {
        const closeButton = e.target.closest('.iframe-block-close-button')

        if (!closeButton) {
            return
        }

        const container = document.querySelector(
            '.iframe-block-full-screen-container'
        )

        container.remove()

        document.body.classList.remove('scroll-blocked')
    }

    openIframeIfNeeded(e) {
        const anchor = e.target.closest('.iframe-block a')

        if (!anchor) {
            return
        }

        this.destination = anchor.getAttribute('destination')

        this.openIframe()
    }

    renderContainer() {
        const container = document.createElement('div')

        container.classList.add('iframe-block-full-screen-container')

        return container
    }

    renderCloseButton() {
        const button = document.createElement('div')

        button.classList.add('iframe-block-close-button')

        button.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>close</title><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg>
        `

        return button
    }

    renderIframe() {
        const iframe = document.createElement('iframe')

        iframe.src = this.destination

        iframe.classList.add('iframe-block-full-screen-iframe')

        return iframe
    }

    buildIframePopup() {
        const container = this.renderContainer()

        const button = this.renderCloseButton()

        const iframe = this.renderIframe()

        container.appendChild(button)

        container.appendChild(iframe)

        document.body.appendChild(container)
    }

    openIframe() {
        this.buildIframePopup()

        document.body.classList.add('scroll-blocked')
    }
}

new iFrameBlockRenderer()
