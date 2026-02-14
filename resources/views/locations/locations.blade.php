@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- /Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Business Locations List</h5>
                        <p class="text-muted mb-0">Manage your organization's Locations</p>
                    </div>

                    <div class="btn-group">
                        <!-- Refresh Button -->
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()"
                            data-bs-toggle="tooltip" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>

                        <!-- Export Button -->
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="downloadCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>

                        <!-- Add User Button (Conditional) -->
                        @auth
                            @if (auth()->user()->hasPermission('users_create'))
                                <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                    data-bs-target="#add_companies">
                                    <i class="fas fa-plus-circle me-1"></i> Add Location
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>



            <div class="super-admin-list-head">
                <div class="content container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card-table">
                                <div class="card-body">
                                    <input type="text" id="searchInput" placeholder="Search for products ..."
                                        onkeyup="searchTable()" class="form-control mb-3">
                                    <div class="table-responsive">
                                        <table class="table table-center table-hover datatable table-bold" id="myTable">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>SN</th>
                                                    <th>Location Name</th>
                                                    <th>Address</th>
                                                    <th>City</th>
                                                    <th>State/Region</th>
                                                    <th>Country</th>
                                                    <th>Phone</th>
                                                    <th>Manager</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($locations as $index => $location)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $location->location_name }}</td>
                                                        <td>{{ $location->address }}</td>
                                                        <td>{{ $location->city }}</td>
                                                        <td>{{ $location->state }}</td>
                                                        <td>{{ $location->country }}</td>
                                                        <td>{{ $location->phone }}</td>
                                                        <td>{{ $location->user->name }}</td>
                                                        <td>
                                                            @if ($location->status === 'active')
                                                                <span class="badge bg-success-light">Active</span>
                                                            @else
                                                                <span class="badge badge-danger">Inactive</span>
                                                            @endif
                                                        </td>
                                                        <td class="d-flex align-items-center">
                                                            <div class="dropdown dropdown-action">
                                                                <a href="#" class="btn-action-icon"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <ul>
                                                                        <li>
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('location.accounts.show', Crypt::encrypt($location->id)) }}">
                                                                                <i class="far fa-edit me-2"></i>Edit
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('location.account.delete', Crypt::encrypt($location->id)) }}">
                                                                                <i class="far fa-trash-alt me-2"></i>Delete
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="10" class="text-center">No business locations found.
                                                        </td>
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


            <!-- Search Filter -->
            <div id="filter_inputs" class="card filter-card">
                <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="input-block mb-3">
                                <label>Name</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="input-block mb-3">
                                <label>Email</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="input-block mb-3">
                                <label>Phone</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Search Filter -->


        </div>
    </div>


    <div class="modal fade" id="add_companies" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow-lg rounded-4">
                <div class="modal-header border-0 bg-light px-4 py-3">
                    <h5 class="modal-title fw-semibold text-dark">Add New Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>


                <form action="{{ route('location.accounts.store') }}" method="POST" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <h6 class="mt-2 mb-3 fw-bold">Location Information</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" name="location_name"
                                        id="location_name" placeholder=" " required>
                                    <label for="location_name">Location Name</label>
                                    <div class="invalid-feedback">Please provide a location name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control rounded-3" name="phone" id="phone"
                                        placeholder=" " pattern="[0-9+()\-\s]{7,}" required>
                                    <label for="phone">Phone</label>
                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 fw-bold">Address Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" name="address" id="address"
                                        placeholder=" " required>
                                    <label for="address">Address</label>
                                    <div class="invalid-feedback">Please enter an address.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" name="city" id="city"
                                        placeholder=" " required>
                                    <label for="city">City</label>
                                    <div class="invalid-feedback">Please enter a city.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" name="state" id="state"
                                        placeholder=" " required>
                                    <label for="state">State</label>
                                    <div class="invalid-feedback">Please enter a state.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input list="countries" class="form-control rounded-3" name="country" id="country"
                                        placeholder=" " required>
                                    <label for="country">Country</label>

                                    <datalist id="countries">
                                        <option value="Afghanistan">
                                        <option value="Albania">
                                        <option value="Algeria">
                                        <option value="Andorra">
                                        <option value="Angola">
                                        <option value="Antigua and Barbuda">
                                        <option value="Argentina">
                                        <option value="Armenia">
                                        <option value="Australia">
                                        <option value="Austria">
                                        <option value="Azerbaijan">
                                        <option value="Bahamas">
                                        <option value="Bahrain">
                                        <option value="Bangladesh">
                                        <option value="Barbados">
                                        <option value="Belarus">
                                        <option value="Belgium">
                                        <option value="Belize">
                                        <option value="Benin">
                                        <option value="Bhutan">
                                        <option value="Bolivia">
                                        <option value="Bosnia and Herzegovina">
                                        <option value="Botswana">
                                        <option value="Brazil">
                                        <option value="Brunei">
                                        <option value="Bulgaria">
                                        <option value="Burkina Faso">
                                        <option value="Burundi">
                                        <option value="Cabo Verde">
                                        <option value="Cambodia">
                                        <option value="Cameroon">
                                        <option value="Canada">
                                        <option value="Central African Republic">
                                        <option value="Chad">
                                        <option value="Chile">
                                        <option value="China">
                                        <option value="Colombia">
                                        <option value="Comoros">
                                        <option value="Congo (Congo-Brazzaville)">
                                        <option value="Costa Rica">
                                        <option value="Croatia">
                                        <option value="Cuba">
                                        <option value="Cyprus">
                                        <option value="Czech Republic">
                                        <option value="Democratic Republic of the Congo">
                                        <option value="Denmark">
                                        <option value="Djibouti">
                                        <option value="Dominica">
                                        <option value="Dominican Republic">
                                        <option value="Ecuador">
                                        <option value="Egypt">
                                        <option value="El Salvador">
                                        <option value="Equatorial Guinea">
                                        <option value="Eritrea">
                                        <option value="Estonia">
                                        <option value="Eswatini">
                                        <option value="Ethiopia">
                                        <option value="Fiji">
                                        <option value="Finland">
                                        <option value="France">
                                        <option value="Gabon">
                                        <option value="Gambia">
                                        <option value="Georgia">
                                        <option value="Germany">
                                        <option value="Ghana">
                                        <option value="Greece">
                                        <option value="Grenada">
                                        <option value="Guatemala">
                                        <option value="Guinea">
                                        <option value="Guinea-Bissau">
                                        <option value="Guyana">
                                        <option value="Haiti">
                                        <option value="Honduras">
                                        <option value="Hungary">
                                        <option value="Iceland">
                                        <option value="India">
                                        <option value="Indonesia">
                                        <option value="Iran">
                                        <option value="Iraq">
                                        <option value="Ireland">
                                        <option value="Israel">
                                        <option value="Italy">
                                        <option value="Ivory Coast">
                                        <option value="Jamaica">
                                        <option value="Japan">
                                        <option value="Jordan">
                                        <option value="Kazakhstan">
                                        <option value="Kenya">
                                        <option value="Kiribati">
                                        <option value="Kuwait">
                                        <option value="Kyrgyzstan">
                                        <option value="Laos">
                                        <option value="Latvia">
                                        <option value="Lebanon">
                                        <option value="Lesotho">
                                        <option value="Liberia">
                                        <option value="Libya">
                                        <option value="Liechtenstein">
                                        <option value="Lithuania">
                                        <option value="Luxembourg">
                                        <option value="Madagascar">
                                        <option value="Malawi">
                                        <option value="Malaysia">
                                        <option value="Maldives">
                                        <option value="Mali">
                                        <option value="Malta">
                                        <option value="Marshall Islands">
                                        <option value="Mauritania">
                                        <option value="Mauritius">
                                        <option value="Mexico">
                                        <option value="Micronesia">
                                        <option value="Moldova">
                                        <option value="Monaco">
                                        <option value="Mongolia">
                                        <option value="Montenegro">
                                        <option value="Morocco">
                                        <option value="Mozambique">
                                        <option value="Myanmar">
                                        <option value="Namibia">
                                        <option value="Nauru">
                                        <option value="Nepal">
                                        <option value="Netherlands">
                                        <option value="New Zealand">
                                        <option value="Nicaragua">
                                        <option value="Niger">
                                        <option value="Nigeria">
                                        <option value="North Korea">
                                        <option value="North Macedonia">
                                        <option value="Norway">
                                        <option value="Oman">
                                        <option value="Pakistan">
                                        <option value="Palau">
                                        <option value="Palestine">
                                        <option value="Panama">
                                        <option value="Papua New Guinea">
                                        <option value="Paraguay">
                                        <option value="Peru">
                                        <option value="Philippines">
                                        <option value="Poland">
                                        <option value="Portugal">
                                        <option value="Qatar">
                                        <option value="Romania">
                                        <option value="Russia">
                                        <option value="Rwanda">
                                        <option value="Saint Kitts and Nevis">
                                        <option value="Saint Lucia">
                                        <option value="Saint Vincent and the Grenadines">
                                        <option value="Samoa">
                                        <option value="San Marino">
                                        <option value="Sao Tome and Principe">
                                        <option value="Saudi Arabia">
                                        <option value="Senegal">
                                        <option value="Serbia">
                                        <option value="Seychelles">
                                        <option value="Sierra Leone">
                                        <option value="Singapore">
                                        <option value="Slovakia">
                                        <option value="Slovenia">
                                        <option value="Solomon Islands">
                                        <option value="Somalia">
                                        <option value="South Africa">
                                        <option value="South Korea">
                                        <option value="South Sudan">
                                        <option value="Spain">
                                        <option value="Sri Lanka">
                                        <option value="Sudan">
                                        <option value="Suriname">
                                        <option value="Sweden">
                                        <option value="Switzerland">
                                        <option value="Syria">
                                        <option value="Taiwan">
                                        <option value="Tajikistan">
                                        <option value="Tanzania">
                                        <option value="Thailand">
                                        <option value="Timor-Leste">
                                        <option value="Togo">
                                        <option value="Tonga">
                                        <option value="Trinidad and Tobago">
                                        <option value="Tunisia">
                                        <option value="Turkey">
                                        <option value="Turkmenistan">
                                        <option value="Tuvalu">
                                        <option value="Uganda">
                                        <option value="Ukraine">
                                        <option value="United Arab Emirates">
                                        <option value="United Kingdom">
                                        <option value="United States">
                                        <option value="Uruguay">
                                        <option value="Uzbekistan">
                                        <option value="Vanuatu">
                                        <option value="Vatican City">
                                        <option value="Venezuela">
                                        <option value="Vietnam">
                                        <option value="Yemen">
                                        <option value="Zambia">
                                        <option value="Zimbabwe">
                                    </datalist>

                                    <div class="invalid-feedback">Please select a country.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="manager" id="" class="form-control rounded-3">
                                        @foreach ($get_users as $users)
                                            <option value="{{ encrypt($users->id) }}">{{ $users->name }}</option>
                                        @endforeach

                                    </select>
                                    <label for="country">Choose Manager</label>
                                    <div class="invalid-feedback">Please select a country.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" name="postal_code"
                                        id="postal_code" placeholder=" " required>
                                    <label for="postal_code">Postal Code</label>
                                    <div class="invalid-feedback">Please enter a postal code.</div>
                                </div>
                            </div>

                        </div>
                    </div>

                    @auth
                        <div class="modal-footer border-0 px-4 pb-4 pt-2">
                            <button type="button" class="btn btn-light border rounded-pill px-4"
                                data-bs-dismiss="modal">Cancel</button>
                            @if (auth()->user()->hasPermission('users_create'))
                                <button type="submit" class="btn btn-primary rounded-pill px-5" id="submit-btn">
                                    <span class="submit-text">Add Location</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            @endif
                        </div>
                    @endauth
                </form>



            </div>
        </div>
    </div>


    <!-- Add Companies Modal -->

    <!-- /Add Companies Modal -->
@endsection
