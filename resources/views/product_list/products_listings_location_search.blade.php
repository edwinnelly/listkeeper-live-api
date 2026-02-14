@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h5 class="mb-0 fw-semibold">Add items from the main branch</h5>
                    <p class="text-muted mb-0">Manage Products Listings</p>
                </div>

                <div class="btn-group">
                    <!-- Refresh -->
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <!-- /Page Header -->

            <!-- Tabs -->
            <div class="card invoices-tabs-card mb-4">
                <div class="invoices-main-tabs">
                    <div class="row align-items-center">
                        <div class="col-lg-12">
                            <div class="invoices-tabs">
                                <ul>
                                    <li><a href="{{ route('products.index') }}">Products</a></li>
                                    <li><a href="{{ route('sublocation_products.items') }}" class="active">Locations
                                            Products</a></li>
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

            <!-- Products Table -->
            <div class="col-md-6">
                <div class="card-body">

                    <div class="mb-4 position-relative">
                        <label for="unit" class="form-label fw-semibold text-muted">
                            Choose a product from main branch
                        </label>

                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="unit" class="form-control border-0"
                                placeholder="Start typing... (e.g. Laptop, food)" autocomplete="off"
                                aria-autocomplete="list" aria-controls="unitDropdown" aria-expanded="false">
                            <input type="hidden" id="unit_id" name="unit_id">
                            <button id="viewBtn" type="button" class="btn btn-primary" disabled>Add Product</button>
                        </div>

                        <!-- Custom dropdown -->
                        <ul id="unitDropdown" class="list-group position-absolute w-100 shadow-sm rounded-3 mt-1 d-none"
                            role="listbox" style="z-index: 1000; max-height: 250px; overflow-y: auto;">
                        </ul>

                        <small id="unitHelp" class="form-text text-secondary">
                            Pick a product by typing at least 2 letters.
                        </small>
                    </div>

                    <!-- Autocomplete Script with Cache -->
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const unitInput = document.getElementById('unit');
                            const unitDropdown = document.getElementById('unitDropdown');
                            const hiddenField = document.getElementById('unit_id');
                            const viewBtn = document.getElementById('viewBtn');

                            let debounceTimer;
                            let activeIndex = -1;
                            const cache = {}; // 🔹 JS memory cache

                            unitInput.addEventListener('input', () => {
                                clearTimeout(debounceTimer);
                                debounceTimer = setTimeout(() => searchProducts(unitInput.value.trim()), 300);
                            });

                            async function searchProducts(query) {
                                hiddenField.value = "";
                                viewBtn.disabled = true;
                                activeIndex = -1;

                                if (query.length < 2) {
                                    unitDropdown.classList.add('d-none');
                                    unitInput.setAttribute("aria-expanded", "false");
                                    return;
                                }

                                // 🔹 Check cache first
                                if (cache[query]) {
                                    renderResults(cache[query], query);
                                    return;
                                }

                                try {
                                    const response = await fetch(`/foods/search?q=${encodeURIComponent(query)}`);
                                    const data = await response.json();

                                    // 🔹 Save to cache
                                    cache[query] = data;

                                    renderResults(data, query);
                                } catch (error) {
                                    console.error("Fetch error:", error);
                                    unitDropdown.innerHTML =
                                        `<li class="list-group-item text-danger">Error fetching results</li>`;
                                    unitDropdown.classList.remove('d-none');
                                }
                            }

                            function renderResults(data, query) {
                                unitDropdown.innerHTML = "";

                                if (data.length === 0) {
                                    unitDropdown.innerHTML =
                                        `<li class="list-group-item text-muted">No results found</li>`;
                                    unitDropdown.classList.remove('d-none');
                                    unitInput.setAttribute("aria-expanded", "true");
                                    return;
                                }

                                data.slice(0, 20).forEach((item) => {
                                    const li = document.createElement('li');
                                    li.className = "list-group-item list-group-item-action";
                                    li.setAttribute("role", "option");
                                    li.dataset.id = item.id;

                                    const regex = new RegExp(`(${query})`, "i");

                                    li.innerHTML = `
                                        <div>
                                            <strong>${item.name.replace(regex, "<mark>$1</mark>")}</strong><br>
                                            <small class="text-primary" style="font-size: 0.8rem;">${item.title || ""}</small><br>
                                            <small class="text-muted d-block text-truncate"
                                                   style="font-size: 0.75rem; max-width: 100%;"
                                                   title="${item.description || ""}">
                                                ${item.description || ""}
                                            </small>
                                        </div>
                                    `;

                                    li.addEventListener('click', () => selectProduct(item));
                                    unitDropdown.appendChild(li);
                                });

                                unitDropdown.classList.remove('d-none');
                                unitInput.setAttribute("aria-expanded", "true");
                            }

                            function selectProduct(item) {
                                unitInput.value = item.name;
                                hiddenField.value = item.id;
                                unitDropdown.classList.add('d-none');
                                unitInput.setAttribute("aria-expanded", "false");
                                viewBtn.disabled = false;
                            }

                            unitInput.addEventListener('keydown', (e) => {
                                const items = unitDropdown.querySelectorAll('li[role="option"]');
                                if (items.length === 0) return;

                                if (e.key === "ArrowDown") {
                                    e.preventDefault();
                                    activeIndex = (activeIndex + 1) % items.length;
                                    updateActiveItem(items);
                                } else if (e.key === "ArrowUp") {
                                    e.preventDefault();
                                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                                    updateActiveItem(items);
                                } else if (e.key === "Enter" && activeIndex >= 0) {
                                    e.preventDefault();
                                    items[activeIndex].click();
                                }
                            });

                            function updateActiveItem(items) {
                                items.forEach((item, i) => {
                                    item.classList.toggle("active", i === activeIndex);
                                });
                            }

                            document.addEventListener('click', (e) => {
                                if (!unitInput.contains(e.target) && !unitDropdown.contains(e.target)) {
                                    unitDropdown.classList.add('d-none');
                                    unitInput.setAttribute("aria-expanded", "false");
                                }
                            });

                            viewBtn.addEventListener('click', () => {
                                const productId = hiddenField.value;
                                if (productId) {
                                    window.location.href = "{{ url('Zcx1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV') }}/" + productId;
                                }
                            });
                        });
                    </script>

                </div>
            </div>
            <!-- /Products Table -->

        </div>
    </div>
@endsection

@section('scripts')
@endsection
