import { BaseLeadFormAnswer } from './base-answer'

export class RatingAnswer extends BaseLeadFormAnswer {
    slug() {
        return 'rating'
    }

    onDocumentClick(e) {
        if (!this.isChildOfBoundForm(e.target)) return

        if (e.target.closest('.rating-number')) {
            this.onRatingNumberClick(e)
        }
    }

    shouldDisableOKButton() {
        return !this.nextPage.querySelector('.checked')?.textContent?.length
    }

    shouldUpdateOKButtonDisabledState() {
        return true
    }

    async onRatingNumberClick(e) {
        const ratingNumber = e.target.closest('.rating-number')

        this.toggleRatingNumberChecked(ratingNumber)

        await this.flashAnimate(ratingNumber)

        this.requestScrollDown()

        this.updateRequiredState()
    }

    toggleRatingNumberChecked(elem) {
        const checked = elem.classList.contains('checked')

        const allRatingNumbers = Array.from(
            elem.parentNode.querySelectorAll('.rating-number')
        )

        allRatingNumbers.forEach((elem) => elem.classList.remove('checked'))

        if (!checked) {
            elem.classList.add('checked')
        }

        // if it was checked, then it will be unchecked while resetting all other elements
    }

    jsonGetValue(questionPage) {
        return questionPage.querySelector('.checked')?.textContent
    }
}

RatingAnswer.boot()
