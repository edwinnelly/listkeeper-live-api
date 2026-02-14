@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Update Serial Changes</h5>
                        <p class="text-muted mb-0">
                            History for Serial
                            / <span style="color: rgb(42, 21, 87)">
                                {{ $productDetails->name ?? 'N/A' }}
                            </span>
                        </p>

                    </div>

                    <div class="btn-group">
                        <!-- Back -->
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <!-- Table -->
            <div class="row">
                <div class="col-sm-4">
                    <div class="card-table">
                        <div class="card-body">
                            <form action="{{ route('products.keys.update', Crypt::encrypt($serial->id)) }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="row g-3">

                                        <div class="col-lg-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control sleek-input" name="serial_number"
                                                    placeholder="Serial Number" value="{{ $serial->serial_number }}"
                                                    readonly>
                                                <label>Serial Number</label>
                                            </div>
                                        </div>

                                        <input type="hidden" name="product_id"
                                            value="{{ Crypt::encrypt($productDetails->id) }}">

                                        <div class="col-lg-6">
                                            <div class="form-floating">
                                                <select name="status" class="form-control sleek-input">
                                                    @foreach (['available', 'sold', 'reserved', 'returned', 'defective'] as $status)
                                                        <option value="{{ $status }}"
                                                            {{ $serial->status === $status ? 'selected' : '' }}>
                                                            {{ ucfirst($status) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label>Change Status</label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer">

                                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Record</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!-- /Table -->

        </div>
    </div>
@endsection
