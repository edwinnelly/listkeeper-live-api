@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Product Serial Numbers </h5>
                        <p class="text-muted mb-0">Manage Serial Numbers / <span
                                style="color: rgb(42, 21, 87)">{{ $productDetails->name }}</span></p>
                    </div>

                    <div class="btn-group">
                        <!-- Refresh -->
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()"
                            data-bs-toggle="tooltip" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>

                        <!-- Export -->
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="downloadCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>

                        <!-- Add Serial Number -->
                        @auth
                            {{-- @if (auth()->user()->hasPermission('serial_create')) --}}
                            <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#add_product_serial">
                                <i class="fas fa-plus-circle me-1"></i> Add Serial
                            </button>
                            {{-- @endif --}}
                        @endauth
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="super-admin-list-head">
                <div class="content container-fluid">

                    <!-- Tabs -->
                    <div class="card invoices-tabs-card">
                        <div class="invoices-main-tabs">
                            <div class="row align-items-center">
                                <div class="col-lg-12">
                                    <div class="invoices-tabs">
                                        <ul>
                                            <li><a href="{{ route('products.index') }}">Products</a></li>
                                            {{-- <li><a href="" class="active">Serial Numbers</a></li> --}}
                                            <li><a href="{{ route('products.category.index') }}">Categories</a></li>
                                            <li><a href="{{ route('products.unit.index') }}">Units</a></li>
                                            <li><a href="{{ route('products.all.keys') }}">Serial Trackers</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Tabs -->

                    <!-- Table -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card-table">
                                <div class="card-body">
                                    <div class="table-responsive">

                                        <input type="text" id="searchInput" placeholder="Search serial numbers..."
                                            onkeyup="searchTable()" class="form-control mb-3">

                                        <table class="table table-center table-hover datatable" id="myTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Sn</th>
                                                    <th>Product</th>
                                                    <th>Serial Number</th>
                                                    <th>Status</th>
                                                    <th>Sold Date</th>
                                                    <th>Created Date</th>

                                                    <th class="no-sort">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($serials as $index => $serial)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $productDetails->name }}</td>
                                                        <td>{{ $serial->serial_number }}</td>
                                                        <td>
                                                            <span
                                                                class="badge bg-{{ $serial->status == 'available' ? 'success' : ($serial->status == 'sold' ? 'primary' : 'secondary') }}">
                                                                {{ ucfirst($serial->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $serial->sale_date ?? '-' }}</td>
                                                        <td>{{ $serial->created_at ?? '-' }}</td>

                                                        <td>
                                                            <div class="dropdown">
                                                                <a href="#" class="btn-action-icon"
                                                                    data-bs-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-right">

                                                                    <a class="dropdown-item"
                                                                        href="{{ route('product.serials.history', ['id' => Crypt::encrypt($serial->id), 'pid' => Crypt::encrypt($serial->product_id)]) }}">
                                                                        <i class="far fa-edit me-2"></i> Update Serial
                                                                    </a>


                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">No serial numbers found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Table -->

                </div>
            </div>
        </div>
    </div>

    <!-- Add Serial Modal -->
    <div class="modal custom-modal custom-lg-modal fade p-20" id="add_product_serial" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div class="form-header modal-header-title text-start mb-0">
                        <h4 class="mb-0">Add Product Serial Number</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('product.serials.store', Crypt::encrypt($productDetails->id)) }}" method="POST"
                    class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-lg-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control sleek-input" name="serial_number"
                                        placeholder="Serial Number" required>
                                    <label>Serial Number</label>
                                </div>
                            </div>

                            <input type="hidden" name="product_id" value="{{ Crypt::encrypt($productDetails->id) }}">

                            <div class="col-lg-6">
                                <div class="form-floating">
                                    <select name="status" class="form-control sleek-input">
                                        <option value="available">Available</option>
                                        <option value="sold">Sold</option>
                                        <option value="reserved">Reserved</option>
                                        <option value="returned">Returned</option>
                                        <option value="defective">Defective</option>
                                    </select>
                                    <label>Status</label>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-bs-dismiss="modal"
                            class="btn btn-outline-secondary rounded-pill">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Add Serial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Add Serial Modal -->
@endsection
