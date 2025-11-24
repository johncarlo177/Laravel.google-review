import { BaseRenderer } from './base-renderer'

import addToCalendarStyles from '../styles/components/add-to-calendar-button.scss?inline'

class EventQRCodeType extends BaseRenderer {
    shouldRun() {
        return this.$('.qrcode-type-event')
    }

    onDomContentLoaded() {
        this.loadAddToCalendarButton()
    }

    loadAddToCalendarButton() {
        const url = '/assets/lib/atcb.js?v=2'

        const script = document.createElement('script')

        script.async = true

        script.onload = this.onLibLoaded()

        script.src = url

        document.head.appendChild(script)
    }

    onLibLoaded = async () => {
        const sheet = document.createElement('style')

        sheet.innerHTML = addToCalendarStyles

        let elem = document.querySelector('add-to-calendar-button')

        while (!elem?.shadowRoot) {
            await new Promise((r) => setTimeout(r, 100))
            elem = document.querySelector('add-to-calendar-button')
        }

        elem.shadowRoot.appendChild(sheet)

        await new Promise((r) => setTimeout(r))

        elem.classList.add('qrcg-loaded')
    }
}

new EventQRCodeType()
