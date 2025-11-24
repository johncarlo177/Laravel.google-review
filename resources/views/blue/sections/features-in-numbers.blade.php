@php
$shouldRender = array_reduce(range(1, 4), function($result, $i) {
return $result && ContentManager::hasAnyBlocks("Feature $i: number");
}, true)
@endphp

@if ($shouldRender)
<section class="features-in-numbers">
    <div class="layout-box">
        <div class="wrapper">
            @foreach (range(1, 4) as $i)
            <div class="number-box">
                <span class="number">
                    {!! ContentManager::contentBlocks("Feature $i: number") !!}
                </span>
                <label>
                    {!! ContentManager::contentBlocks("Feature $i: label") !!}
                </label>
            </div>
            @endforeach

        </div>
    </div>
</section>
@endif