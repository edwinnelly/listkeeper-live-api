@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Product Units</h5>
                        <p class="text-muted mb-0">Manage Product Units</p>
                    </div>

                    <div class="btn-group">
                        <!-- Refresh Button -->
                        {{-- <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button> --}}

                        <!-- Export Button -->
                        {{-- <button class="btn btn-sm btn-outline-primary ms-2" onclick="downloadCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button> --}}


                        @auth
                        <!-- Add Unit Button -->
                            {{-- @if (auth()->user()->hasPermission('unit_create')) --}}
                                {{-- <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                    data-bs-target="#add_product_unit">
                                    <i class="fas fa-plus-circle me-1"></i> Add Unit
                                </button> --}}
                            {{-- @endif --}}
                        @endauth
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="super-admin-list-head">
                <div class="content container-fluid">
                    <div class="card invoices-tabs-card">
                        <div class="invoices-main-tabs">
                            <div class="row align-items-center">
                                <div class="col-lg-12">
                                    <div class="invoices-tabs">
                                        <ul>
                                            <li><a href="{{ route('products.index') }}">Product</a></li>
                                            <li><a href="{{ route('sublocation_products.items') }}">Locations Products</a></li>
                                            <li><a href="{{ route('products.category.index') }}">Category</a></li>
                                            <li><a href="{{ route('products.unit.index') }}" class="active">Units</a></li>
                                            <li><a href="{{ route('products.all.keys') }}">Serial Trackers</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card-table">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <input type="text" id="searchInput" placeholder="Search for unit names..."
                                            onkeyup="searchTable()" class="form-control mb-3">

                                        <table class="table table-center table-hover datatable" id="myTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>SN</th>
                                                    <th>Unit Name</th>
                                                    <th>Slug</th>
                                                    <th>Descriptions</th>
                                                    <th class="no-sort">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($fetch_all as $index => $data)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $data->name }}</td>
                                                        <td>{{ $data->short_name }}</td>
                                                        <td>{{ $data->description }}</td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <a href="#" class="btn-action-icon" data-bs-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('products.unit.index', Crypt::encrypt($data->id)) }}">
                                                                        <i class="far fa-edit me-2"></i>Edit
                                                                    </a>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('products.unit.index', Crypt::encrypt($data->id)) }}">
                                                                        <i class="far fa-trash-alt me-2"></i>Delete
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">No units found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Add Product Unit Modal -->
    <div class="modal custom-modal custom-lg-modal fade p-20" id="add_product_unit" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div class="form-header modal-header-title text-start mb-0">
                        <h4 class="mb-0">Add Product Unit</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form action="" method="POST" class="needs-validation" novalidate
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <div class="form-section">
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control sleek-input" id="unitName"
                                                        name="name" placeholder="Unit Name" required>
                                                    <label for="unitName">Unit Name</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control sleek-input" name="slug"
                                                        placeholder="Unit Code" required>
                                                    <label>Unit Code</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-floating">
                                                    <textarea class="form-control sleek-input" name="description" placeholder="Description" style="height: 100px"></textarea>
                                                    <label>Description</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- /.form-section -->
                                </div>
                            </div>
                        </div>
                    </div>
                    @auth
                        <div class="modal-footer">
                            <button type="button" data-bs-dismiss="modal"
                                class="btn btn-outline-secondary rounded-pill">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Add Unit</button>
                        </div>
                    @endauth
                </form>
            </div>
        </div>
    </div>
@endsection
