@props([
    'metaTitle' => null,
    'metaDescription' => null,
    'canonicalUrl' => null,
    'ogImage' => null,
    'noindex' => false,
])
<x-layouts.header
    :meta-title="$metaTitle"
    :meta-description="$metaDescription"
    :canonical-url="$canonicalUrl"
    :og-image="$ogImage"
    :noindex="$noindex" />
    <main>
        {{ $slot }}
    </main>
<x-layouts.footer />
