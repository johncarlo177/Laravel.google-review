import './styles/main.scss'

import './javascript/qrcode-types'

import './javascript/login-button-controller'

import './javascript/gdpr-widget-renderer'

import './javascript/lead-form/index'

import './javascript/body-scroll-blocker'

import './javascript/available-height-resolver'

import './javascript/share-on-whatsapp-renderer'

import './javascript/ads-manager'

import './javascript/go-to-top-renderer'

import './javascript/sticky-header-renderer'

import './javascript/stack-component'

import './javascript/website-banner-background/shader'

import './javascript/website-banner-background/background'

import './javascript/information-popup-renderer'

import './javascript/share-overlay'

import './javascript/iphone-detector'

import './javascript/image-carousel/image-carousel'

import './javascript/whatsapp-buy-button/buy-button'

import './javascript/upi'

//
//
//
;(function () {
    function parentMatches(node, parentSelector) {
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

    function $(selector) {
        return document.querySelector(selector)
    }

    function $$(selector) {
        return Array.from(document.querySelectorAll(selector))
    }

    function initHeader() {
        if (!$('.website-header')) return

        function removeFakeHeader() {
            $('#fake-website-header')?.remove()
        }

        function closeHeader() {
            removeFakeHeader()
            $('.website-header').classList.remove('open')
        }

        $('.website-header .close-btn')?.addEventListener('click', closeHeader)

        $('.website-header .open-btn')?.addEventListener('click', function () {
            $('.website-header').classList.add('open')
        })

        window.addEventListener('resize', function () {
            closeHeader()
        })
    }

    function initQrCodeTypes() {
        $('.qrcode-types .show-more')?.addEventListener('click', function () {
            document.body.classList.add('qrcode-types-show-all')
        })
    }

    function initModals() {
        var pageScrollPosition = 0

        function createClone(target) {
            const clone = document.createElement('div')

            const r = target.getBoundingClientRect()

            clone.style = `position: fixed; top: ${r.top}px; left: ${r.left}px; width: ${r.width}px; height: ${r.height}px; z-index: 1000;`

            clone.classList.add('modal-target-clone')

            $('body').appendChild(clone)

            return clone
        }

        async function maximizeClone(clone) {
            await new Promise((r) => setTimeout(r, 100))

            return new Promise((resolve) => {
                clone.classList.add('maximized')

                clone.addEventListener('transitionend', resolve)
            })
        }

        function addModalContent(modal, parent) {
            modal.appendChild(
                parent.querySelector('.modal-content').cloneNode(true)
            )
        }

        function blockPageScroll() {
            setTimeout(() => {
                pageScrollPosition = window.scrollY
                document.body.classList.add('modal-open')
            }, 0)
        }

        function releasePageScroll() {
            setTimeout(() => {
                document.body.classList.remove('modal-open')

                setTimeout(() => {
                    window.scrollTo(0, pageScrollPosition)
                }, 0)
            }, 0)
        }

        async function onClick(e) {
            const target = e.target

            const clone = createClone(
                parentMatches(target, 'section.qrcode-showcase .wrapper')
            )

            await maximizeClone(clone)

            addModalContent(clone, parentMatches(target, '.qrcode-showcase'))

            blockPageScroll()
        }

        async function minimizeClone(clone) {
            clone?.classList.remove('maximized')

            return new Promise((r) => {
                clone?.addEventListener('transitionend', r)
            })
        }

        async function onModalCloseBtnClick(e) {
            closeModal()
        }

        async function closeModal() {
            const clone = $('.modal-target-clone')

            if (!clone) return

            releasePageScroll()

            await minimizeClone(clone)

            clone.remove()
        }

        function bindEvents() {
            $$('.qrcode-showcase .overlay').forEach((elem) => {
                elem.addEventListener('click', onClick)
            })

            $('body').addEventListener('click', function (e) {
                if (parentMatches(e.target, '.modal-content .close-btn')) {
                    onModalCloseBtnClick(e)
                }
            })

            $('body').addEventListener('keyup', function (e) {
                if (e.key == 'Escape') closeModal()
            })
        }

        bindEvents()
    }

    function initSmoothScrollHashLinks() {
        function hashLinkToCurrentPage(a) {
            const samePage =
                a.pathname == location.pathname && a.host == location.host

            if (!samePage) return false

            try {
                document.querySelector(a.hash)
            } catch {
                return false
            }

            return true
        }

        function onHashLinkClick(e) {
            e.preventDefault()

            const id = e.target.hash

            const elem = $(id)

            if (!elem) return

            history.pushState({}, null, window.location.pathname + id)

            elem.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }

        const links = $$('a').filter((link) => {
            return hashLinkToCurrentPage(link)
        })

        links.forEach((link) => {
            link.addEventListener('click', onHashLinkClick)
        })
    }

    function initPricingPlans() {
        const switchElem = $('.pricing-switch')

        if (!switchElem) {
            return
        }

        function showPricingItemsWithTabId(tabId) {
            const allElems = $$(`.pricing [tab-id]:not(.switch-item)`)

            const elemsToShow = $$(
                `.pricing [tab-id="${tabId}"]:not(.switch-item)`
            )

            for (const elem of allElems) {
                elem.classList.add('immediate-hide')
            }

            for (const elem of elemsToShow) {
                elem.classList.remove('immediate-hide')
            }
        }

        function activatePricingSwitchItem(tabId) {
            const items = $$('section.pricing .switch-item')

            for (const item of items) {
                item.classList.remove('active')
            }

            $(`section.pricing .switch-item[tab-id=${tabId}]`).classList.add(
                'active'
            )
        }

        function renderActiveTabId(tabId) {
            showPricingItemsWithTabId(tabId)

            activatePricingSwitchItem(tabId)
        }

        function onSwitchItemClick(e) {
            const item = e.target

            const tabId = item.getAttribute('tab-id')

            renderActiveTabId(tabId)
        }

        function syncActiveSwitchItem() {
            const activeSwitch = switchElem.querySelector('.switch-item.active')

            const tabId = activeSwitch.getAttribute('tab-id')

            renderActiveTabId(tabId)
        }

        switchElem.querySelectorAll('.switch-item').forEach(function (elem) {
            elem.addEventListener('click', onSwitchItemClick)
        })

        syncActiveSwitchItem()
    }

    function main() {
        const calls = [
            initQrCodeTypes,
            initHeader,
            initModals,
            initSmoothScrollHashLinks,
            initPricingPlans,
        ]

        for (const call of calls) {
            try {
                call()
            } catch (error) {
                console.error(error)
            }
        }
    }

    document.addEventListener('DOMContentLoaded', main)
})()
