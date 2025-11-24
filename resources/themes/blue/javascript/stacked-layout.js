import { BaseRenderer } from './base-renderer'

export class StackedLayout extends BaseRenderer {
    slug() {
        throw new Error('slug is not specified')
    }

    shouldRun() {
        return !!this.$('.qrcode-type-' + this.slug())
    }

    onDocumentClick = (e) => {
        const element = e.target

        if (element.matches('.menu-category')) {
            this.onMenuCategoryClick(e)
        }

        if (element.matches('.close-button')) {
            this.onMenuCategoryClose(e)
        }

        if (element.matches('.sub-category')) {
            this.onSubCategoryClick(e)
        }

        if (element.matches('.menu-item .image-container')) {
            this.onImageClick(e)
        }
    }

    onImageClick(e) {
        const div = document.createElement('div')

        div.classList.add('restaurant-menu-image-overlay')

        div.style = ''

        const img = e.target.cloneNode()

        if (img.matches('.image-container')) {
            img.style.backgroundSize = 'contain'
            img.style.backgroundRepeat = 'no-repeat'
        }

        const style = document.createElement('style')

        style.innerHTML = `
                .restaurant-menu-image-overlay {
                    position: fixed;
                    top: 0;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background-color: #eee;
                    z-index: 1000;
                    display: flex;

                    animation: image-overlay-main-animation 0.25s ease both;
                }

                .restaurant-menu-image-overlay > * {
                    width: 90%;
                    height: 90%;
                    object-fit: contain;
                    margin: auto;
                    pointer-events: none;
                    animation: image-overlay-animation 0.5s ease both;
                }

                @keyframes image-overlay-animation {
                    from {
                        opacity: 0.3;
                        transform: translateY(10px) scale(0.99);
                    }

                    50% {
                        opacity: 1;
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }

                @keyframes image-overlay-main-animation {
                    from {
                        opacity: 0;
                    }

                    to {
                        opacity: 1;
                    }
                }
            `

        document.head.appendChild(style)

        div.appendChild(img)

        const onKeyup = (e) => {
            if (e.key != 'Escape') return

            clearup(e)
        }

        const clearup = (e) => {
            e.stopImmediatePropagation()
            e.preventDefault()
            div.remove()
            style.remove()
            document.removeEventListener('keyup', onKeyup)
        }

        div.addEventListener('click', clearup)

        document.addEventListener('keyup', onKeyup)

        document.body.appendChild(div)
    }

    onSubCategoryClick(e) {
        const subCategoryId = e.target.getAttribute('slug')

        const page = e.target.closest('.category-page')

        const allSubcategories = Array.from(
            page.querySelectorAll('.sub-category')
        )

        const allItems = Array.from(page.querySelectorAll('.menu-item'))

        const subCategoryItems = Array.from(
            page.querySelectorAll(
                `.menu-item[category-slug="${subCategoryId}"]`
            )
        )

        allSubcategories.forEach((subCategory) =>
            subCategory.classList.remove('active')
        )

        e.target.classList.add('active')

        if (e.target.matches('.show-all')) {
            page.classList.remove('sub-category-active')

            allItems.forEach((i) => i.classList.remove('current-sub-category'))

            return
        }

        page.classList.add('sub-category-active')

        allItems.forEach((item, i) => {
            item.classList.remove('current-sub-category')
            item.style.transitionDelay = i * 0.05 + 's'
        })

        setTimeout(() => {
            subCategoryItems.forEach((item) => {
                item.classList.add('current-sub-category')
            })
        }, 50)
    }

    onCategoryPageOpen = async (e) => {
        const { categoryPage, promise } = e.detail

        await promise

        await new Promise((r) => setTimeout(r, 100))

        const allItems = Array.from(categoryPage.querySelectorAll('.menu-item'))

        allItems.forEach((item) => {
            // disable max height with lazy loading
            item.style.maxHeight = 'unset'

            return

            const initialHeight = item.getAttribute('initial-height')

            if (initialHeight) {
                item.style.maxHeight = initialHeight
            } else {
                item.style.maxHeight =
                    item.getBoundingClientRect().height + 'px'
            }

            if (!initialHeight)
                item.setAttribute(
                    'initial-height',
                    item.getBoundingClientRect().height + 'px'
                )
        })

        this.activateFirstSubCategory(categoryPage)
    }

    activateFirstSubCategory(categoryPage) {
        categoryPage?.querySelector('.sub-category')?.dispatchEvent(
            new Event('click', {
                bubbles: true,
            })
        )
    }

    hideCategoryPage(e) {
        const page = this.$('.category-page.open')

        const slug = page.getAttribute('slug')

        const category = this.$(`.menu-category[slug="${slug}"]`)

        page.classList.add('closing')

        this.bindPageToCategoryBoundary(page, category)

        page.style.borderTopLeftRadius = '2rem'
        page.style.borderTopRightRadius = '2rem'

        const onTransitionEnd = () => {
            page.classList.remove('open')
            page.classList.remove('animated')
            page.classList.remove('closing')
            page.removeEventListener('transitionend', onTransitionEnd)
        }

        page.addEventListener('transitionend', onTransitionEnd)
    }

    bindPageToCategoryBoundary(page, category) {
        page.style.top = `${category.getBoundingClientRect().top}px`

        page.style.bottom = `calc(100vh - ${
            category.getBoundingClientRect().bottom
        }px + 2rem)`
    }

    showAllCategories() {
        for (const c of this.$$('.menu-category')) {
            c.classList.remove('hidden')
            c.classList.remove('selected')
        }
    }

    hideOtherCategories(category) {
        for (const c of this.$$('.menu-category')) {
            c.classList.add('hidden')
            c.classList.remove('selected')
        }

        category.classList.add('selected')
        category.classList.remove('hidden')
    }

    openCategoryPage(category) {
        const slug = category.getAttribute('slug')

        const selector = `.category-page[slug="${slug}"]`

        const categoryPage = this.$(selector)

        categoryPage.classList.add('open')

        categoryPage.style.zIndex = getComputedStyle(category).zIndex

        categoryPage.style.backgroundColor =
            getComputedStyle(category).backgroundColor

        categoryPage.classList.remove('sub-category-active')

        const categoryPageTitle = categoryPage.querySelector(
            '.category-page-title'
        )

        categoryPageTitle.style.color = getComputedStyle(category).color

        this.bindPageToCategoryBoundary(categoryPage, category)

        setTimeout(() => {
            categoryPage.classList.add('animated')
            categoryPage.style.top = 0
            categoryPage.style.bottom = 0
            categoryPage.style.borderTopRightRadius = 0
            categoryPage.style.borderTopLeftRadius = 0
        }, 50)

        this.openCategoryPagePromise = new Promise((resolve) => {
            const onTransitionEnd = () => {
                resolve()

                categoryPage.removeEventListener(
                    'transitionend',
                    onTransitionEnd
                )
            }

            categoryPage.addEventListener('transitionend', onTransitionEnd)
        })

        document.dispatchEvent(
            new CustomEvent('category-page:open', {
                detail: {
                    categoryPage,
                    promise: this.openCategoryPagePromise,
                },
            })
        )

        this.hideShareOverlayTrigger()

        this.bindCloseButtonPositions()
    }

    bindCloseButtonPositions() {
        this.$$('.close-button').forEach(
            /**
             *
             * @param {HTMLElement} button
             */
            (button) => {
                const layout = this.$('.layout-generated-webpage')

                const { right } = layout.getBoundingClientRect()

                button.style.right = `calc(100vw - ${right}px + 1rem)`
            }
        )
    }

    getShareOverlayTrigger() {
        return this.$('.share-overlay-trigger')
    }

    hideShareOverlayTrigger() {
        const trigger = this.getShareOverlayTrigger()

        if (!trigger) return

        trigger.style.display = 'none'
    }

    showShareOverlayTrigger() {
        const trigger = this.getShareOverlayTrigger()

        if (!trigger) return

        trigger.style.display = 'flex'
    }

    restoreScroll() {
        window.scrollTo(0, this.lastWindowScrollTop)
    }

    saveScrollPosition() {
        this.lastWindowScrollTop = window.scrollY
    }

    scrollToTop() {
        window.scrollTo(0, 0)
    }

    unblockBodyScroll() {
        document.body.classList.remove('block-scroll')
    }

    blockBodyScroll() {
        document.body.classList.add('block-scroll')
    }

    async onMenuCategoryClick(e) {
        const category = e.target

        this.hideOtherCategories(category)

        this.saveScrollPosition()

        this.openCategoryPage(category)

        this.bindPagesToGeneratedLayoutWidth()

        await this.openCategoryPagePromise

        this.scrollToTop()

        this.blockBodyScroll()

        this.bindPagesToGeneratedLayoutWidth()
    }

    onMenuCategoryClose(e) {
        this.showShareOverlayTrigger()

        this.unblockBodyScroll()
        this.bindPagesToGeneratedLayoutWidth()
        this.restoreScroll()

        setTimeout(() => {
            this.hideCategoryPage(e)
            this.showAllCategories()
        }, 10)
    }

    bindPagesToGeneratedLayoutWidth = () => {
        this.$$('.category-page').forEach((page) => {
            const parent = page.closest('.layout-generated-webpage')

            const { left, width, right } = parent.getBoundingClientRect()

            page.style.left = `${left}px`
            page.style.width = `${width}px`
            page.style.right = `calc(100vw - ${right}px)`
        })
    }

    scrollToBottom(duration, shouldStop) {
        // When should we finish?
        var finishAt = Date.now() + duration

        // Start
        requestAnimationFrame(tick)

        const animationElement = document.documentElement

        function tick() {
            if (shouldStop()) {
                return
            }

            // How many frames left? (60fps = 16.6ms per frame)
            var framesLeft = (finishAt - Date.now()) / 16.6

            // How far do we have to go?
            var distance =
                document.body.offsetHeight -
                (window.innerHeight + window.scrollY)

            if (distance <= 0) {
                // Done (this shouldn't happen, belt & braces)
                return
            }

            // Adjust by one frame's worth
            if (framesLeft <= 1) {
                // Last call
                animationElement.scrollTop += distance
            } else {
                // Not the last, adjust and schedule next
                animationElement.scrollTop += Math.max(1, distance / framesLeft)

                requestAnimationFrame(tick)
            }
        }
    }

    async autoScrollDown() {
        await new Promise((r) => setTimeout(r, 1500))

        let scrollingUp = false,
            touched = false,
            clicked = false

        this.scrollToBottom(5000, () => {
            return scrollingUp || clicked || touched
        })

        const onWindowScroll = () => {
            if (this.autoScrollDown_lastWindowScroll > window.scrollY) {
                scrollingUp = true
            }

            this.autoScrollDown_lastWindowScroll = window.scrollY
        }

        this.autoScrollDown_lastWindowScroll = 0

        window.addEventListener('scroll', onWindowScroll)

        window.addEventListener('click', () => {
            clicked = true
        })

        window.addEventListener('touchstart', () => {
            touched = true
        })
    }

    onDomContentLoaded() {
        if (!this.shouldRun()) return

        window.addEventListener('resize', this.bindPagesToGeneratedLayoutWidth)

        setTimeout(this.bindPagesToGeneratedLayoutWidth, 100)

        document.addEventListener('all-images-loaded', () =>
            this.autoScrollDown()
        )

        document.addEventListener('category-page:open', this.onCategoryPageOpen)
    }
}
