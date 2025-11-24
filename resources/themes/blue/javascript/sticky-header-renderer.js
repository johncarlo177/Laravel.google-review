import { BaseRenderer } from './base-renderer'

export class StickyHeaderRenderer extends BaseRenderer {
    get header() {
        return this.$('.website-header')
    }

    get headerHeight() {
        return this.header?.getBoundingClientRect().height
    }

    onWindowScroll() {
        if (!this.header) return

        if (window.scrollY > 800) {
            this.addHeaderPlaceholder()
            this.header?.classList.add('sticky')
        } else {
            this.header?.classList.remove('sticky')
            this.removeHeaderPlaceholder()
        }
    }

    addHeaderPlaceholder() {
        if (this.placeholderIsAdded()) return

        this.header.insertAdjacentElement(
            'beforebegin',
            this.createPlaceholder()
        )
    }

    placeholderIsAdded() {
        return this.$('.website-header-placeholder')
    }

    createPlaceholder() {
        const container = document.createElement('div')

        container.classList.add('website-header-placeholder')

        container.style.height = this.headerHeight + 'px'

        return container
    }

    removeHeaderPlaceholder() {
        this.$('.website-header-placeholder')?.remove()
    }
}

StickyHeaderRenderer.boot()
