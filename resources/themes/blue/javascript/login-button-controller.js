class LoginButtonController {
    constructor() {
        window.addEventListener('auth:local-user-ready', this.onLocalUserReady)

        document
            .querySelector('.logout-button')
            ?.addEventListener('click', this.onLogoutClick)
    }

    onLocalUserReady = () => {
        const guestElement = document.querySelector('header [state="guest"]')

        if (!guestElement) return

        guestElement.classList.add('hidden')

        document
            .querySelector('header [state="logged-in"]')
            .classList.remove('hidden')
    }

    onLogoutClick = (e) => {
        e.preventDefault()
        e.stopPropagation()

        window.dispatchEvent(new CustomEvent('auth:request-logout'))
    }
}

new LoginButtonController()
