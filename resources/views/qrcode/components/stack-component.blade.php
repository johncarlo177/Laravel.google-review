<style>
    {!! $component->renderStyles() !!}
</style>

<div class="stack-component">
    @foreach ($component->items() as $item)
        <div class="stack-item" id="{{ $item->getCssId() }}">
            <div class="stack-item-title">
                {!! $item->title !!}
            </div>

            <div class="stack-item-content">
                @foreach ($composer->blocks($item->id) as $block)
                    {!! ContentManager::customCode(sprintf('BioLinks %s: before', $block::slug())) !!}

                    {!! $block->render($composer) !!}

                    {!! ContentManager::customCode(sprintf('BioLinks %s: after', $block::slug())) !!}
                @endforeach
            </div>
        </div>
    @endforeach
</div>
