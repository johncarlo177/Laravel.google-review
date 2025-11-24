import { ScriptLoader } from '../core/script-loader'
import { StyleSheetLoader } from '../core/stylesheet-loader'
import style from './image-carousel.scss?inline'

export class ImageCarousel {
    loop = false
    autoPlay = false
    autoplayInterval = 3000
    slidesToShow = 1

    /**
     * @type {HTMLElement}
     */
    element = null

    static register() {
        document.addEventListener('DOMContentLoaded', () => this.boot())

        this.boot()
    }

    static boot() {
        if (this.__didBoot) {
            return
        }

        document.querySelectorAll('.image-carousel').forEach((elem) => {
            this.createCarouselForDomElement(elem)
        })

        this.injectStyle()

        this.__didBoot = true
    }

    static injectStyle() {
        const tag = document.createElement('style')

        tag.innerHTML = style

        document.head.appendChild(tag)
    }

    /**
     *
     * @param {HTMLElement} element
     */
    static createCarouselForDomElement(element) {
        const rawOptions = element.getAttribute('options')

        const jsonString = window.atob(rawOptions)

        const options = JSON.parse(jsonString)

        return this.withElement(element).withOptions(options).init()
    }

    static withElement(element) {
        const instance = new this()

        instance.element = element

        return instance
    }

    withOptions(optionsObject) {
        Object.keys(optionsObject).forEach((key) => {
            this[key] = optionsObject[key]
        })

        console.log(this)

        return this
    }

    withAutoPlay(autoPlay = true) {
        this.autoPlay = autoPlay

        return this
    }

    withAutoPlayInterval(ms = 3000) {
        this.autoPlayInterval = ms

        return this
    }

    load() {
        return Promise.all([
            ScriptLoader.withUrl(
                '/assets/lib/blaze-slider@1.9.3/blaze-slider.min.js'
            ).load(),
            StyleSheetLoader.withUrl(
                '/assets/lib/blaze-slider@1.9.3/blaze.css'
            ).inject(),
        ])
    }

    getNumber(value, defaultValue = 1) {
        const number = +value

        if (isNaN(number)) {
            return defaultValue
        }

        return number
    }

    async init() {
        await this.load()

        const config = {
            enableAutoplay: this.autoPlay, // <--
            slidesToShow: this.getNumber(this.slidesToShow),
            stopAutoplayOnInteraction: true,
            autoplayInterval: this.autoplayInterval,
            loop: this.loop,
        }

        new BlazeSlider(this.element.querySelector('.blaze-slider'), {
            all: config,
        })
    }
}

ImageCarousel.register()
