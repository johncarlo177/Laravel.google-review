export class Matrix {
    /**
     *
     * @param {number} aspect
     * @param {number} fov
     * @param {number} far
     * @param {number} near
     * @returns
     */
    static perspective(aspect, fov, far, near) {
        return new Matrix(4, 4, [
            1.0 / (aspect * Math.tan(fov / 2.0)),
            0,
            0,
            0,
            0,
            1.0 / Math.tan(fov / 2.0),
            0,
            0,
            0,
            0,
            -(far + near) / (far - near),
            -1,
            0,
            0,
            -(2 * far * near) / (far - near),
            0,
        ])
    }

    /**
     *
     * @param {int} rows
     * @param {int} cols
     * @returns {float}
     */
    static identityData(rows, cols) {
        let data = new Float32Array(rows * cols)
        for (var i = 0; i < rows; i++)
            for (var j = 0; j < cols; j++) data[i * cols + j] = i == j ? 1 : 0
        return data
    }

    /**
     *
     * @param {int} rows
     * @param {int} cols
     * @param {Array<number>} data
     */
    constructor(rows, cols, data = null) {
        /** @type {int} */
        this.rows = rows
        /** @type {int} */
        this.cols = cols

        this.data = data == null ? Matrix.identityData(rows, cols) : data
    }

    /**
     *
     * @param {int} i
     * @param {int} j
     * @returns {number}
     */
    get = (i, j) => {
        return this.data[i * this.cols + j]
    }

    /**
     *
     * @param {int} i
     * @param {int} j
     * @param {number} value
     */
    set = (i, j, value) => {
        this.data[i * this.cols + j] = value
    }

    /**
     * @returns {Matrix}
     */
    clone = () => {
        return new Matrix(this.rows, this.cols, new Float32Array(this.data))
    }

    /**
     *
     * @param {Array<number>} scaleVector3
     * @returns {Matrix}
     */
    scale = (scaleVector3) => {
        let result = this.clone()
        for (var i = 0; i < 3; i++)
            for (var j = 0; j < this.cols; j++)
                result.set(i, j, result.get(i, j) * scaleVector3[i])
        return result
    }

    /**
     *
     * @param {Array<number>} translation
     * @returns {Matrix}
     */
    translate = (translation) => {
        let result = this.clone()
        for (var j = 0; j < 3; j++)
            result.set(3, j, this.get(3, j) + translation[j])
        return result
    }
}
