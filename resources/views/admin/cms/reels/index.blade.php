<x-admin-layout title="Reels">
    <main class="pc-container-edit">

        <x-admin.page-header title="Reels" subtitle="Shoppable videos shown on the homepage">
            <x-slot:actions>
                <a href="{{ route('admin.cms.reels.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i> Add Reel
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Reels']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Reel List">
            <x-slot:head>
                <th>#</th>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Product</th>
                <th>Source</th>
                <th>Sort</th>
                <th>Status</th>
                <th width="130">Action</th>
            </x-slot:head>

            @forelse ($reels as $reel)
                <tr>
                    <td>{{ $reel->id }}</td>
                    <td>
                        @if ($reel->thumbnail)
                            <img src="{{ Storage::url($reel->thumbnail) }}" alt="" class="rounded border" style="height:56px">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><strong>{{ $reel->title ?: '—' }}</strong></td>
                    <td>{{ $reel->product?->name ?? '(deleted)' }}@if ($reel->variant)<br><small class="text-muted">{{ $reel->variant->displayLabel() }}</small>@endif</td>
                    <td>
                        {{ $reel->video_path ? 'Uploaded video' : ($reel->video_url ? 'Video URL' : 'Instagram link') }}
                    </td>
                    <td>{{ $reel->sort_order }}</td>
                    <td><x-admin.status-badge :status="$reel->status" /></td>
                    <td>
                        <a href="{{ route('admin.cms.reels.edit', $reel) }}" class="btn btn-sm btn-warning" title="Edit"><i class="ph ph-pencil-simple"></i></a>
                        <form action="{{ route('admin.cms.reels.destroy', $reel) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this reel?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="ph ph-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-admin.empty-state>No reels yet. The homepage reels section stays hidden until you add one.</x-admin.empty-state></td></tr>
            @endforelse

            <x-slot:pagination>
                {{ $reels->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
