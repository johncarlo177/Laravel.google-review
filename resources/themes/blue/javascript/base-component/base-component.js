import style from './base-component.scss?inline'

export class BaseComponent extends HTMLElement {
    static tag = null

    static styleSheets = [style]

    static register() {
        window.customElements.define(this.tag, this)
    }

    constructor() {
        super()

        this.attachShadow({ mode: 'open' })
    }

    static renderSelf() {
        return `<${this.tag}></${this.tag}>`
    }

    static findSelf(container) {
        return container.querySelector(this.tag)
    }

    connectedCallback() {
        this.injectHtml()

        this.injectStyle()
    }

    disconnectedCallback() {
        //
    }

    firstUpdated() {}

    injectHtml() {
        this.clearOldHTML()

        const html = this.render()

        if (typeof html === 'undefined') {
            return
        }

        const template = document.createElement('template')

        template.innerHTML = html

        this.shadowRoot.appendChild(template.content)

        setTimeout(() => {
            if (this.__didCallFirstUpdated) {
                return
            }

            this.firstUpdated()

            this.__didCallFirstUpdated = true
        })
    }

    clearOldHTML() {
        this.shadowRoot
            .querySelectorAll('*:not(style)')
            .forEach((e) => e.remove())

        this.shadowRoot.childNodes.forEach((node) => {
            if (node.nodeType == Node.COMMENT_NODE) {
                node.remove()
            }
        })
    }

    requestUpdate() {
        setTimeout(() => {
            this.injectHtml()
        })
    }

    injectStyle() {
        const tag = document.createElement('style')

        tag.innerHTML = this.constructor.styleSheets.join('\n')

        this.shadowRoot.appendChild(tag)
    }

    render() {}
}
