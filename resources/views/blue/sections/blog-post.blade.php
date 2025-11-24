<section class="post">
    <article>
        @if ($post->featured_image_src)
        <img src="{{ $post->featured_image_src }}" alt="Post image" />
        @endif

        <header>
            <h1>{{ $post->title }}</h1>
        </header>

        <div class="content">
            {!! $post->html !!}
        </div>
    </article>
</section>