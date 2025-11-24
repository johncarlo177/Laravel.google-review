import { BaseRenderer } from './base-renderer'

class SplashScreenRenderer extends BaseRenderer {
    constructor() {
        super()
    }

    shouldRun() {
        return !!this.$('.splash-screen')
    }

    getDefinedTimeout() {
        const t = window.QRCG_SPLASH_SCREEN_TIMEOUT

        if (!t || t.length === 0) return 3000

        return t * 1000
    }

    onDomContentLoaded() {
        setTimeout(() => {
            this.$('.splash-screen').remove()
        }, this.getDefinedTimeout())
    }
}

new SplashScreenRenderer()
