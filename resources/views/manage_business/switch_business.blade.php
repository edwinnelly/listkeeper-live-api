@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="content-page-header">
                    <div class="customer-details">
                        <div class="d-flex align-items-center">
                            <span class="customer-widget-img d-inline-flex">
                                <img class="rounded-circle" src="{{ asset('storage/' . $business_switches->logo) }}"
                                    alt="profile-img" style="max-height: 80px">
                            </span>
                            <div class="customer-details-cont">
                                <h6 style="padding-left: 10px">{{ $business_switches->business_name }}</h6>
                                <p style="padding-left: 10px">{{ $business_switches->slug }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- <h5>{{ $business_switches->business_name }}</h5> --}}
                    <div class="page-content">
                        <div class="list-btn">
                            <ul class="filter-list">
                                <li>
                                    <a class="btn-filters" href="#" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="Refresh"><span><i
                                                class="fe fe-refresh-ccw"></i></span></a>
                                </li>
                                <li>
                                    <a href="../accounts"
                                        class="btn btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-2 shadow-sm"
                                        aria-label="Go to Business List">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" height="18" width="18">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 7h18M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l3.6-4.8A1 1 0 018 2h8a1 1 0 01.8.4L21 7" />
                                        </svg>
                                        <span>Business List</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            @if ($errors->any())
                <div class="alert alert-danger rounded">
                    <strong>Whoops! Something went wrong.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="super-admin-list-head">

                <div class="row">

                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                            style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="text-secondary mb-1 small"> Total Locations</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $business_location }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Account</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="{{ asset('assets/img/icons/receipt-item.svg') }}" alt="clipboard" style="width: 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                            style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="text-secondary mb-1 small">Active Locations</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $location_status }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Account</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="{{ asset('assets/img/icons/transaction-minus.svg')}}" alt="transaction"
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
                                    <h6 class="text-secondary mb-1 small">Inactive Locations</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $location_status_inactive }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Account</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="{{ asset('assets/img/icons/clipboard-close.svg')}}" alt="clipboard close"
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
                                    <h6 class="text-secondary mb-1 small">Inactive Locations</h6>
                                    <h4 class="fw-bold text-dark mb-2">{{ $location_status_inactive }}</h4>
                                    <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                        <small>Manage Account</small> <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                    <img src="{{ asset('assets/img/icons/archive-book.svg')}}" alt="archive book" style="width: 20px;">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


            <div class="container my-4">
                <div class="row g-4">
                    @php
                        $details = [
                            ['icon' => 'globe', 'label' => 'Website', 'value' => $business_switches->website],
                            ['icon' => 'map-pin', 'label' => 'Country', 'value' => $business_switches->country],
                            ['icon' => 'dollar-sign', 'label' => 'Currency', 'value' => $business_switches->currency],
                            [
                                'icon' => 'briefcase',
                                'label' => 'Business Type',
                                'value' => $business_switches->industry_type,
                            ],
                            ['icon' => 'map', 'label' => 'Company Address', 'value' => $business_switches->address],
                        ];
                    @endphp

                    @foreach ($details as $detail)
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-sm border-0 h-100 hover-shadow transition rounded-4 p-3 bg-light">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-primary flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feather" width="28"
                                            height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            @if ($detail['icon'] == 'globe')
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="2" y1="12" x2="22" y2="12" />
                                                <path d="M12 2a15.3 15.3 0 0 1 0 20" />
                                            @elseif ($detail['icon'] == 'map-pin')
                                                <path d="M21 10c0 5.25-9 13-9 13S3 15.25 3 10a9 9 0 1118 0z" />
                                                <circle cx="12" cy="10" r="3" />
                                            @elseif ($detail['icon'] == 'dollar-sign')
                                                <line x1="12" y1="1" x2="12" y2="23" />
                                                <path d="M17 5H9a3 3 0 000 6h6a3 3 0 010 6H6" />
                                            @elseif ($detail['icon'] == 'briefcase')
                                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                                <path d="M16 3h-8v4h8V3z" />
                                            @elseif ($detail['icon'] == 'map')
                                                <polygon points="1 6 8 3 16 6 23 3 23 18 16 21 8 18 1 21 1 6" />
                                                <line x1="8" y1="3" x2="8" y2="18" />
                                                <line x1="16" y1="6" x2="16" y2="21" />
                                            @endif
                                        </svg>
                                    </div>
                                    <div>
                                        <h6 class="fw-semibold mb-1">{{ $detail['label'] }}</h6>
                                        <p class="mb-0 text-muted">{{ $detail['value'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>





            @if (in_array($subscription->status, ['inactive', 'cancelled', 'expired']))
                <style>
                    @keyframes slideFadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    .animated-alert {
                        animation: slideFadeIn 0.6s ease-out forwards;
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                    }

                    .animated-alert:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
                    }
                </style>

                <div class="alert alert-warning p-4 rounded shadow-sm animated-alert text-center">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                            class="feather feather-alert-triangle text-warning" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" height="40">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12" y2="17" />
                        </svg>
                    </div>
                    <h5 class="fw-bold mb-2">Subscription Inactive</h5>
                    <p class="mb-3">Please renew your subscription to switch accounts.</p>
                    <a href="#" class="btn btn-primary btn-lg rounded-pill px-4">Pay Now</a>
                </div>
            @else
                <div class="d-flex justify-content-center">
                    <form action="{{ route('business.accounts.switchBusiness') }}" method="POST"
                        class="d-inline-block">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="switch_business"
                            value="{{ Crypt::encrypt($business_switches->business_key) }}">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                class="feather feather-refresh-cw me-2" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24" height="20">
                                <polyline points="23 4 23 10 17 10" />
                                <polyline points="1 20 1 14 7 14" />
                                <path d="M3.51 9a9 9 0 0114.13-3.36L23 10M1 14l5.36 5.36A9 9 0 0020.49 15" />
                            </svg>
                            Switch Account Now
                        </button>
                    </form>
                </div>
            @endif











        </div>
    </div>
@endsection
