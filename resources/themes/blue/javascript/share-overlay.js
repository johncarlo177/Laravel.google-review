import { BaseRenderer } from './base-renderer'

export class ShareOverlayRenderer extends BaseRenderer {
    shouldRun() {
        return this.$('.share-overlay-trigger') !== null
    }

    onDomContentLoaded() {
        if (!this.shouldRun()) {
            return
        }

        this.bindTriggerPosition()

        document.addEventListener('all-images-loaded', () => {
            setTimeout(() => {
                this.bindTriggerPosition()
            }, 100)
        })
    }

    onWindowResize() {
        setTimeout(() => {
            this.bindTriggerPosition()
        })
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onDocumentClick(e) {
        this.onTriggerClick(e)
        this.onInnerComponentClick(e)
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onTriggerClick(e) {
        const target = e.target.closest('.share-overlay-trigger')

        if (!target) {
            return
        }

        const overlay = this.$('.share-overlay')

        overlay.classList.add('open')
    }

    onInnerComponentClick(e) {
        if (!e.target.closest('.share-overlay')) {
            return
        }

        this.onNatvieShareClick(e)

        this.onCloseClick(e)
    }

    onCloseClick(e) {
        const target = e.target.closest('.close')

        if (!target) {
            return
        }

        const overlay = this.$('.share-overlay')

        overlay.classList.remove('open')
    }

    onNatvieShareClick(e) {
        const target = e.target.closest('.native-share-button')

        if (!target) {
            return
        }

        this.nativeShare()
    }

    nativeShare() {
        try {
            navigator.share({
                title: document.title,
                url: window.location.href.replace(/\?preview=true/, ''),
            })
        } catch (e) {
            console.error('Native share failed', e)
        }
    }

    bindTriggerPosition() {
        const trigger = this.$('.share-overlay-trigger')
        const container = this.$('.layout-generated-webpage')

        const { left } = container.getBoundingClientRect()

        trigger.style.left = `calc(${left}px - 2rem)`

        trigger.classList.add('ready')
    }
}

new ShareOverlayRenderer()
