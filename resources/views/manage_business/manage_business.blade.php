@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="content-page-header">
                    <h5>Account List</h5>
                    <div class="page-content">
                        <div class="list-btn">
                            <ul class="filter-list">
                                <li>
                                    <a class="btn-filters" href="accounts" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="Refresh"><span><i class="fe fe-refresh-ccw"></i></span></a>
                                </li>
                                <li>
                                    <a class="btn btn-filters w-auto popup-toggle" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="Filter"><span class="me-2"><img
                                                src="assets/img/icons/filter-icon.svg" alt="filter"></span>Filter
                                    </a>
                                </li>

                                <li>
                                    <div class="dropdown dropdown-action" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="Download">
                                        <a href="companies.html#" class="btn btn-filters" data-bs-toggle="dropdown"
                                            aria-expanded="false"><span class="me-2"><i
                                                    class="fe fe-download"></i></span>Export</a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <ul class="d-block">
                                                <li>
                                                    <a class="d-flex align-items-center download-item"
                                                        href="javascript:void(0);" download><i
                                                            class="far fa-file-pdf me-2"></i>Export as PDF</a>
                                                </li>
                                                <li>
                                                    <a class="d-flex align-items-center download-item"
                                                        href="javascript:void(0);" download><i
                                                            class="far fa-file-text me-2"></i>Export as Excel</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a class="btn btn-filters" href="javascript:void(0);" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="Print"><span class="me-2"><i
                                                class="fe fe-printer"></i></span> Print
                                    </a>
                                </li>
                                <li>
                                    <a class="btn btn-outline-primary rounded-pill" href="companies.html#"
                                        data-bs-toggle="modal" data-bs-target="#add_companies"><i
                                            class="fa fa-plus-circle me-2" aria-hidden="true"></i>Add Account</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="super-admin-list-head">


                <div class="row">

                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                            style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="text-secondary mb-1 small">Total Accounts</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $count_business }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Account</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="assets/img/icons/receipt-item.svg" alt="clipboard" style="width: 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                            style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="text-secondary mb-1 small">Active Accounts</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $count_business_active }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="assets/img/icons/transaction-minus.svg" alt="transaction"
                                        style="width: 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                            style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="text-secondary mb-1 small">Inactive Accounts</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $count_business_inactive }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="assets/img/icons/clipboard-close.svg" alt="clipboard close"
                                        style="width: 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                            style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="text-secondary mb-1 small">Pending Accounts</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $count_business_pending }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="assets/img/icons/archive-book.svg" alt="archive book" style="width: 20px;">
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

            <div class="row">
                <div class="col-sm-12">
                    <div class="card-table">
                        <div class="card-body">
                            <div class="table-responsive">
                                <div class="companies-table">
                                    <table class="table table-center table-hover datatable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th Class="no-sort">SN</th>
                                                <th>Business Name</th>
                                                <th>Address</th>
                                                <th>Website</th>
                                                <th>Subscription</th>
                                                <th>Manage Account</th>
                                                <th>Created Date</th>
                                                <th>Status</th>
                                                <th Class="no-sort">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($business_list as $index => $business)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <h2 class="table-avatar">
                                                            <a href="#"
                                                                class="company-avatar avatar-md me-2 companies company-icon">
                                                                <img class="avatar-img rounded-circle company"
                                                                    src="{{ asset('storage/' . $business->logo) }}"
                                                                    alt="Company Image"></a>
                                                            <a href="#">{{ $business->business_name }}</a>
                                                        </h2>
                                                    </td>
                                                    <td>{{ $business->address }}
                                                    </td>
                                                    <td>{{ $business->website }}</td>
                                                    <td>{{ $business->subscription_plan }}</td>
                                                    <td><a href="switches/{{ Crypt::encrypt($business->business_key) }}"><span
                                                                class="badge bg-purple">Manage Account
                                                            </span></a></td>
                                                    <td>{{ $business->created_at->format('d-m-Y') }}</td>
                                                    <td><span
                                                            class="badge bg-success-light d-inline-flex align-items-center"><i
                                                                class="fe fe-check me-1"></i>Active</span></td>
                                                    <td class="d-flex align-items-center">
                                                        <div class="dropdown dropdown-action">
                                                            <a href="companies.html#" class=" btn-action-icon "
                                                                data-bs-toggle="dropdown" aria-expanded="false"><i
                                                                    class="fas fa-ellipsis-v"></i></a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <ul class="dropdown-ul">
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="javascript:void(0);"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#view_companies"><i
                                                                                class="far fa-eye me-2"></i>View
                                                                            Company</a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="javascript:void(0);"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#edit_companies"><i
                                                                                class="fe fe-edit me-2"></i>Edit</a>
                                                                    </li>
                                                                    <li class="delete-alt">
                                                                        <div>
                                                                            <a class="dropdown-item"
                                                                                href="javascript:void(0);"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#delete_modal"><i
                                                                                    class="fe fe-trash-2 me-2"></i>Delete</a>
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="javascript:void(0);"><i
                                                                                class="fe fe-user-x me-2"></i>Cancel
                                                                            Plan</a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="javascript:void(0);"><i
                                                                                class="fe fe-shuffle me-2"></i>Subscription
                                                                            Log</a>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add Companies Modal -->
    <div class="modal custom-modal custom-lg-modal fade p-20" id="add_companies" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div class="form-header modal-header-title text-start mb-0">
                        <h4 class="mb-0">Add New Business</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form action="{{ route('business.accounts.store') }}" method="POST" class="needs-validation" novalidate
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <h5 class="form-title mb-3">Business Profile</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="profile-img me-3">
                                <img id="company-img" class="img-fluid" src="assets/img/companies/company-add-img.svg"
                                    alt="profile-img">
                            </div>
                            <div>
                                <h6 class="mb-1">Upload a New Photo</h6>
                                <span class="text-muted small">Logo.png</span>
                                <input type="file" class="form-control mt-2" name="logo" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" name="business_name" required
                                placeholder="Enter Business Name">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" required
                                    placeholder="Company Address">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website URL</label>
                                <div class="input-group">
                                    <input type="text" name="website" class="form-control" placeholder="Account URL">
                                    <span class="input-group-text">domain.com</span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input class="form-control" id="phone" name="phone" type="text" required
                                    placeholder="Phone Number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" name="slug" placeholder="Enter slug">
                                @error('slug')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">About Business</label>
                            <textarea name="about_business" class="form-control" rows="3" placeholder="Describe your business"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <select class="form-select" name="country" required>
                                    <option value="">Choose Country</option>
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Brunei">Brunei</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cabo Verde">Cabo Verde</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo (Congo-Brazzaville)">Congo (Congo-Brazzaville)</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Democratic Republic of the Congo">Democratic Republic of the Congo
                                    </option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Eswatini">Eswatini</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guinea-Bissau">Guinea-Bissau</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran">Iran</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Ivory Coast">Ivory Coast</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Laos">Laos</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libya">Libya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Marshall Islands">Marshall Islands</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Micronesia">Micronesia</option>
                                    <option value="Moldova">Moldova</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="North Korea">North Korea</option>
                                    <option value="North Macedonia">North Macedonia</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palau">Palau</option>
                                    <option value="Palestine">Palestine</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russia">Russia</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                    <option value="Saint Lucia">Saint Lucia</option>
                                    <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines
                                    </option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Korea">South Korea</option>
                                    <option value="South Sudan">South Sudan</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syria">Syria</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Timor-Leste">Timor-Leste</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Vatican City">Vatican City</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Vietnam">Vietnam</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subscription Type</label>
                                <select class="form-select" name="subscription_type">
                                    <option value="">Select Plan Type</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Yearly">Yearly</option>
                                    <option disabled>Lifetime</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subscription Plan</label>
                                <select class="form-select" name="subscription_plan">
                                    <option value="Basic">Choose Plan</option>
                                    <option>Basic</option>
                                    <option>Enterprise</option>
                                    <option>Premium</option>
                                    <option>Diamond</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industry Type</label>
                                <select class="form-select" name="industry_type" required>
                                    <option value="">Choose Type</option>
                                    <option value="automotive">Automotive</option>
                                    <option value="aerospace">Aerospace</option>
                                    <option value="electronics_manufacturing">Electronics Manufacturing</option>
                                    <option value="furniture_manufacturing">Furniture Manufacturing</option>
                                    <option value="food_beverage_manufacturing">Food & Beverage Manufacturing</option>
                                    <option value="textile_apparel">Textile & Apparel</option>
                                    <option value="chemical_manufacturing">Chemical Manufacturing</option>
                                    <option value="pharmaceutical">Pharmaceutical</option>
                                    <option value="plastics_rubber">Plastics & Rubber Products</option>
                                    <option value="industrial_machinery">Industrial Machinery</option>

                                    <!-- Retail & Wholesale -->
                                    <option value="fashion_apparel">Fashion & Apparel</option>
                                    <option value="electronics_retail">Electronics & Appliances</option>
                                    <option value="grocery_supermarket">Grocery & Supermarkets</option>
                                    <option value="home_furniture">Home & Furniture</option>
                                    <option value="beauty_cosmetics">Beauty & Cosmetics</option>
                                    <option value="jewelry">Jewelry</option>
                                    <option value="sporting_goods">Sporting Goods</option>
                                    <option value="bookstores">Bookstores</option>
                                    <option value="convenience_stores">Convenience Stores</option>
                                    <option value="pet_supplies">Pet Supplies</option>

                                    <!-- Food & Hospitality -->
                                    <option value="restaurants">Restaurants</option>
                                    <option value="cafes_bakeries">Cafes & Bakeries</option>
                                    <option value="catering">Catering Services</option>
                                    <option value="hotels_resorts">Hotels & Resorts</option>
                                    <option value="bars_nightclubs">Bars & Nightclubs</option>

                                    <!-- Healthcare & Medical -->
                                    <option value="hospitals_clinics">Hospitals & Clinics</option>
                                    <option value="pharmacies">Pharmacies</option>
                                    <option value="medical_equipment">Medical Equipment Suppliers</option>
                                    <option value="veterinary_clinics">Veterinary Clinics</option>

                                    <!-- Construction & Materials -->
                                    <option value="hardware_stores">Hardware Stores</option>
                                    <option value="construction_companies">Construction Companies</option>
                                    <option value="plumbing_electrical">Plumbing & Electrical Supplies</option>
                                    <option value="paint_coatings">Paint & Coatings</option>

                                    <!-- Logistics & Distribution -->
                                    <option value="warehousing">Warehousing</option>
                                    <option value="freight_cargo">Freight & Cargo</option>
                                    <option value="courier_services">Courier Services</option>
                                    <option value="third_party_logistics">Third-Party Logistics (3PL)</option>

                                    <!-- E-commerce -->
                                    <option value="online_retail">Online Retail Stores</option>
                                    <option value="subscription_boxes">Subscription Box Services</option>
                                    <option value="print_on_demand">Print-on-Demand Stores</option>

                                    <!-- Chemical & Industrial Goods -->
                                    <option value="oil_gas">Oil & Gas</option>
                                    <option value="agrochemicals">Agrochemicals</option>
                                    <option value="cleaning_supplies">Cleaning Supplies</option>

                                    <!-- Education & Institutions -->
                                    <option value="school_supplies">School & Office Supplies</option>
                                    <option value="lab_equipment">Lab Equipment Inventory</option>
                                    <option value="libraries">Libraries</option>

                                    <!-- Office & IT -->
                                    <option value="computer_hardware">Computer Hardware</option>
                                    <option value="office_supplies">Office Supplies</option>
                                    <option value="printers_toners">Printer & Toner Inventory</option>

                                    <!-- Agriculture -->
                                    <option value="farm_produce">Farm Produce</option>
                                    <option value="livestock_supplies">Livestock Feed & Supplies</option>
                                    <option value="agricultural_equipment">Agricultural Equipment</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency" required>
                                    <option value="">Select Currency</option>
                                    <option value="$">$ - US Dollar (USD)</option>
                                    <option value="€">€ - Euro (EUR)</option>
                                    <option value="£">£ - British Pound (GBP)</option>
                                    <option value="¥">¥ - Japanese Yen (JPY)</option>
                                    <option value="¥">¥ - Chinese Yuan (CNY)</option>
                                    <option value="₹">₹ - Indian Rupee (INR)</option>
                                    <option value="$">$ - Australian Dollar (AUD)</option>
                                    <option value="$">$ - Canadian Dollar (CAD)</option>
                                    <option value="CHF">CHF - Swiss Franc (CHF)</option>
                                    <option value="$">$ - New Zealand Dollar (NZD)</option>
                                    <option value="R">R - South African Rand (ZAR)</option>
                                    <option value="₦">₦ - Nigerian Naira (NGN)</option>
                                    <option value="KSh">KSh - Kenyan Shilling (KES)</option>
                                    <option value="₵">₵ - Ghanaian Cedi (GHS)</option>
                                    <option value="FCFA">FCFA - Central African CFA Franc (XAF)</option>
                                    <option value="CFA">CFA - West African CFA Franc (XOF)</option>
                                    <option value="﷼">﷼ - Saudi Riyal (SAR)</option>
                                    <option value="د.إ">د.إ - UAE Dirham (AED)</option>
                                    <option value="﷼">﷼ - Qatari Riyal (QAR)</option>
                                    <option value="£">£ - Egyptian Pound (EGP)</option>
                                    <option value="R$">R$ - Brazilian Real (BRL)</option>
                                    <option value="$">$ - Mexican Peso (MXN)</option>
                                    <option value="$">$ - Singapore Dollar (SGD)</option>
                                    <option value="$">$ - Hong Kong Dollar (HKD)</option>
                                    <option value="RM">RM - Malaysian Ringgit (MYR)</option>
                                    <option value="฿">฿ - Thai Baht (THB)</option>
                                    <option value="₩">₩ - South Korean Won (KRW)</option>
                                    <option value="kr">kr - Swedish Krona (SEK)</option>
                                    <option value="kr">kr - Norwegian Krone (NOK)</option>
                                    <option value="kr">kr - Danish Krone (DKK)</option>
                                    <option value="₽">₽ - Russian Ruble (RUB)</option>
                                    <option value="₺">₺ - Turkish Lira (TRY)</option>
                                    <option value="₨">₨ - Pakistani Rupee (PKR)</option>
                                    <option value="৳">৳ - Bangladeshi Taka (BDT)</option>
                                    <option value="Rs">Rs - Sri Lankan Rupee (LKR)</option>
                                    <option value="NT$">NT$ - New Taiwan Dollar (TWD)</option>
                                    <option value="₫">₫ - Vietnamese Dong (VND)</option>
                                    <option value="Rp">Rp - Indonesian Rupiah (IDR)</option>
                                    <option value="zł">zł - Polish Zloty (PLN)</option>
                                    <option value="Kč">Kč - Czech Koruna (CZK)</option>
                                    <option value="Ft">Ft - Hungarian Forint (HUF)</option>
                                    <option value="₪">₪ - Israeli Shekel (ILS)</option>
                                    <option value="$">$ - Argentine Peso (ARS)</option>
                                    <option value="$">$ - Chilean Peso (CLP)</option>
                                    <option value="$">$ - Colombian Peso (COP)</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Language</label>
                                <select class="form-select" name="language" required>
                                    <option value="">Select Language</option>
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="it">Italian</option>
                                    <option value="pt">Portuguese</option>
                                    <option value="ru">Russian</option>
                                    <option value="zh">Chinese (Simplified)</option>
                                    <option value="ja">Japanese</option>
                                    <option value="ko">Korean</option>
                                    <option value="ar">Arabic</option>
                                    <option value="hi">Hindi</option>
                                    <option value="bn">Bengali</option>
                                    <option value="pa">Punjabi</option>
                                    <option value="ur">Urdu</option>
                                    <option value="sw">Swahili</option>
                                    <option value="tr">Turkish</option>
                                    <option value="vi">Vietnamese</option>
                                    <option value="fa">Persian (Farsi)</option>
                                    <option value="th">Thai</option>
                                    <option value="pl">Polish</option>
                                    <option value="ro">Romanian</option>
                                    <option value="nl">Dutch</option>
                                    <option value="el">Greek</option>
                                    <option value="hu">Hungarian</option>
                                    <option value="cs">Czech</option>
                                    <option value="sv">Swedish</option>
                                    <option value="no">Norwegian</option>
                                    <option value="fi">Finnish</option>
                                    <option value="he">Hebrew</option>
                                    <option value="id">Indonesian</option>
                                    <option value="ms">Malay</option>
                                    <option value="am">Amharic</option>
                                    <option value="yo">Yoruba</option>
                                    <option value="ig">Igbo</option>
                                    <option value="ha">Hausa</option>
                                    <option value="zu">Zulu</option>
                                    <option value="xh">Xhosa</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <h6 class="me-3 mb-0">Status</h6>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="access-trail" name="status"
                                    checked>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-primary rounded-pill">Add Business</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- /Add Companies Modal -->
@endsection
