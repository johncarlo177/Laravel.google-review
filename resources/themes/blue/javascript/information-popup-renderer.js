import { BaseRenderer } from './base-renderer'

export class InformationPopupRenderer extends BaseRenderer {
    shouldRun() {
        return this.$('.information-pupup-link')
    }

    onDocumentClick(e) {
        let target = e.composedPath()[0]

        target = target.closest('.information-pupup-link')

        if (!target) {
            return
        }

        window.InformationPopupModal.open({
            data: {
                title: target.dataset.title,
                content: target.dataset.content,
            },
        })
    }
}

InformationPopupRenderer.boot()
