import { BaseRenderer } from './base-renderer'
import { remToPx, waitForTransition } from './helpers'

export class StackComponent extends BaseRenderer {
    //
    onDocumentClick(e) {
        //
        if (!e.target.closest('.stack-component')) {
            return
        }

        const item = e.target.closest('.stack-item')

        this.onItemClick(item)
    }

    getItems() {
        return this.$$('.stack-component .stack-item')
    }

    async hideAllItems() {
        const items = this.getItems()

        for (const item of items) {
            item.classList.remove('open')
        }
    }

    itemHasContent(item) {
        const contents = item.querySelectorAll('.stack-item-content > *')

        return contents.length > 0
    }

    /**
     *
     * @param {HTMLElement} item
     */
    async onItemClick(item) {
        await this.hideAllItems()

        if (!this.itemHasContent(item)) {
            return
        }

        this.openItem(item)
    }

    openItem(item) {
        const height = this.calculateContentHeight(item)

        item.style.setProperty('--max-content-height', height + 'px')

        item.classList.add('open')
    }

    calculateContentHeight(item) {
        return 5000

        const tmp = this.createTmpClone(item)

        return this.getContentHeight(tmp)
    }

    /**
     *
     * @param {HTMLElement} item
     */
    getContentHeight(item) {
        const content = item.querySelector('.stack-item-content')

        const { height } = content.getBoundingClientRect()

        return height
    }

    /**
     *
     * @param {HTMLElement} subject
     * @param {HTMLElement} original
     */
    bindCloneBoundaries(subject, original) {
        const rect = original.getBoundingClientRect()

        const bind = (property, addValue = 0) => {
            //
            const result = rect[property] + addValue

            subject.style[property] = result + 'px'

            subject.style.setProperty('--bound-' + property, result + 'px')
        }

        bind('top', remToPx(2))

        bind('left')
        bind('width')
    }

    createTmpClone(item) {
        const node = this.createClone(item, 'tmp')

        node.classList.add('open')

        this.bindCloneBoundaries(node, item)

        setTimeout(() => {
            node.remove()
        }, 10)

        return node
    }

    /**
     *
     * @param {HTMLElement} item
     *
     * @returns {HTMLElement}
     */
    createClone(item, cloneClass = 'clone') {
        /**
         * @type {HTMLElement}
         */
        const clone = item.cloneNode(true)

        item.after(clone)

        clone.classList.add(cloneClass)

        clone.style.zIndex = getComputedStyle(item).zIndex

        return clone
    }
}

StackComponent.boot()
