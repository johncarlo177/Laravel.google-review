import { BaseRenderer } from '../base-renderer'
import { WhatsAppCart } from './whatsapp-cart'

import './cart-element/cart-element'

export class WhatsAppBuyButton extends BaseRenderer {
    cart = new WhatsAppCart()

    /**
     *
     * @param {MouseEvent} e
     */
    onDocumentClick(e) {
        const buyButton = e.target.closest('.whatsapp-buy-button')

        if (buyButton) {
            this.onBuyButtonClick(buyButton)
        }
    }

    disableAllButtonsExcept(button) {
        this.$$('.whatsapp-buy-button').forEach((_button) => {
            if (button === _button) {
                return
            }
            _button.setAttribute('disabled', 'disabled')
        })
    }

    releaseAllButtons() {
        this.$$('.whatsapp-buy-button').forEach((_button) => {
            _button.removeAttribute('disabled')
        })
    }

    /**
     *
     * @param {HTMLElement} button
     */
    onBuyButtonClick(button) {
        this.disableAllButtonsExcept(button)

        if (this.__isBuying) {
            return
        }

        this.__isBuying = true

        button.classList.add('loading')

        setTimeout(() => {
            //
            this.cart.addItemFromButton(button)

            button.classList.remove('loading')

            this.__isBuying = false

            this.releaseAllButtons()
        }, 700)
    }
}

WhatsAppBuyButton.boot()
