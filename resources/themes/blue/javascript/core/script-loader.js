export class ScriptLoader {
    url = null

    static loadedUrls = {}

    static withUrl(url) {
        //
        const instance = new this()

        instance.url = url

        return instance
    }

    load() {
        if (ScriptLoader.loadedUrls[this.url]) {
            return Promise.resolve()
        }

        return new Promise((resolve, reject) => {
            const tag = document.createElement('script')

            tag.onload = () => {
                //
                ScriptLoader.loadedUrls[this.url] = true

                resolve()
            }

            tag.src = this.url

            document.body.appendChild(tag)
        })
    }
}
