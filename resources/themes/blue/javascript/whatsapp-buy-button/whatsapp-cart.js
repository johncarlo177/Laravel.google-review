import { ButtonModel } from './button-model'
import { WhatsAppCartItemModel } from './cart-item-model'

export class WhatsAppCart {
    /**
     * @type {WhatsAppCartItemModel[]}
     */
    static get items() {
        try {
            const rawItems = localStorage[this.getLocalStorageKey()]

            return JSON.parse(rawItems)
        } catch {
            this.items = []

            return this.items
        }
    }

    static set items(v) {
        localStorage[this.getLocalStorageKey()] = JSON.stringify(v)
    }

    static getLocalStorageKey() {
        const slug = window.location.pathname.replaceAll('/', '')

        return `whatsapp-cart[${slug}].items`
    }

    static EVENT_CART_UPDATED = 'whatsapp-cart:updated'

    get items() {
        return WhatsAppCart.items
    }

    set items(v) {
        WhatsAppCart.items = v
    }

    getTotal() {
        return this.items.reduce((sum, item) => {
            return item.subtotal + sum
        }, 0)
    }

    removeItem(id) {
        this.items = this.items.filter((_item) => {
            //
            const equals = _item.id !== id

            return equals
        })

        this.notifyUpdatedEvent()
    }

    addItem(item) {
        if (!item) {
            return
        }
        //
        const currentItem = this.itemIsAdded(item)

        if (currentItem) {
            //
            currentItem.quantity += item.quantity

            currentItem.subtotal = currentItem.quantity * currentItem.price

            this.items = this.items.map((_item) => {
                if (_item.id === currentItem.id) {
                    return currentItem
                }

                return _item
            })
            //
        } else {
            //
            item.subtotal = item.quantity * item.price

            this.items = [...this.items, item]
        }

        this.notifyUpdatedEvent()
    }

    itemIsAdded(item) {
        return this.items.find((i) => i.id === item.id)
    }

    hasFractionDigits() {
        return this.items.reduce((hasFraction, item) => {
            return hasFraction || item.rawPrice?.match(/,/)
        }, false)
    }

    /**
     *
     * @param {HTMLButtonElement} button
     */
    addItemFromButton(button) {
        //
        this.addItem(ButtonModel.withButton(button).toModel())
    }

    notifyUpdatedEvent() {
        document.dispatchEvent(new CustomEvent(WhatsAppCart.EVENT_CART_UPDATED))
    }
}
