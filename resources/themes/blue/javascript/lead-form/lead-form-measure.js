import { BaseLeadFormRenderer } from './base-lead-form-renderer'

export class LeadFormMeasure extends BaseLeadFormRenderer {
    constructor(form) {
        super(form)

        if (!this.shouldRun()) {
            return
        }

        this.width = 0

        this.top = 0

        this.left = 0

        this.measure()
    }

    onWindowResize(e) {
        this.measure()
    }

    layout() {
        const layout = this.form.getAttribute('layout')

        if (!layout || !layout.length) {
            return this.$('.layout-generated-webpage')
        }

        return this.$(layout)
    }

    layoutRect() {
        return this.layout()?.getBoundingClientRect()
    }

    onDomContentLoaded() {
        this.measure()
    }

    async measure() {
        if (this.measuring) return

        this.measuring = true

        const px = (v) => `${v}px`

        await this.getLayoutDimensions()

        const style = `.lead-form.measured { 
            display: block;
            position: fixed!important;
            top: ${px(this.top)};
            left: ${px(this.left)};
            width: ${px(this.width)};
            height: var(--available-height);
        }`

        const tag = this.getStyleTag()

        tag.innerHTML = style

        this.form.classList.add('measured')

        this.measuring = false
    }

    async getLayoutDimensions() {
        const calc = () => {
            this.width = this.layoutRect()?.width

            this.left = this.layoutRect()?.left
        }

        calc()

        while (!this.width) {
            //

            await new Promise((r) => setTimeout(r, 100))

            calc()
        }

        this.top = 0
    }

    getStyleTag() {
        if (!this._styleTag) {
            this._styleTag = document.createElement('style')
            document.head.appendChild(this._styleTag)
        }

        return this._styleTag
    }
}

LeadFormMeasure.boot()
