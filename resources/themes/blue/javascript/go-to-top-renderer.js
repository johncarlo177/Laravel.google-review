import { BaseRenderer } from './base-renderer'

export class GoToTopRenderer extends BaseRenderer {
    get cssClass() {
        return 'go-to-top-button'
    }

    get button() {
        return document.querySelector('.' + this.cssClass)
    }

    onDocumentClick(e) {
        const target = e.target

        if (target.closest('.' + this.cssClass)) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            })
        }
    }

    onWindowScroll() {
        if (window.scrollY > 800) {
            this.button?.classList.add('visible')
        } else {
            this.button?.classList.remove('visible')
        }
    }
}

GoToTopRenderer.boot()
