import { iPhoneSafari } from './helpers'

export class iPhoneDetector {
    static boot() {
        if (iPhoneSafari()) {
            document.documentElement.classList.add('iphone')
        }
    }
}

iPhoneDetector.boot()
