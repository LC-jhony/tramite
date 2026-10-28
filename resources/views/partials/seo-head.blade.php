{{--
    Cabecera SEO del sitio público.

    Consume los grupos `seo` y `general` de la tabla `settings` con el helper
    global `setting()` (§6 de la guía), así que no depende de Filament para
    nada. Se incluye una sola vez dentro del <head> de cada plantilla pública:

        <head>
            @include('partials.seo-head')
        </head>

    Aporta: <title>, meta description/keywords/robots, canonical, favicon,
    Open Graph + Twitter Card y Google Analytics.
--}}
@php
    /** Archivo subido desde settings: acepta string o array (estado de FileUpload). */
    $settingAsset = function (mixed $path): ?string {
        $path = is_array($path) ? \Illuminate\Support\Arr::first($path) : $path;

        return is_string($path) && filled($path) ? asset('storage/'.$path) : null;
    };

    $siteName = (string) setting('general.site_name', config('app.name'));
    $siteUrl = (string) setting('general.site_url', '');
    $title = (string) setting('seo.meta_title', $siteName);
    $description = (string) setting('seo.meta_description', (string) setting('general.description', ''));
    $keywords = (string) setting('seo.meta_keywords', '');
    $canonical = (string) setting('seo.canonical_url', $siteUrl !== '' ? $siteUrl : url()->current());
    $indexable = (bool) setting('seo.indexable', true);
    $ogImage = $settingAsset(setting('seo.og_image'));
    $favicon = $settingAsset(setting('general.favicon'));
    $analyticsId = (string) setting('seo.google_analytics_id', '');
@endphp

<title>{{ filled($title) ? $title : config('app.name') }}</title>

@if (filled($description))
    <meta name="description" content="{{ $description }}">
@endif

@if (filled($keywords))
    <meta name="keywords" content="{{ $keywords }}">
@endif

{{-- Sin indexación cuando la pestaña SEO la tiene desactivada. --}}
<meta name="robots" content="{{ $indexable ? 'index, follow' : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ $canonical }}">

@if (filled($favicon))
    <link rel="icon" href="{{ $favicon }}">
@endif

{{-- Open Graph / Twitter: lo que se pinta al compartir en redes (§7.6). --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:url" content="{{ $canonical }}">

@if (filled($description))
    <meta property="og:description" content="{{ $description }}">
@endif

@if (filled($ogImage))
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ filled($ogImage) ? 'summary_large_image' : 'summary' }}">

{{-- Google Analytics (medición de la pestaña SEO) --}}
@if (filled($analyticsId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', @js($analyticsId));
    </script>
@endif
