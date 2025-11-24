import { BaseRenderer } from './base-renderer'

export class AvailableHeightResolver extends BaseRenderer {
    onWindowResize() {
        this.measure()
    }

    onDomContentLoaded() {
        this.measure()
    }

    measure() {
        const doc = document.documentElement

        doc.style.setProperty('--available-height', `${window.innerHeight}px`)
    }
}

AvailableHeightResolver.boot()
