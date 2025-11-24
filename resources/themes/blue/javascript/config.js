const base64Decode = (base64EncodedString) => {
    if (typeof base64EncodedString == 'undefined') {
        return base64EncodedString
    }

    return new TextDecoder().decode(
        Uint8Array.from(window.atob(base64EncodedString), (m) =>
            m.codePointAt(0)
        )
    )
}

const decodeValue = (key) => {
    if (window.QRCG_BUNDLE_TYPE == 'build') {
        key = window.btoa(key)
    }

    const configObject = window.CONFIG

    if (!configObject) return null

    let value = configObject[key]

    if (window.QRCG_BUNDLE_TYPE == 'build') {
        try {
            value = base64Decode(value)
        } catch {
            console.log('error while decoding string: ', { key, value })
        }
    }

    return value
}

export const Config = {
    get(key, type = String) {
        const value = decodeValue(key)

        if (type === Boolean) {
            if (value === 'true' || value === '1') {
                return true
            }

            return false
        }

        try {
            return JSON.parse(value)
        } catch {
            return value
        }
    },
}
