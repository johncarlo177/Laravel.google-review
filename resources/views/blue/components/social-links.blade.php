@foreach ($composer->socialLinks() as $link)
    <a href="{{ $link['url'] }}" target="_blank">
        @include('blue.components.icons.social.' . $link['socialId'])


    </a>
@endforeach
