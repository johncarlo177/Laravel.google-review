@foreach ($composer->categories() as $category)
    <div class="category-page" slug="{{ $category['id'] }}">

        <button class="close-button button" title="{{ t('Go to Main') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z">
                </path>
            </svg>
            {{ t('Back') }}
        </button>

        <div class="category-page-title">
            {{ $category['name'] }} <span class="category-count">({{ count($composer->items($category)) }})</span>
        </div>

        @isset($category['servingHours'])
            <div class="serving-hours">
                {{ $category['servingHours'] }}
            </div>
        @endisset

        @if (!empty(($subs = $composer->subCategories($category))))
            <div class="sub-categories">
                @if ($composer->categoryHasDirectChildItems($category))
                    <div class="sub-category" slug="{{ $category['id'] }}">
                        {{ $category['name'] }}
                    </div>
                @endif
                @foreach ($subs as $subCategory)
                    <div class="sub-category" slug="{{ $subCategory['id'] }}">
                        {{ $subCategory['name'] }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="menu-items">

            @foreach ($composer->items($category) as $item)
                @include('qrcode.types.restaurant-menu.menu-item')
            @endforeach
        </div>
    </div>
@endforeach