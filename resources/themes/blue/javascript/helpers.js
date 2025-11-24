export function pxToRem(px) {
    return px / parseFloat(getComputedStyle(document.documentElement).fontSize)
}

export function remToPx(rem) {
    return rem * parseFloat(getComputedStyle(document.documentElement).fontSize)
}

/**
 *
 * @param {HTMLElement} elem
 */
export function waitForTransition(elem) {
    return new Promise((resolve) => {
        const onEnd = () => {
            resolve()

            elem.removeEventListener('transitionend', onEnd)
        }

        elem.addEventListener('transitionend', onEnd)
    })
}

/**
 *
 * @param {HTMLElement} elem
 */
export function waitForAnimation(elem) {
    return new Promise((resolve) => {
        const onEnd = () => {
            resolve()

            elem.removeEventListener('animationend', onEnd)
        }

        elem.addEventListener('animationend', onEnd)
    })
}

export function iPhoneSafari() {
    return !!navigator.userAgent.match('iPhone OS')
}

export function html(strings, ...rest) {
    return strings
        .map((str, i) => {
            return str + (rest[i] ? rest[i] : '')
        })
        .join('')
}

export const mapEventDelegate = (e, map) => {
    const target = e.composedPath()[0]

    Object.keys(map).forEach((selector) => {
        const matched = parentMatches(target, selector)

        if (matched) {
            const callback = map[selector]

            callback(e, matched)
        }
    })
}

export function parentMatches(node, parentSelector) {
    do {
        if (node.nodeType == Node.DOCUMENT_FRAGMENT_NODE) {
            node = node.getRootNode().host
        }

        if (node.matches && node.matches(parentSelector)) {
            return node
        }

        node = node.parentNode
    } while (node)

    return null
}

export const queryParam = (name) => {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    })

    return params[name]
}
