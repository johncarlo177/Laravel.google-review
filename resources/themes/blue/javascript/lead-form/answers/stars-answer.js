import { BaseLeadFormAnswer } from './base-answer'

export class StarsAnswer extends BaseLeadFormAnswer {
    slug() {
        return 'stars'
    }

    onDocumentClick(e) {
        if (!this.isChildOfBoundForm(e.target)) return

        if (e.target.closest('.star')) {
            this.onStarClick(e)
        }
    }

    async onStarClick(e) {
        const number = this.getStarNumber(e.target.closest('.star'))

        this.resetAllStars(e.target.closest('.stars-container'))

        this.enableStarsBeforeOrEqual(
            number,
            e.target.closest('.stars-container')
        )

        await this.flashAnimate(e.target.closest('.star'))

        this.doNextAction(e)
    }

    doNextAction(e) {
        const question = this.decodeQuestionObject(e)

        const number = this.getStarNumber(e.target.closest('.star'))

        if (!this.shouldGoNextQuestion(question, number)) {
            return
        }

        this.requestScrollDown()
    }

    shouldGoNextQuestion(question, number) {
        return document.dispatchEvent(
            new CustomEvent('stars-answer-should-go-next', {
                cancelable: true,
                detail: {
                    question,
                    number,
                },
            })
        )
    }

    decodeQuestionObject(e) {
        const elem = e.target.closest('.stars')

        let question = null

        try {
            question = JSON.parse(window.atob(elem.dataset.question))
        } catch {
            //
        }

        return question
    }

    resetAllStars(container) {
        const elems = Array.from(container.querySelectorAll('.star'))

        elems.forEach((elem) => this.setStarEnabled(elem, false))
    }

    setStarEnabled(elem, enabled) {
        if (enabled) {
            elem.classList.add('enabled')
        } else {
            elem.classList.remove('enabled')
        }
    }

    enableStarsBeforeOrEqual(number, container) {
        for (let i = 0; i <= number; i++) {
            const elem = container.querySelector(`.star:nth-child(${i + 1})`)

            this.setStarEnabled(elem, true)
        }
    }

    getStarNumber(elem) {
        const i = Array.from(elem.parentNode.children).indexOf(elem)

        return i === -1 ? false : i
    }

    shouldDisableOKButton() {
        return !this.jsonGetValue(this.nextPage)
    }

    shouldUpdateOKButtonDisabledState() {
        return true
    }

    jsonGetValue(questionPage) {
        return questionPage.querySelectorAll('.star.enabled').length
    }
}

StarsAnswer.boot()
