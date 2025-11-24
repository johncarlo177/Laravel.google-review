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

    onCloneClick(item) {
        this.hideClone(item)
    }

    /**
     *
     * @param {HTMLElement} item
     */
    async hideClone(item) {
        item.classList.add('hide')

        item.classList.remove('open')

        await waitForTransition(item)

        setTimeout(() => {
            item.__original_node__.classList.remove('clone-is-open')
        }, 40)

        setTimeout(() => {
            item.remove()
        }, 50)
    }

    async hideAllClones() {
        const clones = this.$$('.stack-item.clone')

        for (const clone of clones) {
            await this.hideClone(clone)
        }
    }

    /**
     *
     * @param {HTMLElement} item
     */
    async onItemClick(item) {
        await this.hideAllClones()

        /**
         * @type {HTMLElement}
         */
        const clone = this.createClone(item)

        this.bindCloneBoundaries(clone, item)

        this.revealClone(clone)
    }

    /**
     *
     * @param {HTMLElement} clone
     */
    revealClone(clone) {
        //
        const tmp = this.createTmpClone(clone)

        setTimeout(async () => {
            const currentTop = +clone.style.top.replace(/px/, '')

            //
            const newTop = currentTop - this.getContentHeight(tmp)

            clone.style.top = newTop + 'px'

            clone.classList.add('visible', 'open')
        }, 0)
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

        node.__original_node__.classList.remove('clone-is-open')

        node.classList.add('open')

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

        clone.style.zIndex = item.computedStyleMap().get('z-index')

        clone.__original_node__ = item

        item.classList.add('clone-is-open')

        return clone
    }
}

StackComponent.boot()
