import { StackedLayout } from './stacked-layout'

export class RestaurantMenuRenderer extends StackedLayout {
    slug() {
        return 'restaurant-menu'
    }
}

new RestaurantMenuRenderer()
