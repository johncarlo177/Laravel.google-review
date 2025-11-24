@php
$open = '<?xml version="1.0" encoding="UTF-8"?>';
@endphp
{!! $open !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  @foreach ($urls as $url)
  <url>
      <loc>{{ $url['url'] }}</loc>
      <lastmod>{{ $url['date'] }}</lastmod>
    </url>
  @endforeach
</urlset>