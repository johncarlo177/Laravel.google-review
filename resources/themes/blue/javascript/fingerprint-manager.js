export class FingerPrintManager {
    static get LOADED_EVENT() {
        return 'fingerprint-manager:loaded'
    }

    path() {
        return '/assets/lib/fp.min.js'
    }

    loadScript() {
        return new Promise((resolve) => {
            if (window.FingerprintJS) {
                return resolve()
            }

            const tag = document.createElement('script')

            tag.addEventListener('load', () => {
                this.onScriptLoaded()

                resolve()
            })

            tag.src = this.path()

            document.head.appendChild(tag)
        })
    }

    async getFingerprint() {
        await this.loadScript()

        const instance = await window.FingerprintJS.load()

        const result = await instance.get()

        const id = result.visitorId

        return id
    }

    onScriptLoaded() {
        document.dispatchEvent(new CustomEvent(FingerPrintManager.LOADED_EVENT))
    }
}
