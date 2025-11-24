<div class="sidebar subscribe"
    style="background-image: url({{ override_asset('/assets/images/qrcode-showcase-bg-1.png') }})">
    <h3>
        {!! ContentManager::contentBlocks('Sidebar: title') !!}
    </h3>

    {!! ContentManager::contentBlocks('Sidebar: content', noParagraph: false) !!}
</div>