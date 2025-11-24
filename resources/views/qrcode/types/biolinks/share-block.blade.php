<div class="block share-block" id="{{ $model->getId() }}">
    <a href="{{ $block->url() }}" {!! $block->linkTarget() !!}>
        {!! $block->icon() !!}
        {!! $block->html() !!}
    </a>
</div>


<script>
    (function() {

        function onClick(e) {
            e.preventDefault()
            e.stopImmediatePropagation()

            navigator.share({
                url: window.location.href
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const link = document.querySelector('#{{ $model->getId() }} a');

            link.addEventListener('click', onClick)
        })
    })()
</script>
