<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Troubleshoot</title>
    <style>
        html {
            font-family: sans-serif;
        }

        * {
            box-sizing: border-box;
            line-height: 1.7;
        }

        .item {
            display: flex;
            border: 2px solid #eee;
            padding: 1rem;
            margin: 1rem;
            border-radius: 0.5rem;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .item .title {
            font-weight: bold;
        }

        code {
            user-select: all;
            background-color: #eee;
            padding: 1rem;
        }

        pre {
            display: block;
            padding: 1rem 0;
            font-family: ui-monospace, monospace;
        }

        .result {
            color: #919191;
            font-weight: bold;
        }

        .item.error {
            border-color: red;
        }
    </style>
</head>

<body>
    <div class="item auth-header">
        <div class=details>
            <div class="title">
                Auth Header
            </div>
            <div class="help">
                Some Apache mod_security rules prevent the Authorization Header from reaching PHP script, please disable those rules to fix this issue.
            </div>
        </div>
        <div class="result loading">
            Loading...
        </div>
    </div>

    <div class="item put-requests">
        <div class=details>
            <div class="title">
                PUT Requests
            </div>
            <div class="help">
                Make sure PUT requests are allowed in mod_security rules.
            </div>
        </div>
        <div class="result loading">
            Loading...
        </div>
    </div>


    <div class="item delete-requests">
        <div class=details>
            <div class="title">
                DELETE Requests
            </div>
            <div class="help">
                Make sure DELETE requests are allowed in mod_security rules.
            </div>
        </div>
        <div class="result loading">
            Loading...
        </div>
    </div>

    <script>
        (function() {

            function renderError(itemSelector, message) {
                const result = document.querySelector(itemSelector + ' .result')

                const item = document.querySelector(itemSelector)

                item.classList.add('error')

                result.classList.add('error')

                result.innerHTML = '❌ ' + message

            }

            async function doCheck(itemSelector, fetcher) {
                const result = document.querySelector(itemSelector + ' .result')

                try {
                    const response = await fetcher()

                    const json = await response.json();

                    if (json.success) {
                        result.classList.add('success')
                        result.innerHTML = '✅ PASS'
                    } else {
                        renderError(itemSelector, 'ERROR')
                    }

                } catch (ex) {
                    renderError(itemSelector, ex.message)
                } finally {
                    result.classList.remove('loading')
                }
            }


            async function main() {
                await doCheck('.auth-header', () => {
                    return fetch('/api/troubleshoot/auth-header', {
                        headers: {
                            Authorization: "Bearer dummytoken",
                            Accept: "application/json"
                        }
                    })
                })

                await doCheck('.put-requests', () => {
                    return fetch('/api/troubleshoot/put', {
                        method: 'PUT',
                        headers: {
                            Accept: 'application/json',
                        }
                    })
                })

                await doCheck('.delete-requests', () => {
                    return fetch('/api/troubleshoot/delete', {
                        method: 'DELETE',
                        headers: {
                            Accept: 'application/json',
                        }
                    })
                })
            }

            main()

        })()
    </script>
</body>


</html>
