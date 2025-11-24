import { BaseLeadFormRenderer } from './base-lead-form-renderer'
import { FingerPrintManager } from '../fingerprint-manager'

export class FingerPrintRenderer extends BaseLeadFormRenderer {
    constructor(form) {
        super(form)

        document.addEventListener(
            FingerPrintRenderer.EVENT_ON_SUCCESS,
            this.onFormSubmit
        )

        this.blockSubmissionIfNeeded()
    }

    async blockSubmissionIfNeeded() {
        if (this.multipleSubmissionAllowed()) {
            return
        }

        if (this.getLocalStorageValue('submitted')) {
            return this.blockSubmission()
        } else {
            console.log('local storage not submitted')
        }

        if (await this.checkIfFingerprintFound()) {
            this.blockSubmission()
        }
    }

    blockSubmission() {
        this.form.setAttribute('submission-blocked', 'true')
    }

    onFormSubmit = () => {
        this.setLocalStorageValue('submitted', true)

        if (this.multipleSubmissionAllowed()) return

        if (this.isFullPage()) {
            setTimeout(() => {
                window.location.reload()
            }, 1000)
        } else {
            this.blockSubmissionIfNeeded()
        }
    }

    setLocalStorageValue(key, value) {
        localStorage[this.localStorageKey(key)] = JSON.stringify(value)
    }

    getLocalStorageValue(key) {
        try {
            const raw = localStorage[this.localStorageKey(key)]

            return JSON.parse(raw)
        } catch {
            return null
        }
    }

    localStorageKey(key) {
        return `fingerprint-render:form-${this.getLeadFormId()}:${key}`
    }

    async extendFormData(data) {
        data.fingerprint = await this.getFingerprint()
    }

    async checkIfFingerprintFound() {
        try {
            const response = await fetch(
                `/api/lead-form-response/check-fingerprint`,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        lead_form_id: this.getLeadFormId(),
                        fingerprint: await this.getFingerprint(),
                    }),
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                }
            )

            const data = await response.json()

            return data.found
        } catch (ex) {}
    }

    multipleSubmissionAllowed() {
        return !this.form.hasAttribute('single-submission')
    }

    async getFingerprint() {
        const manager = new FingerPrintManager()

        return await manager.getFingerprint()
    }
}

FingerPrintRenderer.boot()
