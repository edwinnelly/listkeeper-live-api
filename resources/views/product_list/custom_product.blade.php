<div class="table-responsive">
                    <!-- Search -->
                    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search for products..." onkeyup="searchTable()">

                     <table class="table table-center table-hover datatable" id="myTable">
                        <thead class="thead-light">
                            <tr>
                                <th>SN</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Cost Price</th>
                                <th>Sale Price</th>
                                <th>Stock Qty</th>
                                <th>Low Stock Threshold</th>
                                <th>Discount %</th>
                                <th>Discount Start</th>
                                <th>Discount End</th>
                                <th>Production Date</th>
                                <th>Expiration Date</th>
                                <th>Weight</th>
                                <th>Dimensions (L×W×H)</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th class="no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fetchProducts as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data->name }}</td>
                                    <td>{{ $data->sku }}</td>
                                    <td>{{ $data->category->name ?? 'N/A' }}</td>
                                    <td>{{ $data->unit->short_name ?? 'N/A' }}</td>
                                    <td>{{ $data->business_lists->currency ?? 'N/A' }}{{ number_format($data->cost_price, 2) }}</td>
                                    <td>{{ $data->business_lists->currency ?? 'N/A' }}{{ number_format($data->sale_price, 2) }}</td>
                                    <td>{{ $data->stock_quantity }}</td>
                                    <td>{{ $data->low_stock_threshold }}</td>
                                    <td>{{ $data->discount_percentage }}%</td>
                                    <td>{{ $data->discount_start_date }}</td>
                                    <td>{{ $data->discount_end_date }}</td>
                                    <td>{{ $data->manufactured_at }}</td>
                                    <td>{{ $data->expires_at }}</td>
                                    <td>{{ $data->weight }}</td>
                                    <td>{{ $data->dimensions }}</td>
                                    <td>{{ $data->vendors->vendor_name ?? 'N/A' }}</td>
                                    <td>{{ $data->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="btn-action-icon" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="{{ route('product.serials.show', Crypt::encrypt($data->id)) }}"><i class="far fa-folder-closed me-2"></i> Products Keys</a>
                                                <a class="dropdown-item" href="{{ route('products.edit.show', Crypt::encrypt($data->id)) }}"><i class="far fa-edit me-2"></i> Edit</a>
                                                <a class="dropdown-item" href="#"><i class="far fa-trash-alt me-2"></i> Delete</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="19" class="text-center">No products found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
