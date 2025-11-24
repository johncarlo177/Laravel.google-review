export class StyleSheetLoader {
    static cacheStore = {}

    url

    /**
     * @type {HTMLElement}
     */
    container = document.body

    static withUrl(url) {
        const instance = new this()

        instance.url = url

        return instance
    }

    /**
     *
     * @param {HTMLElement} element
     */
    inContainer(element) {
        //
        this.container = element

        return this
    }

    async loadRemoteStyle() {
        const response = await fetch(this.url)

        return await response.text()
    }

    async inject() {
        let text =
            StyleSheetLoader.cacheStore[this.url] ??
            (await this.loadRemoteStyle())

        if (this.container.querySelector(`style[data-url="${this.url}"]`)) {
            // already loaded in the current container, do not load again
            return
        }

        const tag = document.createElement('style')

        tag.innerHTML = text

        console.log(tag)

        tag.setAttribute('data-url', this.url)

        this.container.appendChild(tag)
    }
}
