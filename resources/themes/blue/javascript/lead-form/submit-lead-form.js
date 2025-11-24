import { Config } from '../config'
import { BaseLeadFormAnswer } from './answers/base-answer'
import { BaseLeadFormRenderer } from './base-lead-form-renderer'
import { FingerPrintRenderer } from './fingerprint-renderer'

class SubmitLeadForm extends BaseLeadFormRenderer {
    onDocumentClick(e) {
        if (!this.isChildOfBoundForm(e.target)) return

        if (e.target.closest('.ok-button.submit')) {
            this.onSubmitClick(e)
        }
    }

    isButtonDisabled(e) {
        const container = e.target.closest('.ok-button.submit')

        const btn = container.querySelector('.button')

        return btn.hasAttribute('disabled')
    }

    setButtonLoading(e) {
        const container = e.target.closest('.ok-button.submit')

        const btn = container.querySelector('.button')

        btn.setAttribute('disabled', 'true')

        btn.classList.add('loading')

        if (container.querySelector('qrcg-loader')) {
            return
        }

        const loader = document.createElement('qrcg-loader')

        loader.setAttribute(
            'style',
            `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                height: 100%;
                width: 3rem;
                left: 50%;
                transform: translateX(-50%) scale(0.7);
            `
        )

        container.appendChild(loader)
    }

    async onSubmitClick(e) {
        if (this.isButtonDisabled(e)) return

        if (!this.hasValidData()) {
            return
        }

        this.setButtonLoading(e)

        const fields = this.getFields()

        const token = this.getCsrfToken()

        const leadFormId = this.getLeadFormId()

        const data = { fields, token, leadFormId }

        /**
         * @type {FingerPrintRenderer}
         */
        const fingerPrint = this.getRenderer(FingerPrintRenderer)

        await fingerPrint.extendFormData(data)

        const formData = this.buildFormData(data)

        try {
            await this.post(formData)

            this.onSuccess()
        } catch (err) {
            console.log(err)
            this.onPostError(err)
        }

        this.onAfterPost()
    }

    onPostError(err) {
        //
    }

    onAfterPost() {
        //
    }

    onSuccess() {
        document.dispatchEvent(
            new CustomEvent(SubmitLeadForm.EVENT_ON_SUCCESS, {
                detail: { form: this.form },
            })
        )
    }

    endpoint() {
        return Config.get('app.url') + '/api/lead-form-response'
    }

    async post(formData) {
        const response = await fetch(this.endpoint(), {
            method: 'POST',
            body: formData,
            headers: {
                Accept: 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error('Error while submitting the form')
        }
    }

    buildFormData({ fields, token, leadFormId, ...rest }) {
        const data = new FormData()

        data.append('fields', fields)

        data.append('_token', token)

        data.append('lead_form_id', leadFormId)

        if (typeof rest == 'object') {
            for (const key of Object.keys(rest)) {
                data.append(key, rest[key])
            }
        }

        return data
    }

    getCsrfToken() {
        return this.$('[name="csrf-token"]')?.getAttribute('content')
    }

    /**
     * This validates only the latest page, because each page
     * should not scroll until it's valid.
     * @returns {Boolean}
     */
    hasValidData() {
        return this.getAnswerRenderers().reduce((result, page) => {
            return result && page.validateData()
        }, true)
    }

    /**
     *
     * @returns {BaseLeadFormAnswer[]}
     */
    getAnswerRenderers() {
        return this.form.renderers.filter((renderer) => {
            return renderer instanceof BaseLeadFormAnswer
        })
    }

    getFields() {
        let fields = this.f$$('.question-page').reduce(
            (result, questionPage) => {
                return this.form.renderers.reduce((result, renderer) => {
                    if (renderer instanceof BaseLeadFormAnswer) {
                        const json = renderer.toJson(questionPage)

                        if (json) {
                            result.push(json)
                        }
                    }

                    return result
                }, result)
            },
            []
        )

        fields = JSON.stringify(fields)

        return fields
    }
}

SubmitLeadForm.boot()
