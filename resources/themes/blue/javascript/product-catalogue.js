import { StackedLayout } from './stacked-layout'

export class ProductCatalogueRenderer extends StackedLayout {
    slug() {
        return 'product-catalogue'
    }
}

new ProductCatalogueRenderer()
