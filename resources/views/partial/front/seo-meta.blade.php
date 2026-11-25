{{-- Rendered when a controller passes $seoMeta (see SeoMetaService). --}}
<title>{{ $seoMeta['title'] }}</title>
<meta name="description" content="{{ $seoMeta['description'] }}">
@if (!empty($seoMeta['keywords']))
<meta name="keywords" content="{{ $seoMeta['keywords'] }}">
@endif
<meta name="author" content="{{ $seoMeta['author'] }}">
<meta name="robots" content="{{ $seoMeta['robots'] }}">
<link rel="canonical" href="{{ $seoMeta['canonical'] }}">
<meta property="og:type" content="{{ $seoMeta['type'] }}">
<meta property="og:site_name" content="{{ $seoMeta['site_name'] }}">
<meta property="og:locale" content="{{ $seoMeta['locale'] }}">
<meta property="og:title" content="{{ $seoMeta['title'] }}">
<meta property="og:description" content="{{ $seoMeta['description'] }}">
<meta property="og:url" content="{{ $seoMeta['canonical'] }}">
<meta property="og:image" content="{{ $seoMeta['image'] }}">
<meta name="twitter:card" content="summary_large_image">
@if (!empty($seoMeta['twitter_site']))
<meta name="twitter:site" content="{{ $seoMeta['twitter_site'] }}">
@endif
<meta name="twitter:title" content="{{ $seoMeta['title'] }}">
<meta name="twitter:description" content="{{ $seoMeta['description'] }}">
<meta name="twitter:image" content="{{ $seoMeta['image'] }}">
@foreach ($seoMeta['json_ld'] as $ld)
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endforeach
