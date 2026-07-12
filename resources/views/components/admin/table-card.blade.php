@props(['title'])

<div class="card">
    <div class="card-header">
        <h5>{{ $title }}</h5>
    </div>

    <div class="card-body">
        @isset($toolbar)
            {{ $toolbar }}
        @endisset

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                @isset($head)
                    <thead>
                        <tr>{{ $head }}</tr>
                    </thead>
                @endisset

                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @isset($pagination)
            <div class="d-flex justify-content-end">
                {{ $pagination }}
            </div>
        @endisset
    </div>
</div>
