<section class="blog-list">
    @foreach ($posts as $post)

    <article {!! $post->featured_image_src ? 'class="has-image"' : '' !!}>
        <a href="{{ $post->url }}">
            @if ($post->featured_image_src)
            <img src="{{ $post->featured_image_src }}" alt="Post image" />
            @endif

            <header>
                <h2>{{ $post->title }}</h2>
            </header>
            <p class="excerpt">
                {{ $post->excerpt }}
            </p>
        </a>
    </article>
    @endforeach
</section>