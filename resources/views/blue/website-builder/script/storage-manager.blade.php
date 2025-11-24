<script>
    class QrcgRemoteStorage {
        token() {
            return "{{ request()->token }}"
        }

        baseUrl() {
            return "{{ url('api/website-builder-storage') }}"
        }

        url(type) {
            return this.baseUrl() + '/' + type + '?token=' + this.token()
        }

        async load(type) {
            const response = await fetch(this.url(type))

            const result = await response.text()

            return result;
        }

        async loadJson() {
            try {
                return JSON.parse(await this.load('json'))
            } catch {
                return {}
            }
        }

        utoa(data) {
            return btoa(encodeURIComponent(data));
        }

        atou(b64) {
            return decodeURIComponent(atob(b64));
        }

        store(type, data) {
            data = JSON.stringify(data)

            return fetch(this.url(type), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    "Content-Type": 'application/json',
                },
                body: JSON.stringify({
                    payload: this.utoa(data)
                })
            })
        }
    }

    function remoteStoragePlugin(editor) {
        const remote = new QrcgRemoteStorage()

        editor.Storage.add('remote', {
            async load() {
                return remote.loadJson()
            },
            async store(data) {
                return remote.store('json', data)
            }
        })

        editor.on('storage:start:store', function() {
            window.parent?.postMessage('saving:start', '*')
        })


        editor.on('storage:store', async function(e) {
            try {
                await remote.store('css', editor.getCss())
                await remote.store('html', editor.getHtml())
            } catch {
                window.parent?.postMessage('saving:error', '*')
            }

            window.parent?.postMessage('saving:end', '*')

        });
    }
</script>
