@props([
    'metaTitle' => null,
    'metaDescription' => null,
    'canonicalUrl' => null,
])
<x-layouts.header
    :meta-title="$metaTitle"
    :meta-description="$metaDescription"
    :canonical-url="$canonicalUrl" />
    <main>
        {{ $slot }}
    </main>
<x-layouts.footer />
