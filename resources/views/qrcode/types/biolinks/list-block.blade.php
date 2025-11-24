<div class="block list-block" id="{{ $model->getId() }}">
    <div class="white-card list-card">
        <div class="main-title">
            {{ $model->field('main_title') }}
        </div>

        @foreach ($model->field('items') as $item)
            <div class="item">
                @if ($icon = $block->icon($item))
                    <div class=icon-container>
                        {!! $icon !!}
                    </div>
                @endif

                <div class=text>
                    {!! @$item['text'] !!}
                </div>
            </div>
        @endforeach

    </div>

</div>
