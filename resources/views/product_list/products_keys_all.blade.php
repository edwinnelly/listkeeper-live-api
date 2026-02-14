@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Product Serial Numbers </h5>
                        <p class="text-muted mb-0">Manage Serial Numbers<span
                                style="color: rgb(42, 21, 87)"></span></p>
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
                                            <li><a href="{{ route('sublocation_products.items') }}">Locations Products</a></li>
                                            {{-- <li><a href="" class="active">Serial Numbers</a></li> --}}
                                            <li><a href="{{ route('products.category.index') }}">Categories</a></li>
                                            <li><a href="{{ route('products.unit.index') }}">Units</a></li>
                                            <li><a href="{{ route('products.all.keys') }}" class="active">Serial Trackers</a></li>
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
                                                    {{-- <th>Product</th> --}}
                                                    <th>Serial Number</th>
                                                    <th>Status</th>
                                                    <th>Updated By</th>
                                                    <th>Created Date</th>

                                                    <th class="no-sort">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($serials as $index => $serial)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>

                                                        <td>{{ $serial->serial_number }}</td>

                                                        <td>
                                                            <span
                                                                class="badge bg-{{ $serial->status == 'available' ? 'success' : ($serial->status == 'sold' ? 'primary' : 'secondary') }}">
                                                                {{ ucfirst($serial->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $serial->username }}</td>

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

@endsection
