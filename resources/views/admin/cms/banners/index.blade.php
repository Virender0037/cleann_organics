@php
    $isHero = $section === \App\Models\HomeBanner::SECTION_HERO;
    $label = \App\Models\HomeBanner::SECTION_LABELS[$section];
    $threshold = app(\App\Services\Storefront\StorefrontSettings::class);
    $formattedThreshold = $threshold->formatMoney($threshold->freeShippingThreshold());
@endphp
<x-admin-layout :title="$label">
    <main class="pc-container-edit">

        <x-admin.page-header
            :title="$label"
            :subtitle="$isHero ? 'Slides of the main homepage slideshow (active slides, lowest sort order first)' : 'Trust / benefit cards shown in the strip under the hero slideshow (active items, lowest sort order first)'">
            <x-slot:actions>
                <a href="{{ route('admin.cms.banners.create', ['section' => $section]) }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i> {{ $isHero ? 'Add Slide' : 'Add Benefit' }}
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => $label]]" />

        @include('admin.partials.alerts')

        <x-admin.table-card :title="$label.' List'">
            <x-slot:head>
                <th>#</th>
                <th>Preview</th>
                <th>Title</th>
                <th>Links to</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Updated</th>
                <th width="170">Action</th>
            </x-slot:head>

            @forelse ($banners as $banner)
                <tr>
                    <td>{{ $banner->id }}</td>
                    <td>
                        @if ($banner->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($banner->image))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($banner->image) }}" alt="{{ $banner->alt_text }}" class="rounded border" style="height:48px;max-width:120px;object-fit:cover">
                        @elseif ($banner->icon)
                            <span class="text-success"><x-frontend.benefit-icon :name="$banner->icon" :size="32" /></span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $banner->title ?: '—' }}</strong>
                        @if ($banner->subtitle)
                            <div class="text-muted small">{{ $banner->renderedSubtitle($formattedThreshold) }}</div>
                        @endif
                    </td>
                    <td>
                        @switch($banner->link_type)
                            @case('product') Product: {{ $banner->product?->name ?? '(deleted)' }} @break
                            @case('category') Category: {{ $banner->category?->name ?? '(deleted)' }} @break
                            @case('tag') Collection: {{ $banner->tag?->name ?? '(deleted)' }} @break
                            @case('none') <span class="text-muted">No link</span> @break
                            @default {{ $banner->link_url }}
                        @endswitch
                        @if ($banner->opens_new_tab && $banner->link_type !== 'none') <span class="badge bg-light text-dark">new tab</span> @endif
                    </td>
                    <td>{{ $banner->sort_order }}</td>
                    <td><x-admin.status-badge :status="$banner->status" /></td>
                    <td class="text-nowrap">{{ $banner->updated_at?->format('d M Y') }}</td>
                    <td class="text-nowrap">
                        <form action="{{ route('admin.cms.banners.toggle', $banner) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-{{ $banner->status === 'active' ? 'secondary' : 'success' }}" title="{{ $banner->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                <i class="ph ph-{{ $banner->status === 'active' ? 'eye-slash' : 'eye' }}"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.cms.banners.edit', $banner) }}" class="btn btn-sm btn-warning" title="Edit"><i class="ph ph-pencil-simple"></i></a>
                        <form action="{{ route('admin.cms.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="ph ph-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-admin.empty-state>Nothing here yet — this section stays hidden on the homepage until you add and activate an item.</x-admin.empty-state></td></tr>
            @endforelse

            <x-slot:pagination>
                {{ $banners->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
