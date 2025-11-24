<div class="block {{ $block::slug() }}-block" id="{{ $model->getId() }}">
    @if ($model->notEmpty('title'))
        <div class=title>
            {{ $model->field('title') }}
        </div>
    @endif

    <div class=grid>
        @foreach ($model->field('items') as $item)
            <a id="{{ $item['id'] }}" href="{{ @$item['url'] ?? 'javascript: return 0;' }}">
            </a>
        @endforeach
    </div>
</div>
