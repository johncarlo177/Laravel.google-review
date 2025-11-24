import './restaurant-menu'
import './product-catalogue'
import './bio-links'
import './iframe-block'
import './pincode'
import './event-type'
import './splash-screen'
import './business-review'
import './business-review-validator'

//
//
//
;(function () {
    function $(selector) {
        return document.querySelector(selector)
    }

    function $$(selector) {
        return Array.from(document.querySelectorAll(selector))
    }

    function untilImagesAreLoaded() {
        const promise = Promise.all(
            Array.from(document.images)
                .filter((img) => !img.complete)
                .map(
                    (img) =>
                        new Promise((resolve) => {
                            img.onload = img.onerror = resolve
                        })
                )
        )

        promise.then(function () {
            document.dispatchEvent(new CustomEvent('all-images-loaded'))
        })

        return promise
    }

    async function initElements() {
        async function initMainBanner() {
            const background = $('.bg-video') ?? $('.bg-image')

            if (!background) return

            function resizeImage() {
                const parent = $('.layout-generated-webpage')

                const parentRect = parent.getBoundingClientRect()

                background.style.left = `${parentRect.left}px`
                background.style.width = `${parentRect.width}px`
                background.style.display = 'block'
            }

            // Fix the resize bug because of rendering the browser scrollbar.
            const attempts = [100, 300, 500, 1000]

            attempts.forEach(function (time) {
                setTimeout(() => {
                    resizeImage()
                }, time)
            })

            resizeImage()

            document.addEventListener('request-banner-resize', resizeImage)

            window.addEventListener('resize', resizeImage)
        }

        function initAddButton() {
            const button = $('.button.add-contact.floating')

            if (!button) return

            function positionButton() {
                const layout = $('.layout-generated-webpage')

                const buttonLeft = layout.getBoundingClientRect().right

                const marginLeft = '6rem'

                button.style.left = `calc(${buttonLeft}px - ${marginLeft})`
            }

            window.addEventListener('resize', positionButton)

            positionButton()
        }

        function initVCardBackground() {
            if (!$('.qrcode-type-vcard-plus')) return

            function resizeBackground() {
                const contactsCard = $('.white-card.contacts-card')

                if (!contactsCard) return

                const background = $('.gradient-bg')

                const { top: topContact, height: heightContact } =
                    contactsCard.getBoundingClientRect()

                background.style.height = `${
                    contactsCard.offsetTop + heightContact * 0.7
                }px`
            }

            setTimeout(() => {
                resizeBackground()
            })

            window.addEventListener('resize', resizeBackground)
        }

        function initQRCodeMarginTop() {
            const backgroundImage =
                $('.bg-video-placeholder') ?? $('.bg-image-placeholder')

            const qrcodeContainer = $('.qrcode-container')

            if (!backgroundImage || !qrcodeContainer) return

            function setQrCodeMarginTop() {
                const { height: backgroundImageHeight } =
                    backgroundImage.getBoundingClientRect()

                const { height: qrcodeContainerHeight } =
                    qrcodeContainer.getBoundingClientRect()

                qrcodeContainer.style.marginTop = `-${Math.min(
                    backgroundImageHeight / 4,
                    qrcodeContainerHeight / 1.5
                )}px`
            }

            setQrCodeMarginTop()

            window.addEventListener('resize', setQrCodeMarginTop)
        }

        function initFAQs() {
            const items = $$('.faq-item')

            if (!items.length) return

            $$('.faq-title').forEach((e) =>
                e.addEventListener('click', onFaqTitleClick)
            )

            function onFaqTitleClick(e) {
                const title = e.target

                const item = title.closest('.faq-item')

                const isActive = item.classList.contains('active')

                items.forEach((item) => item.classList.remove('active'))

                if (!isActive) {
                    item.classList.add('active')
                }
            }
        }

        initFAQs()

        initVCardBackground()

        initMainBanner()

        initAddButton()

        initQRCodeMarginTop()
    }

    async function main() {
        if (!document.body.matches('.qrcode-type')) return

        await untilImagesAreLoaded()

        document.body.classList.add('loaded')

        window.dispatchEvent(new CustomEvent('images-loaded'))

        initElements()
    }

    document.addEventListener('DOMContentLoaded', main)
})()
