@props(['what' => 'This screen'])

<div class="alert alert-warning d-flex align-items-start gap-2" role="alert" data-not-implemented>
    <i class="ph ph-warning-circle fs-4"></i>
    <div>
        <strong>Not implemented yet.</strong>
        {{ $what }} is a static preview — the rows shown are sample data, and nothing on this page is saved.
        Admin access is controlled by each user's <code>role</code> column (only <code>superadmin</code> can sign in to this panel).
    </div>
</div>
