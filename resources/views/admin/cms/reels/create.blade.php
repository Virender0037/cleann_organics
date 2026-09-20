<x-admin-layout title="Add Reel">
    <main class="pc-container-edit">
        <x-admin.page-header title="Add Reel" subtitle="A homepage shoppable video linked to a product" />
        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Reels', 'url' => route('admin.cms.reels.index')], ['label' => 'Add']]" />
        @include('admin.partials.alerts')

        <form action="{{ route('admin.cms.reels.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.cms.reels._form')
            <button type="submit" class="btn btn-primary">Save Reel</button>
            <a href="{{ route('admin.cms.reels.index') }}" class="btn btn-light">Cancel</a>
        </form>
    </main>
</x-admin-layout>
