import path from 'path'
import { glob } from 'glob'
import fs from 'fs'

import { exec } from 'node:child_process'

function command(string) {
    return new Promise((resolve, reject) => {
        exec(string, (err, stdout, stderr) => {
            if (err) {
                console.log(err)
            }

            if (stderr && stderr.length > 0) {
                return reject(err)
            }

            resolve(stdout)
        })
    })
}

async function getBuildHash(ext = 'js') {
    const pattern = path.resolve('dist/assets/*.' + ext)

    const files = await glob(pattern)

    const name = path.basename(files[0])

    const match = name.match(new RegExp('index-(.*)\\.' + ext))

    return match[1]
}

function getFileContents(file) {
    const buffer = fs.readFileSync(file)

    const fileContent = buffer.toString()

    return fileContent
}

function replaceInFile(path, search, replace) {
    let content = getFileContents(path)

    content = content.replace(search, replace)

    fs.writeFileSync(path, content)
}

async function renameBundle(ext, hash) {
    const src = path.resolve(`dist/assets/index-${hash}.${ext}`)

    const dist = path.resolve(`dist/assets/blue.${hash}.${ext}`)

    return await command(`mv ${src} ${dist}`)
}

async function syncBundle(ext) {
    const hash = await getBuildHash(ext)

    await renameBundle(ext, hash)

    replaceInFile(
        path.resolve('dist/blue-assets.blade.php'),
        new RegExp(`blue.${ext}`),
        `blue.${hash}.${ext}`
    )
}

async function copyBladeAdaptersToDistDirectory() {
    await command(`cp blade-adapters/blue-assets.blade.php dist/`)
}

async function entrypoint() {
    await copyBladeAdaptersToDistDirectory()

    await syncBundle('js')
    await syncBundle('css')
}

entrypoint()
