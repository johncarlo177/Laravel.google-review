import { BaseRenderer } from './base-renderer'

class BioLinksRenderer extends BaseRenderer {
    onDomContentLoaded() {
        // check if in iframe

        if (window.parent !== window) {
            const links = this.$$('a')
            links.forEach((a) => a.setAttribute('target', '_blank'))
        }
    }

    onDocumentClick(e) {}
}

new BioLinksRenderer()
