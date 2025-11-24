export class BaseRenderer {
    #mainCalled = false

    constructor() {
        // Wait until child constructor finishes it's stuff.

        setTimeout(() => {
            if (!this.shouldRun()) return

            document.addEventListener('DOMContentLoaded', this.main)

            this.main()

            document.addEventListener('click', this.onBaseDocumentClick)

            document.addEventListener('keyup', this.onBaseDocumentKeyup)

            document.addEventListener('keypress', this.onBaseDocumentKeypress)

            document.addEventListener('paste', this.onBasePaste)

            document.addEventListener(
                'all-images-loaded',
                this.onBaseAllImagesLoaded
            )

            window.addEventListener('scroll', this.onBaseWindowScroll)

            document.addEventListener('wheel', this.onBaseDocumentWheel)

            document.addEventListener('input', this.onBaseDocumentInput)

            window.addEventListener('resize', this.onBaseWindowResize)
        })
    }

    static boot() {
        return new this()
    }

    /**
     *
     * @param {String} selector
     * @returns {HTMLElement}
     */

    $(selector) {
        return document.querySelector(selector)
    }

    $$(selector) {
        return Array.from(document.querySelectorAll(selector))
    }

    shouldRun() {
        return true
    }

    onDomContentLoaded() {}

    onBaseDocumentClick = (e) => {
        this.onDocumentClick(e)
    }

    onBaseDocumentInput = (e) => {
        this.onDocumentInput(e)
    }

    onDocumentInput(e) {}

    onBaseDocumentKeyup = (e) => {
        this.onDocumentKeyup(e)
    }

    onAllImagesLoaded() {}

    onBaseAllImagesLoaded = (e) => {
        this.onAllImagesLoaded(e)
    }

    onDocumentKeypress(e) {}

    onBaseDocumentKeypress = (e) => {
        this.onDocumentKeypress(e)
    }

    onBasePaste = (e) => {
        this.onPaste(e)
    }

    onPaste(e) {}

    onDocumentKeyup(e) {}

    onDocumentClick(e) {}

    onBaseWindowScroll = (e) => {
        this.onWindowScroll(e)
    }

    onWindowScroll(e) {}

    onBaseDocumentWheel = (e) => {
        this.onDocumentWheelEvent(e)
    }

    onWindowResize(e) {}

    onBaseWindowResize = (e) => {
        this.onWindowResize(e)
    }

    onDocumentWheelEvent(e) {}

    animationPromise(elem) {
        return new Promise((resolve) => {
            elem.addEventListener('animationend', resolve)
        })
    }

    transitionPromise(elem) {
        return new Promise((resolve) => {
            elem.addEventListener('transitionend', resolve)
        })
    }

    toggleClass(elem, _class) {
        if (elem.classList.contains(_class)) {
            elem.classList.remove(_class)
        } else {
            elem.classList.add(_class)
        }
    }

    openLinkInNewTab(link) {
        const a = document.createElement('a')

        a.href = link

        a.target = '_blank'

        a.style = 'display: none'

        document.body.appendChild(a)

        a.click()

        setTimeout(() => {
            a.remove()
        }, 100)
    }

    main = () => {
        if (this.#mainCalled) {
            return
        }

        this.#mainCalled = true

        this.onDomContentLoaded()
    }
}
