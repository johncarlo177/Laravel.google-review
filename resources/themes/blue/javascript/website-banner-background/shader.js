import html from './shader.html?raw'

import INITIAL_VERTEX_SHADER_SOURCE from './shader-vertex-shader.glsl?raw'
import INITIAL_FRAGMENT_SHADER_SOURCE from './shader-fragement-shader.glsl?raw'
import { BaseComponent } from '../base-component/base-component'

import { Matrix } from './matrix'

export class WebsiteBannerShader extends BaseComponent {
    static tag = 'blue-website-banner-shader'

    firstUpdated() {
        this.canvas = this.shadowRoot.querySelector('canvas')
        this.canvas.width = this.canvas.clientWidth
        this.canvas.height = this.canvas.clientHeight

        /** @type {WebGL2RenderingContext} */
        this.gl = this.canvas.getContext('webgl')

        this.validateGL()
        this.clear()

        const vsSource = INITIAL_VERTEX_SHADER_SOURCE
        const fsSource = INITIAL_FRAGMENT_SHADER_SOURCE

        this.shaderProgram = this.initShaderProgram(this.gl, vsSource, fsSource)

        this.programInfo = {
            program: this.shaderProgram,
            attribLocations: {
                vertexPosition: this.gl.getAttribLocation(
                    this.shaderProgram,
                    'aVertexPosition'
                ),
            },
            uniformLocations: {
                projectionMatrix: this.gl.getUniformLocation(
                    this.shaderProgram,
                    'uProjectionMatrix'
                ),
                modelViewMatrix: this.gl.getUniformLocation(
                    this.shaderProgram,
                    'uModelViewMatrix'
                ),
            },
        }

        const buffers = this.initBuffers(this.gl)

        this.drawScene(this.gl, this.programInfo, buffers)

        this.constructionTime = this.getGlobalTotalSeconds()

        requestAnimationFrame(this.__animationFrame)
    }

    getWidth = () => {
        return this.getBoundingClientRect().width
    }
    getHeight = () => {
        return this.getBoundingClientRect().height
    }
    getRatio = () => {
        return this.getWidth() / this.getHeight()
    }

    __animationFrame = () => {
        let currentTime = this.getGlobalTotalSeconds() - this.constructionTime

        this.setFloatUniform('iTime', currentTime)
        this.setFVecUniform('iResolution', [this.getWidth(), this.getHeight()])
        this.refreshDrawing()

        requestAnimationFrame(this.__animationFrame)
    }

    /**
     * @param {String} fragmentShaderSource
     */
    setFragmentShader = (fragmentShaderSource) => {
        // Assign new source to the fragment shader
        this.gl.shaderSource(this.fragmentShader, fragmentShaderSource)
        this.gl.compileShader(this.fragmentShader)

        // Check for compilation errors
        const compileStatus = this.gl.getShaderParameter(
            this.fragmentShader,
            this.gl.COMPILE_STATUS
        )
        if (!compileStatus) {
            console.error(
                'Fragment shader compilation error:',
                this.gl.getShaderInfoLog(this.fragmentShader)
            )
            return
        }

        // Re-link the program
        this.gl.linkProgram(this.shaderProgram)

        // Check for linking errors
        const linkStatus = this.gl.getProgramParameter(
            this.shaderProgram,
            this.gl.LINK_STATUS
        )
        if (!linkStatus) {
            console.error(
                'Shader program linking error:',
                this.gl.getProgramInfoLog(this.shaderProgram)
            )
            return
        }

        // Use the updated shader program
        this.gl.useProgram(this.shaderProgram)

        this.programInfo = {
            program: this.shaderProgram,
            attribLocations: {
                vertexPosition: this.gl.getAttribLocation(
                    this.shaderProgram,
                    'aVertexPosition'
                ),
            },
            uniformLocations: {
                projectionMatrix: this.gl.getUniformLocation(
                    this.shaderProgram,
                    'uProjectionMatrix'
                ),
                modelViewMatrix: this.gl.getUniformLocation(
                    this.shaderProgram,
                    'uModelViewMatrix'
                ),
            },
        }

        const buffers = this.initBuffers(this.gl)

        this.drawScene(this.gl, this.programInfo, buffers)
    }

    /**
     * @param {String} uniformName
     * @returns {WebGLUniformLocation | null}
     */
    getUniformLocation = (uniformName) => {
        let location = this.programInfo.uniformLocations[uniformName]
        if (location == undefined || location == null)
            location = this.gl.getUniformLocation(
                this.shaderProgram,
                uniformName
            )
        return location
    }

    /**
     * @param {String} name
     * @param {Float} value
     */
    setFloatUniform = (name, value) => {
        this.gl.useProgram(this.shaderProgram)
        let location = this.getUniformLocation(name)
        this.gl.uniform1f(location, value)
    }

    __uniform_nfv = (n) => {
        if (n == 2) return this.gl.uniform2fv.bind(this.gl)
        if (n == 3) return this.gl.uniform3fv.bind(this.gl)
        if (n == 4) return this.gl.uniform4fv.bind(this.gl)
    }

    /**
     * @param {String} name
     * @param {Array<number>} value
     */
    setFVecUniform = (name, value) => {
        this.gl.useProgram(this.shaderProgram)
        let location = this.getUniformLocation(name)
        this.__uniform_nfv(value.length)(location, new Float32Array(value))
    }

    /**
     * @param {String} name
     * @param {String} hexColor
     */
    setColorRGBAUniform = (name, hexColor) => {
        let rgba = this.hexToRGBA(hexColor)
        this.setFVecUniform(name, [
            rgba[0] / 255,
            rgba[1] / 255,
            rgba[2] / 255,
            rgba[3] / 255,
        ])
    }

    loadShader(gl, type, source) {
        const shader = gl.createShader(type)

        gl.shaderSource(shader, source)

        gl.compileShader(shader)

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            alert(
                `An error occurred compiling the shaders: ${gl.getShaderInfoLog(
                    shader
                )}`
            )
            gl.deleteShader(shader)
            return null
        }

        return shader
    }

    drawScene = (gl, programInfo, buffers) => {
        gl.clearColor(0.0, 0.0, 0.0, 1.0)
        gl.clearDepth(1.0)
        gl.enable(gl.DEPTH_TEST)
        gl.depthFunc(gl.LEQUAL)

        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT)

        const fieldOfView = (45 * Math.PI) / 180
        const aspect = gl.canvas.clientWidth / gl.canvas.clientHeight
        const zNear = 0.1
        const zFar = 100.0

        let projectionMatrix2 = Matrix.perspective(
            aspect,
            fieldOfView,
            zFar,
            zNear
        )
        let modelViewMatrix2 = new Matrix(4, 4)

        modelViewMatrix2 = modelViewMatrix2.scale([aspect, 1, 1])
        modelViewMatrix2 = modelViewMatrix2.translate([
            0.0,
            0.0,
            -1 / Math.tan(fieldOfView * 0.5),
        ])

        this.setPositionAttribute(gl, buffers, programInfo)

        gl.useProgram(programInfo.program)

        gl.uniformMatrix4fv(
            programInfo.uniformLocations.projectionMatrix,
            false,
            projectionMatrix2.data
        )

        gl.uniformMatrix4fv(
            programInfo.uniformLocations.modelViewMatrix,
            false,
            modelViewMatrix2.data
        )

        this.refreshDrawing()
    }

    refreshDrawing = () => {
        const offset = 0
        const vertexCount = 4
        this.gl.drawArrays(this.gl.TRIANGLE_STRIP, offset, vertexCount)
    }

    setPositionAttribute(gl, buffers, programInfo) {
        const numComponents = 2
        const type = gl.FLOAT
        const normalize = false
        const stride = 0
        const offset = 0
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.position)
        gl.vertexAttribPointer(
            programInfo.attribLocations.vertexPosition,
            numComponents,
            type,
            normalize,
            stride,
            offset
        )
        gl.enableVertexAttribArray(programInfo.attribLocations.vertexPosition)
    }

    initBuffers(gl) {
        const positionBuffer = this.initPositionBuffer(gl)
        return { position: positionBuffer }
    }

    initPositionBuffer(gl) {
        const positionBuffer = gl.createBuffer()
        gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer)
        const positions = [1.0, 1.0, -1.0, 1.0, 1.0, -1.0, -1.0, -1.0]
        gl.bufferData(
            gl.ARRAY_BUFFER,
            new Float32Array(positions),
            gl.STATIC_DRAW
        )
        return positionBuffer
    }

    clear = () => {
        this.gl.clearColor(0.0, 0.0, 0.0, 1.0)
        this.gl.clear(this.gl.COLOR_BUFFER_BIT)
    }

    validateGL = () => {
        if (this.gl == null) {
            alert(
                'Unable to initialize WebGL, Your browser or machine may not support it.'
            )
        }
    }

    /**
     * @param {WebGL2RenderingContext} gl
     * @param {String} vsSource
     * @param {String} fsSource
     * @returns {WebGLProgram}
     */
    initShaderProgram(gl, vsSource, fsSource) {
        this.vertexShader = this.loadShader(gl, gl.VERTEX_SHADER, vsSource)
        this.fragmentShader = this.loadShader(gl, gl.FRAGMENT_SHADER, fsSource)

        const shaderProgram = gl.createProgram()
        gl.attachShader(shaderProgram, this.vertexShader)
        gl.attachShader(shaderProgram, this.fragmentShader)
        gl.linkProgram(shaderProgram)

        if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
            alert(
                `Unable to initialize the shader program: ${gl.getProgramInfoLog(
                    shaderProgram
                )}`
            )
            return null
        }

        return shaderProgram
    }

    hexToRGBA(hexCode) {
        hexCode = hexCode.replace('#', '')

        let r = parseInt(`0x${hexCode.substring(0, 2)}`)
        let g = parseInt(`0x${hexCode.substring(2, 4)}`)
        let b = parseInt(`0x${hexCode.substring(4, 6)}`)
        let a = parseInt(`0x${hexCode.substring(6, 8)}`)

        return [r, g, b, a]
    }

    getGlobalTotalSeconds() {
        return Date.now() / 1000.0
    }

    render() {
        return html
    }
}

WebsiteBannerShader.register()
