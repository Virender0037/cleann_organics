@props([
    'metaTitle' => null,
    'metaDescription' => null,
    'canonicalUrl' => null,
    'ogImage' => null,
])
<x-layouts.header
    :meta-title="$metaTitle"
    :meta-description="$metaDescription"
    :canonical-url="$canonicalUrl"
    :og-image="$ogImage" />
    <main>
        {{ $slot }}
    </main>
<x-layouts.footer />
