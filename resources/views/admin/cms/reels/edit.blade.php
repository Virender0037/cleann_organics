<x-admin-layout title="Edit Reel">
    <main class="pc-container-edit">
        <x-admin.page-header title="Edit Reel" subtitle="Update this shoppable video" />
        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Reels', 'url' => route('admin.cms.reels.index')], ['label' => 'Edit']]" />
        @include('admin.partials.alerts')

        <form action="{{ route('admin.cms.reels.update', $reel) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.cms.reels._form')
            <button type="submit" class="btn btn-primary">Update Reel</button>
            <a href="{{ route('admin.cms.reels.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </main>
</x-admin-layout>
