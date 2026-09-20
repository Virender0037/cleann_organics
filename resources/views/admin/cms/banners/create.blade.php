@php $label = \App\Models\HomeBanner::SECTION_LABELS[$section]; @endphp
<x-admin-layout :title="'Add — '.$label">
    <main class="pc-container-edit">
        <x-admin.page-header :title="'Add — '.$label" subtitle="Appears on the homepage once it is Active" />
        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => $label, 'url' => route('admin.cms.banners.index', ['section' => $section])], ['label' => 'Add']]" />
        @include('admin.partials.alerts')

        <form action="{{ route('admin.cms.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.cms.banners._form')
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.cms.banners.index', ['section' => $section]) }}" class="btn btn-light">Cancel</a>
        </form>
    </main>
</x-admin-layout>
