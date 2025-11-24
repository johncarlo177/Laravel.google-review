import { BaseComponent } from '../base-component/base-component'
import { Config } from '../config'

import FRAGMENT_SHADER from './background.glsl?raw'

import style from './background.scss?inline'

import { WebsiteBannerShader } from './shader'

class WebsiteBannerBackground extends BaseComponent {
    static tag = 'blue-website-banner-background'

    static styleSheets = [...super.styleSheets, style]

    static COLOR_BACKGROUND_COLOR = '#081838FF'

    static COLOR_1 = '#48A9A6FF'
    static COLOR_2 = '#48A9A600'
    static COLOR_3 = '#194Eb5FF'
    static COLOR_4 = '#48A9A600'

    firstUpdated() {
        this.init()
    }

    getColor(defaultColor, name, transperancy = 'FF') {
        const value = Config.get(name)

        if (!value || value.length === 0) {
            return defaultColor
        }

        return value + transperancy
    }

    init() {
        /** @type {WebsiteBannerShader}*/
        this.shaderRenderer = WebsiteBannerShader.findSelf(this.shadowRoot)
        this.shaderRenderer.setFragmentShader(FRAGMENT_SHADER)

        // Float uniforms
        this.shaderRenderer.setFloatUniform('topRightCircleRadius', 0.5)
        this.shaderRenderer.setFloatUniform('bottomLeftCircleRadius', 0.5)

        // Vec2 uniforms
        this.shaderRenderer.setFVecUniform(
            'resolution',
            new Float32Array([window.innerWidth, window.innerHeight])
        )
        this.shaderRenderer.setFVecUniform(
            'topRightCircleScale',
            new Float32Array([0.2, 0.2])
        )
        this.shaderRenderer.setFVecUniform(
            'bottomLeftCircleScale',
            new Float32Array([0.2, 0.2])
        )

        // Vec4 uniforms (using rgbaVec4 conversion function)
        this.shaderRenderer.setColorRGBAUniform(
            'backgroundColor',
            this.getColor(
                WebsiteBannerBackground.COLOR_BACKGROUND_COLOR,
                'website-banner-background-color'
            )
        )

        this.shaderRenderer.setColorRGBAUniform(
            'topRightCircleColor1',
            this.getColor(
                WebsiteBannerBackground.COLOR_1,
                'website-banner-color-1'
            )
        ) // Green gradient color 1

        this.shaderRenderer.setColorRGBAUniform(
            'topRightCircleColor2',
            this.getColor(
                WebsiteBannerBackground.COLOR_2,
                'website-banner-color-2',
                '00'
            )
        ) // Green gradient color 2

        this.shaderRenderer.setColorRGBAUniform(
            'bottomLeftCircleColor1',
            this.getColor(
                WebsiteBannerBackground.COLOR_3,
                'website-banner-color-3'
            )
        ) // Blue gradient color 1

        this.shaderRenderer.setColorRGBAUniform(
            'bottomLeftCircleColor2',
            this.getColor(
                WebsiteBannerBackground.COLOR_4,
                'website-banner-color-4',
                '00'
            )
        )
    }

    render() {
        return WebsiteBannerShader.renderSelf()
    }
}

WebsiteBannerBackground.register()
