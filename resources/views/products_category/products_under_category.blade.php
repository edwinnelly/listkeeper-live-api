@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Products under: {{ $category->name }}</h5>
                        <p class="text-muted mb-0">Category: {{ $category->slug }}</p>
                    </div>
                    <a href="{{ route('products.category.index') }}" class="btn btn-outline-primary rounded-pill">← Back to
                        Categories</a>
                </div>
            </div>



            <div class="super-admin-list-head">
                <div class="content container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card-table">
                                <div class="card-body">
                                    @if ($products->isEmpty())
                                        <p class="text-center">No products found in this category.</p>
                                    @else
                                        <input type="text" id="searchInput" placeholder="Search for location names..."
                                            onkeyup="searchTable()" class="form-control mb-3">
                                        <div class="table-responsive">
                                            <table class="table table-center table-hover datatable table-bold"
                                                id="myTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>SN</th>
                                                        <th>Product Name</th>
                                                        <th>SKU</th>
                                                        <th>Cost Price</th>
                                                        <th>Selling Price</th>
                                                        <th>Qty</th>
                                                        <th>Low Stock</th>
                                                        {{-- <th>Description</th> --}}
                                                        <th class="no-sort">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($products as $index => $product)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $product->name }}</td>
                                                            <td>{{ $product->sku }}</td>
                                                            <td>{{ number_format($product->cost_price, 2) }}</td>
                                                            <td>{{ number_format($product->sale_price, 2) }}</td>
                                                            <td>{{ number_format($product->stock_quantity, 2) }}</td>
                                                            <td>{{ number_format($product->low_stock_threshold, 2) }}</td>
                                                            {{-- <td>{{ $product->description }}</td> --}}
                                                            <td class="d-flex align-items-center">
                                                                <div class="dropdown dropdown-action">
                                                                    <a href="#" class="btn-action-icon"
                                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="fas fa-ellipsis-v"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <ul>
                                                                            <li>
                                                                                <a href="#" class="dropdown-item">
                                                                                    <i class="far fa-eye me-2"></i>View
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a href="#" class="dropdown-item">
                                                                                    <i class="far fa-edit me-2"></i>Edit
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
