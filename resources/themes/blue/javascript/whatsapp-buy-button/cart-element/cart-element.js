import style from './cart-element.scss?inline'
import { BaseComponent } from '../../base-component/base-component'
import { html, mapEventDelegate, queryParam } from '../../helpers'
import { WhatsAppCart } from '../whatsapp-cart'

export class CartElement extends BaseComponent {
    cart = new WhatsAppCart()

    set isMinimized(v) {
        if (v) {
            this.setAttribute('is-minimized', 'true')
        } else {
            this.removeAttribute('is-minimized')
        }

        this.requestUpdate()
    }

    get isMinimized() {
        return this.hasAttribute('is-minimized')
    }

    static tag = 'blue-whatsapp-cart-element'

    static styleSheets = [...super.styleSheets, style]

    static register() {
        super.register()

        if (this.isOnRestaurantMenuOrProductCatalogue()) {
            document.body.appendChild(new this())
        }
    }

    static isOnRestaurantMenuOrProductCatalogue() {
        return document.body.matches(
            '.qrcode-type-restaurant-menu,.qrcode-type-product-catalogue'
        )
    }

    constructor() {
        super()

        this.isMinimized = false

        this.isFirstRender = true
    }

    connectedCallback() {
        super.connectedCallback()

        document.addEventListener(
            WhatsAppCart.EVENT_CART_UPDATED,
            this.onCartUpdated
        )

        this.addEventListener('click', this.onClick)
    }

    disconnectedCallback() {
        super.disconnectedCallback()

        document.removeEventListener(
            WhatsAppCart.EVENT_CART_UPDATED,
            this.onCartUpdated
        )

        this.removeEventListener('click', this.onClick)
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onClick(e) {
        mapEventDelegate(e, {
            '.item-remove': this.onRemoveItemClick,
            '.place-order': this.onPlaceOrderClick,
            '.minimized-container': this.onMinizedClick,
            '.close': this.onCloseClick,
            '.minimize-cart': this.onCloseClick,
        })
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onCloseClick = (e) => {
        //
        this.isMinimized = true

        this.requestUpdate()
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onMinizedClick = (e) => {
        //
        this.isMinimized = false
    }

    async getBillingDetails() {
        if (this.getOrderType() === 'table') {
            return `Table Number: ${this.getTableNumber()}`
        }

        return await window.WhatsAppBillingDetailsModal.open()
    }

    async buildMessage() {
        const billingDetails = await this.getBillingDetails()

        const header = 'New Order #' + this.getCurrentOrderNumber()

        const items = this.cart.items
            .map((item, i) => {
                return `${i + 1}. *${item.name}* - ${item.currency}${
                    item.price
                } x ${item.quantity} = ${item.currency}${item.subtotal}`
            })
            .join('\n')

        const total = `Total: *${
            this.cart.items[0].currency
        }${this.cart.getTotal()}*`

        const thanks = `Thanks for shopping with us.`

        const paymentUrl = this.getPaymentUrl()

        const message = [
            header,
            items,
            total,
            billingDetails,
            paymentUrl,
            thanks,
        ]
            .filter((l) => l)
            .join('\n-------------------\n')

        return encodeURIComponent(message)
    }

    getTableNumber() {
        if (queryParam('table-number')) {
            return queryParam('table-number')
        }

        return window.__$$$whatsapp_order$$$table_number__
    }

    getOrderType() {
        const value = window.__$$$whatsapp_order$$$order_type__

        return value
    }

    getMobileNumber() {
        return window.__$$$whatsapp_order$$$mobile_number__
    }

    getPaymentUrl() {
        const url = window.__$$$whatsapp_order$$$payment_url__

        if (!url?.length) {
            return ''
        }

        return (
            window.__$$$whatsapp_order$$$payment_url__ +
            '?amount=' +
            this.cart.getTotal()
        )
    }

    getCurrentOrderNumber() {
        const value = window.__$$$whatsapp_order$$$order_number__

        const number = Number.parseInt(value)

        if (isNaN(number)) {
            return 0
        }

        return number
    }

    getTermsAndConditions() {
        return window.__$$$whatsapp_order$$$whatsapp_order_terms_conditions__
    }

    /**
     *
     * @param {MouseEvent} e
     */
    onPlaceOrderClick = async (e, button) => {
        button.classList.add('loading')
        //
        //
        const message = await this.buildMessage()

        this.cart.items = []

        const form = document.getElementById('whatsapp-order-form')

        const slugInput = form.querySelector('[name=slug]')

        slugInput.value = window.location.pathname.replace(/^\//, '')

        const destinationInput = form.querySelector('[name=destination]')

        destinationInput.value = `https://wa.me/${this.getMobileNumber()}?text=${message}`

        form.submit()
    }

    /**
     *
     * @param {MouseEvent} e
     * @param {HTMLElement} element
     */
    onRemoveItemClick = (e, element) => {
        //

        if (
            !confirm(
                'Are you sure you want to remove this item from shopping cart?'
            )
        ) {
            return
        }

        const id = element.getAttribute('item-id').trim()

        this.cart.removeItem(id)
    }

    formatPrice(price) {
        if (this.cart.hasFractionDigits()) {
            return price.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        }

        return price.toLocaleString('en-US')
    }

    onCartUpdated = () => {
        this.requestUpdate()
    }

    renderItem(item, i) {
        return html`
            <div class="item">
                <div class="item-name">${i + 1}. ${item.name}</div>
                <div class="item-quantity">x${item.quantity}</div>
                <div class="item-price">
                    ${item.currency}${this.formatPrice(item.subtotal)}
                </div>
                <div class="item-remove" item-id="${item.id}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <title>close-thick</title>
                        <path
                            d="M20 6.91L17.09 4L12 9.09L6.91 4L4 6.91L9.09 12L4 17.09L6.91 20L12 14.91L17.09 20L20 17.09L14.91 12L20 6.91Z"
                        />
                    </svg>
                </div>
            </div>
        `
    }

    renderItems() {
        return html`
            <div class="items">
                ${this.cart.items
                    .map((item, i) => {
                        return this.renderItem(item, i)
                    })
                    .join('\n')}
            </div>
        `
    }

    renderPlaceOrderButton() {
        return html`
            <div class="footer">
                <div class="minimize-cart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <title>window-minimize</title>
                        <path d="M20,14H4V10H20" />
                    </svg>
                    <span> Minimize cart </span>
                </div>

                <button class="place-order">
                    <svg
                        role="img"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <title>WhatsApp</title>
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
                        />
                    </svg>
                    <span> Place Order </span>
                </button>
            </div>
        `
    }

    renderTotal() {
        return html`
            <div class="item total">
                <div class="item-name">
                    Total (${this.cart.items.length}
                    item${this.cart.items.length > 1 ? 's' : ''})
                </div>

                <div class="item-price">
                    ${this.cart.items[0].currency}${this.formatPrice(
                        this.cart.getTotal()
                    )}
                </div>
            </div>
        `
    }

    renderHeader() {
        return html`
            <!--  -->
            <header>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    class="cart-icon"
                >
                    <title>cart-outline</title>
                    <path
                        d="M17,18A2,2 0 0,1 19,20A2,2 0 0,1 17,22C15.89,22 15,21.1 15,20C15,18.89 15.89,18 17,18M1,2H4.27L5.21,4H20A1,1 0 0,1 21,5C21,5.17 20.95,5.34 20.88,5.5L17.3,11.97C16.96,12.58 16.3,13 15.55,13H8.1L7.2,14.63L7.17,14.75A0.25,0.25 0 0,0 7.42,15H19V17H7C5.89,17 5,16.1 5,15C5,14.65 5.09,14.32 5.24,14.04L6.6,11.59L3,4H1V2M7,18A2,2 0 0,1 9,20A2,2 0 0,1 7,22C5.89,22 5,21.1 5,20C5,18.89 5.89,18 7,18M16,11L18.78,6H6.14L8.5,11H16Z"
                    />
                </svg>

                <h2>Shopping Cart</h2>

                <div class="close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <title>close-circle-outline</title>
                        <path
                            d="M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2C6.47,2 2,6.47 2,12C2,17.53 6.47,22 12,22C17.53,22 22,17.53 22,12C22,6.47 17.53,2 12,2M14.59,8L12,10.59L9.41,8L8,9.41L10.59,12L8,14.59L9.41,16L12,13.41L14.59,16L16,14.59L13.41,12L16,9.41L14.59,8Z"
                        />
                    </svg>
                </div>
            </header>
        `
    }

    renderMinimizedMode() {
        //

        return html`
            <div class="minimized-container">
                <div class="minimized-cart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <title>cart-outline</title>
                        <path
                            d="M17,18A2,2 0 0,1 19,20A2,2 0 0,1 17,22C15.89,22 15,21.1 15,20C15,18.89 15.89,18 17,18M1,2H4.27L5.21,4H20A1,1 0 0,1 21,5C21,5.17 20.95,5.34 20.88,5.5L17.3,11.97C16.96,12.58 16.3,13 15.55,13H8.1L7.2,14.63L7.17,14.75A0.25,0.25 0 0,0 7.42,15H19V17H7C5.89,17 5,16.1 5,15C5,14.65 5.09,14.32 5.24,14.04L6.6,11.59L3,4H1V2M7,18A2,2 0 0,1 9,20A2,2 0 0,1 7,22C5.89,22 5,21.1 5,20C5,18.89 5.89,18 7,18M16,11L18.78,6H6.14L8.5,11H16Z"
                        />
                    </svg>
                    <div class="text">
                        ${window.__$$$whatsapp_order$$$open_cart_text__}
                        ${this.cart.items[0].currency}${this.cart.getTotal()}
                    </div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        class="arrow"
                    >
                        <title>arrow-right-thin</title>
                        <path
                            d="M14 16.94V12.94H5.08L5.05 10.93H14V6.94L19 11.94Z"
                        />
                    </svg>
                </div>
            </div>
        `
    }

    renderTerms() {
        if (!this.getTermsAndConditions()?.length) {
            return ''
        }

        return html` <div class="terms">${this.getTermsAndConditions()}</div> `
    }

    render() {
        if (!WhatsAppCart.items.length) {
            return
        }

        if (this.isMinimized) {
            return this.renderMinimizedMode()
        }

        const $class = this.isFirstRender ? 'widget first-render' : 'widget'

        this.isFirstRender = false

        return html`
            <div class="${$class}">
                <!--  -->
                ${this.renderHeader()}

                <!--  -->
                ${this.renderItems()}

                <!--  -->
                ${this.renderTotal()}
                <!--  -->
                ${this.renderPlaceOrderButton()}
                <!--  -->
                ${this.renderTerms()}
            </div>
            <!--  -->
        `
    }
}

CartElement.register()
