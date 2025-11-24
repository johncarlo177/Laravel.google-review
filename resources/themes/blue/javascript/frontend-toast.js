import { BaseRenderer } from './base-renderer'

export class FrontendToast extends BaseRenderer {
    static show(message, timeout = 2000) {
        const instance = new FrontendToast(message, timeout)

        return instance.show()
    }

    constructor(message, timeout) {
        super()
        this.message = message
        this.timeout = timeout
    }

    async show() {
        return new Promise((resolve) => {
            const div = this.render()

            document.body.appendChild(div)

            setTimeout(async () => {
                div.classList.add('closing')

                await this.animationPromise(div)

                div.remove()

                resolve()
            }, this.timeout)
        })
    }

    render() {
        const div = document.createElement('div')

        div.classList.add('frontend-toast')

        div.innerHTML = this.message

        return div
    }
}
