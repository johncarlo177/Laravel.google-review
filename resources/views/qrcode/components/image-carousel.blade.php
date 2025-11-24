<div class="image-carousel" options="{{ $model->getOptions() }}" style="--navigation-color: {{ $model->navigation_color }}">
    <div class="blaze-slider">
        <div class="blaze-container">
            <div class="blaze-track-container">
                <div class="blaze-track">
                    @foreach ($model->getItems() as $item)
                        <div>
                            @if (@$item['url'])
                                <a href="{{ $item['url'] }}" target=_blank>
                            @endif
                            
                            <img src="{{ file_url($item['image']) }}">

                            @if (@$item['url'])
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($model->hasNavigation())
            <div class="pagination-container">
                <button class="blaze-prev" aria-label="Go to previous slide">
                    @include('blue.components.icons.chevron-left')
                </button>
                <div class="blaze-pagination"></div>
                <button class="blaze-next" aria-label="Go to next slide">
                    @include('blue.components.icons.chevron-right')
                </button>
            </div>
        @endif
        
    </div>
</div>

