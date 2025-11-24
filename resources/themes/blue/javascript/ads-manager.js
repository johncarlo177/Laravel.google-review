import { BaseRenderer } from './base-renderer'

export class AdsManager extends BaseRenderer {
    seconds = 0

    onDomContentLoaded() {
        if (!this.numberElement()) return

        this.updateView()

        setInterval(() => {
            this.tick()
        }, 1000)
    }

    numberElement() {
        return document.querySelector('.banner-layout .navbar .number')
    }

    totalTimout() {
        return window.BANNER_TIMEOUT
    }

    tick() {
        this.seconds++

        this.reloadIfNeeded()

        this.updateView()
    }

    reloadIfNeeded() {
        if (this.reloadIsDone) {
            return
        }

        if (this.remainingTime() == 0) {
            setTimeout(() => {
                window.location.reload()
                this.reloadIsDone = true
            }, 1000)
        }
    }

    remainingTime() {
        return Math.max(this.totalTimout() - this.seconds, 0)
    }

    updateView() {
        setTimeout(() => {
            this.numberElement().innerHTML = this.remainingTime()
        })
    }
}

AdsManager.boot()
