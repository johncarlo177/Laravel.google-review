import { BaseRenderer } from './base-renderer'

export class ShareOnWhatsAppRenderer extends BaseRenderer {
    onDocumentClick(e) {
        if (e.target.closest('.share-on-whatsapp-container .icon')) {
            this.sendOnWhatsapp()
        }
    }

    get container() {
        return this.$('.share-on-whatsapp-container')
    }

    get input() {
        return this.container.querySelector('input')
    }

    shouldRun() {
        return !!this.container
    }

    sendOnWhatsapp() {
        const number = this.input?.value

        if (!number) {
            return
        }

        const formattedNumber = this.formatWhatsAppNumber(number)

        const message = this.generateMessage()

        const link = `https://wa.me/${formattedNumber}?text=${message}`

        this.openLinkInNewTab(link)
    }

    generateMessage() {
        let message = this.container.dataset.message

        message = [message, this.getUrl()].filter((s) => s.length > 0).join(' ')

        const encoded = encodeURIComponent(message)

        return encoded
    }

    getUrl() {
        const params = new URLSearchParams(window.location.search)

        params.delete('preview')

        let search = params.toString()

        search = search.length > 0 ? `?` + search : ''

        const url = `${location.protocol}//${location.host}${location.pathname}${search}`

        return url
    }

    formatWhatsAppNumber(number) {
        number = number.replace(/[^\d]/g, '')

        number = number.replace(/^0+/g, '')

        return number
    }

    onWhatsAppKeyUp(e) {
        if (e.key === 'Enter') {
            this.sendOnWhatsapp()
        }
    }
}

ShareOnWhatsAppRenderer.boot()
