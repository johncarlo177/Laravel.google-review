import { WhatsAppCartItemModel } from './cart-item-model'

export class ButtonModel {
    /**
     * @type {HTMLButtonElement}
     */
    button = null

    static withButton(button) {
        const instance = new this()

        instance.button = button

        return instance
    }

    /**
     * @return {HTMLDivElement}
     */
    getPriceContainer() {
        return this.button.closest('.price-container')
    }

    isVariation() {
        return this.getPriceContainer().querySelector('select')
    }

    getSelectedVariation() {
        return this.getPriceContainer().querySelector('option:checked')
    }

    getCurrentVariationPrice() {
        return this.getSelectedVariation().getAttribute('variation-price')
    }

    getCurrentVariationId() {
        return this.getSelectedVariation().getAttribute('variation-id')
    }

    getCurrentVariationName() {
        return this.getSelectedVariation().getAttribute('variation-name')
    }

    guessName() {
        const itemName = this.button.getAttribute('item-name')

        if (!this.isVariation()) {
            return itemName
        }

        return `${itemName} - ${this.getCurrentVariationName()}`
    }

    guessCurrency(rawPrice) {
        return rawPrice
            .replace(/,/g, '')
            .replace(/[0-9\.\/\-]/g, '')
            .trim()
    }

    guessRawPrice() {
        const itemPrice = this.button.getAttribute('item-price')

        if (!this.isVariation()) {
            return itemPrice
        }

        return this.getCurrentVariationPrice()
    }

    guessItemId() {
        const itemId = this.button.getAttribute('item-id')

        if (this.isVariation()) {
            return `${itemId}-${this.getCurrentVariationId()}`
        }

        return itemId
    }

    /**
     *
     * @returns {WhatsAppCartItemModel}
     */
    toModel() {
        const name = this.guessName()

        const id = this.guessItemId()

        const rawPrice = this.guessRawPrice()

        const price = rawPrice.replace(/[^0-9\.]/g, '').trim()

        const currency = this.guessCurrency(rawPrice)

        if (isNaN(+price) || price === '') {
            return null
        }

        const item = new WhatsAppCartItemModel()

        item.rawPrice = rawPrice

        item.currency = currency

        item.name = name

        item.id = id

        item.price = price

        item.quantity = 1

        return item
    }
}
