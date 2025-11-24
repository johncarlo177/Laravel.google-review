import { BaseRenderer } from './base-renderer'
import { mapEventDelegate } from './helpers'

class UPIRenderer extends BaseRenderer {
    shouldRun() {
        return this.$('.qrcode-type-upi-dynamic')
    }

    onDocumentClick(e) {
        mapEventDelegate(e, {
            '.download-upi-qrcode': this.onDownloadUPIQRCodeClick,
            '.payment-url-button': this.onPaymentUrlButtonClick,
        })
    }

    /**
     *
     * @param {MouseEvent} e
     * @param {HTMLAnchorElement} button
     */
    onPaymentUrlButtonClick = (e, button) => {
        e.preventDefault()
        e.stopImmediatePropagation()

        const url = button.getAttribute('payment-url')

        this.openLinkInNewTab(url)
    }

    onDownloadUPIQRCodeClick = (e, button) => {
        e.preventDefault()

        e.stopImmediatePropagation()

        const image = this.$('.upi-qrcode img')
        this.downloadImageAsPNG(image)
    }

    getImageBackgroundColor() {
        const css = getComputedStyle(this.$('.download-upi-qrcode'))

        return css.backgroundColor
    }

    getImageTextColor() {
        const css = getComputedStyle(this.$('.download-upi-qrcode'))

        return css.color
    }

    downloadImageAsPNG(imgElement, scale = 3) {
        const canvas = document.createElement('canvas')
        const context = canvas.getContext('2d')

        const padding = 20

        const rectHeight = 20

        const imgWidth = imgElement.naturalWidth * scale
        const imgHeight = imgElement.naturalHeight * scale
        const totalHeight = imgHeight + rectHeight * scale

        const borderWidth = imgWidth + padding * 2
        const borderHeight = totalHeight + padding * 3

        canvas.width = borderWidth
        canvas.height = borderHeight

        // Fill background with white
        context.fillStyle = 'white'
        context.fillRect(0, 0, borderWidth, borderHeight)

        // Draw the scaled image
        context.drawImage(imgElement, padding, padding, imgWidth, imgHeight)

        this.drawText(context, 'UPI ₹' + this.getAmount(), imgWidth, imgHeight)

        // Create PNG and download
        canvas.toBlob((blob) => {
            const link = document.createElement('a')
            link.href = URL.createObjectURL(blob)
            link.download = 'UPI.png'
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
            URL.revokeObjectURL(link.href)
        }, 'image/png')
    }

    getAmount() {
        return this.$('.download-upi-qrcode').getAttribute('amount')
    }

    /**
     *
     * @param {CanvasRenderingContext2D} context
     * @param {*} text
     * @param {*} imgWidth
     * @param {*} imgHeight
     * @param {*} rectHeight
     * @param {*} scale
     * @param {*} backgroundColor
     * @param {*} textColor
     * @param {*} padding
     */
    drawText(
        context,
        text,
        imgWidth,
        imgHeight,
        rectHeight = 20,
        scale = 3,

        padding = 20
    ) {
        const backgroundColor = this.getImageBackgroundColor()

        const textColor = this.getImageTextColor()

        // Draw the rectangle below the image
        context.fillStyle = backgroundColor

        const y = imgHeight + padding * 2

        context.fillRect(padding, y, imgWidth, rectHeight * scale)

        // Draw the text in the center of the rectangle
        context.fillStyle = textColor
        context.font = `${rectHeight + rectHeight / 2}px sans-serif`
        context.textAlign = 'center'
        context.textBaseline = 'middle'

        context.fillText(text, imgWidth / 2, y + (rectHeight * scale) / 2)
    }
}

UPIRenderer.boot()
