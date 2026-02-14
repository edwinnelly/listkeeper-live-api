@extends('inc.base')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h5 class="mb-0 fw-semibold">Add New Product</h5>
                    <p class="text-muted mb-0">Create a new product listing</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary"
                       data-bs-toggle="tooltip" title="Back to Products">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('products.store') }}" method="POST"
                              class="needs-validation" novalidate enctype="multipart/form-data">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Basic Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Basic Information</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control sleek-input" id="productName"
                                               name="name" placeholder="Product Name" value="{{ old('name') }}" required>
                                        <label for="productName">Product Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control sleek-input" id="sku"
                                               name="sku" placeholder="SKU" value="{{ old('sku') }}">
                                        <label for="sku">SKU</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control sleek-input" id="description" name="description"
                                                  placeholder="Description" style="height: 100px">{{ old('description') }}</textarea>
                                        <label for="description">Description</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Category Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Category Information</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select class="form-select sleek-input" id="category" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach ($category as $pcategory)
                                                <option value="{{ $pcategory->id }}" {{ old('category_id') == $pcategory->id ? 'selected' : '' }}>
                                                    {{ $pcategory->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="category">Category</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Pricing Information</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" class="form-control sleek-input" id="costPrice"
                                               name="cost_price" placeholder="Cost Price" value="{{ old('cost_price') }}">
                                        <label for="costPrice">Cost Price</label>
                                    </div>
                                    @error('cost_price') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" class="form-control sleek-input" id="salePrice"
                                               name="sale_price" placeholder="Sale Price" value="{{ old('sale_price') }}">
                                        <label for="salePrice">Sale Price</label>
                                    </div>
                                    @error('sale_price') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Inventory Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Inventory Information</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select class="form-select sleek-input" id="unit" name="unit_id" required>
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $punits)
                                                <option value="{{ $punits->id }}" {{ old('unit_id') == $punits->id ? 'selected' : '' }}>
                                                    {{ $punits->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="unit">Unit</label>
                                    </div>
                                    @error('unit_id') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" class="form-control sleek-input" id="stockQty"
                                               name="stock_quantity" placeholder="Stock Quantity" value="{{ old('stock_quantity') }}">
                                        <label for="stockQty">Stock Quantity</label>
                                    </div>
                                    @error('stock_quantity') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" class="form-control sleek-input" id="lowStockThreshold"
                                               name="low_stock_threshold" placeholder="Low Stock Threshold" value="{{ old('low_stock_threshold') }}">
                                        <label for="lowStockThreshold">Low Stock Threshold</label>
                                    </div>
                                    @error('low_stock_threshold') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Discount Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Discount Information</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" class="form-control sleek-input"
                                               id="discount_percentage" name="discount_percentage" placeholder="Discount %"
                                               value="{{ old('discount_percentage') }}">
                                        <label for="discount_percentage">Discount %</label>
                                    </div>
                                    @error('discount_percentage') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="date" class="form-control sleek-input" id="discountStart"
                                               name="discount_start_date" value="{{ old('discount_start_date') }}">
                                        <label for="discountStart">Discount Start Date</label>
                                    </div>
                                    @error('discount_start_date') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="date" class="form-control sleek-input" id="discountEnd"
                                               name="discount_end_date" value="{{ old('discount_end_date') }}">
                                        <label for="discountEnd">Discount End Date</label>
                                    </div>
                                    @error('discount_end_date') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Manufacturing Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Manufacturing Information</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="date" class="form-control sleek-input" id="manufactured_at"
                                               name="manufactured_at" value="{{ old('manufactured_at') }}">
                                        <label for="manufactured_at">Manufactured Date</label>
                                    </div>
                                    @error('manufactured_at') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="date" class="form-control sleek-input" id="expires_at"
                                               name="expires_at" value="{{ old('expires_at') }}">
                                        <label for="expires_at">Expiry Date</label>
                                    </div>
                                    @error('expires_at') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Physical Attributes -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Physical Attributes</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control sleek-input" id="weight"
                                               name="weight" placeholder="Weight" value="{{ old('weight') }}">
                                        <label for="weight">Weight</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-floating">
                                        <input type="text" class="form-control sleek-input" id="dimensions"
                                               name="dimensions" placeholder="Dimensions (L×W×H)" value="{{ old('dimensions') }}">
                                        <label for="dimensions">Dimensions (L×W×H)</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Supplier & Status -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Supplier & Status</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select sleek-input" id="supplier" name="supplier_id">
                                            <option value="">Select Supplier</option>
                                            @foreach ($vendors as $pvendors)
                                                <option value="{{ $pvendors->id }}" {{ old('supplier_id') == $pvendors->id ? 'selected' : '' }}>
                                                    {{ $pvendors->vendor_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="supplier">Supplier</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select sleek-input" id="status" name="status" required>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        </select>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Image -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3 fw-semibold text-primary">Product Image</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="productImage" class="form-label">Upload Product Image</label>
                                        <input class="form-control" type="file" id="productImage" name="image" accept="image/*">
                                    </div>
                                    @error('image') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row g-3 mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="reset" class="btn btn-outline-secondary rounded-pill px-4">Reset</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Product</button>
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
