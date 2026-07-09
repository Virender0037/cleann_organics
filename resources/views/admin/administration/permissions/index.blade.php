<x-admin-layout title="Permissions">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Permissions</h4>
        <p class="text-muted mb-0">View module-wise permission structure</p>
    </div>

    <button class="btn btn-light-secondary">
        <i class="ph ph-download-simple me-1"></i>
        Export
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h5>Permission Matrix</h5>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Module</th>
                    <th class="text-center">View</th>
                    <th class="text-center">Create</th>
                    <th class="text-center">Edit</th>
                    <th class="text-center">Delete</th>
                    <th class="text-center">Export</th>
                </tr>
            </thead>

            <tbody>
                @foreach (['Dashboard', 'Catalog', 'Inventory', 'Sales', 'Customers', 'Shipping', 'CMS', 'Reports', 'Administration', 'Settings'] as $module)
                    <tr>
                        <td><strong>{{ $module }}</strong></td>

                        <td class="text-center">
                            <span class="badge bg-success">Allowed</span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success">Allowed</span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success">Allowed</span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-danger">Restricted</span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success">Allowed</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</main>
</x-admin-layout>