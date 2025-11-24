@if ($composer->shouldRender())
<div class="layout-gap"></div>
<section class="blog-short-list">
    <div class="layout-box">
        <div class="text-wrapper">
            <h2 class="section-title">
                {!! ContentManager::contentBlocks('Blog: page title') !!}
            </h2>
            <p class="explainer">
                {!! ContentManager::contentBlocks('Blog: below title') !!}
            </p>
        </div>

        <div class="posts">

            @foreach ($posts as $post)
            <a class="post" href="{{ $post->url }}">
                @if (!empty($post->featured_image_src))
                <img src="{{ $post->featured_image_src }}" alt="Post image" />
                @endif

                <h2 class="post-title">{{ $post->title }}</h2>
                <div class="post-excerpt">
                    {{ $post->excerpt }}
                </div>
            </a>
            @endforeach

        </div>

        <div class="explore-more-container">
            <a href="{{ url('/blog') }}" class="button primary">{{ t('Explore More') }}</a>
        </div>
    </div>
</section>
@endif