<div class="page-wrapper">
    <div class="content container-fluid pb-0">

        <!-- /Page Header -->
        <div class="super-admin-dashboard">
            <div class="row">
                @auth
                    @if (Auth::user()->active_business_key == 0)
                        <div class="d-flex justify-content-center align-items-center mt-5">
                            <div class="col-md-6">
                                <div class="p-5 rounded-4 shadow-lg border border-0 bg-light bg-opacity-75"
                                    style="backdrop-filter: blur(8px);">
                                    <div class="text-center mb-4">
                                        <h4 class="fw-semibold text-dark">🚀 Ready to Launch a New Business?</h4>
                                        <p class="text-muted">Just a few quick notes before you begin:</p>
                                    </div>

                                    <ul class="list-unstyled px-3">
                                        <li class="mb-3">
                                            <i class="bi bi-building text-primary me-2 fs-5"></i>
                                            Fill in the business name, address, and logo.
                                        </li>
                                        <li class="mb-3">
                                            <i class="bi bi-diagram-3-fill text-success me-2 fs-5"></i>
                                            You can add multiple branches later.
                                        </li>
                                        <li class="mb-3">
                                            <i class="bi bi-gear-fill text-warning me-2 fs-5"></i>
                                            Each business has its own accounts and settings.
                                        </li>
                                    </ul>

                                    <div class="text-center">
                                        <a href="../accounts" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">+
                                            Create New Business</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-xl-12 d-flex">
                            <div class="dash-user-card w-100">
                                <h4>
                                    <span>
                                        <img src="{{ asset('storage/' . $business_actice_info->logo) }}"
                                            style="max-height:40px" alt="">
                                    </span>
                                    <span style="padding-left: 7px">{{ $business_actice_info->business_name }}</span>
                                </h4>
                                <p>{{ $business_actice_info->address }}</p>

                                @auth
                                    @if (Auth::user()->creator == 'Host')
                                        <span style="padding-left: 7px">{{ $business_actice_info->business_name }}</span>

                                        <a href="/accounts"><button class="btn btn-ai" style="margin-top: 20px;">
                                                <i class="fas fa-recycle me-2"></i> Switch Account
                                            </button></a>
                                    @else
                                        <h2 style="color: white;font-weight:normal;font-size:20px"> Welcome back
                                            {{ Auth()->user()->name }}
                                        </h2>
                                        <span style="padding-left: 7px">{{ $business_actice_info->business_name }}</span>
                                        <a href=""><button class="btn btn-ai" style="margin-top: 20px;">
                                                <i class="fas fa-book me-2"></i> My Reports
                                            </button></a>
                                    @endif

                                @endauth



                                <div class="dash-img">
                                    <img src="assets/img/dashboard-card-img.png" alt="">
                                </div>
                            </div>
                        </div>


                        {{-- @endif --}}
                    @endif
                @endauth

                @if (Auth::user()->active_business_key == 0)
                    {{-- Put whatever should be shown for business_key == 2 --}}
                @else
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                            <div class="card shadow-sm border-0 rounded-4 w-100 position-relative hover-shadow"
                                style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">
                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Total Users</h6>
                                        <h4 class="fw-bold text-dark mb-2">112</h4>
                                        <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                            <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                        <img src="assets/img/icons/receipt-item.svg" alt="clipboard"
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
                                        <h6 class="text-secondary mb-1 small">Total Users</h6>
                                        <h4 class="fw-bold text-dark mb-2">232</h4>
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
                                        <h6 class="text-secondary mb-1 small">Total Users</h6>
                                        <h4 class="fw-bold text-dark mb-2">12</h4>
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
                                        <h6 class="text-secondary mb-1 small">Total Users</h6>
                                        <h4 class="fw-bold text-dark mb-2">12</h4>
                                        <a href="#" class="text-primary text-decoration-none fw-semibold small">
                                            <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                        <img src="assets/img/icons/archive-book.svg" alt="archive book"
                                            style="width: 20px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    @auth
                        @if (Auth::user()->creator == 'Host')
                            <div class="row">


                                <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                                    <div class="card shadow-sm border-0 rounded-4 w-100 position-relative dashboard-card hover-shadow"
                                        style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">

                                        <!-- First Block -->
                                        <div
                                            class="card-body d-flex justify-content-between align-items-center p-3 border-bottom">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Total Sales</h6>
                                                <h4 class="fw-bold text-dark mb-2">₦455</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>Manage Sales</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                                <i class="fas fa-users fs-5"></i>
                                            </div>
                                        </div>

                                        <!-- Second Block -->
                                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                                            <!-- Second Block -->
                                            <div class="card-body d-flex justify-content-end align-items-center p-3">
                                                <button class="btn btn-ai">
                                                    <i class="fas fa-robot me-2"></i> Use AI
                                                </button>
                                            </div>

                                        </div>

                                    </div>
                                </div>



                                <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                                    <div class="card shadow-sm border-0 rounded-4 w-100 position-relative dashboard-card hover-shadow"
                                        style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">

                                        <!-- First Block -->
                                        <div
                                            class="card-body d-flex justify-content-between align-items-center p-3 border-bottom">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Total Users</h6>
                                                <h4 class="fw-bold text-dark mb-2">12</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                                <i class="fas fa-users fs-5"></i>
                                            </div>
                                        </div>

                                        <!-- Second Block -->
                                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Active Plans</h6>
                                                <h4 class="fw-bold text-dark mb-2">5</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>View Plans</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #36b9cc, #f6c23e);">
                                                <i class="fas fa-clipboard-list fs-5"></i>
                                            </div>
                                        </div>

                                    </div>
                                </div>



                                <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                                    <div class="card shadow-sm border-0 rounded-4 w-100 position-relative dashboard-card hover-shadow"
                                        style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">

                                        <!-- First Block -->
                                        <div
                                            class="card-body d-flex justify-content-between align-items-center p-3 border-bottom">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Total Users</h6>
                                                <h4 class="fw-bold text-dark mb-2">12</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                                <i class="fas fa-users fs-5"></i>
                                            </div>
                                        </div>

                                        <!-- Second Block -->
                                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Active Plans</h6>
                                                <h4 class="fw-bold text-dark mb-2">5</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>View Plans</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #36b9cc, #f6c23e);">
                                                <i class="fas fa-clipboard-list fs-5"></i>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                                <div class="col-xl-3 col-lg-4 col-sm-6 col-12 d-flex">
                                    <div class="card shadow-sm border-0 rounded-4 w-100 position-relative dashboard-card hover-shadow"
                                        style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); transition: all 0.3s ease-in-out; overflow: hidden;">

                                        <!-- First Block -->
                                        <div
                                            class="card-body d-flex justify-content-between align-items-center p-3 border-bottom">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Total Users</h6>
                                                <h4 class="fw-bold text-dark mb-2">12</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>Manage Users</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #4e73df, #1cc88a);">
                                                <i class="fas fa-users fs-5"></i>
                                            </div>
                                        </div>

                                        <!-- Second Block -->
                                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                                            <div>
                                                <h6 class="text-secondary mb-1 small">Active Plans</h6>
                                                <h4 class="fw-bold text-dark mb-2">5</h4>
                                                <a href="#"
                                                    class="text-primary text-decoration-none fw-semibold small">
                                                    <small>View Plans</small> <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                            <div class="icon-box text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                style="width: 44px; height: 44px; background: linear-gradient(135deg, #36b9cc, #f6c23e);">
                                                <i class="fas fa-clipboard-list fs-5"></i>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        @endif
                    @endauth


                    <div class="row">
                        <div class="col-xl-6 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body pb-0">
                                    <div class="mb-3">
                                        <h6 class="mb-1">Revenue</h6>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        <div>
                                            <p class="mb-1">Total Revenue</p>
                                            <div class="d-flex align-items-center">
                                                <h6 class="fs-16 fw-semibold me-2">897</h6>
                                                <span class="badge badge-sm badge-soft-success">+45<i
                                                        class="isax isax-arrow-up-15 ms-1"></i></span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <p class="fs-13 text-dark d-flex align-items-center mb-0"><i
                                                    class="fa-solid fa-circle text-primary-transparent fs-12 me-1"></i>Received
                                            </p>
                                            <p class="fs-13 text-dark d-flex align-items-center mb-0"><i
                                                    class="fa-solid fa-circle text-primary fs-12 me-1"></i>Outstanding
                                            </p>
                                        </div>
                                    </div>
                                    <div id="revenue_chart" style="min-height: 375px;" class="">
                                        <div id="apexchartsk31pm21w"
                                            class="apexcharts-canvas apexchartsk31pm21w apexcharts-theme-"
                                            style="width: 498px; height: 360px;"><svg
                                                xmlns="http://www.w3.org/2000/svg" version="1.1"
                                                xmlns:xlink="http://www.w3.org/1999/xlink" class="apexcharts-svg"
                                                xmlns:data="ApexChartsNS" transform="translate(0, 0)" width="498"
                                                height="360">
                                                <foreignObject x="0" y="0" width="498" height="360">
                                                    <style type="text/css">
                                                        .apexcharts-flip-y {
                                                            transform: scaleY(-1) translateY(-100%);
                                                            transform-origin: top;
                                                            transform-box: fill-box;
                                                        }

                                                        .apexcharts-flip-x {
                                                            transform: scaleX(-1);
                                                            transform-origin: center;
                                                            transform-box: fill-box;
                                                        }

                                                        .apexcharts-legend {
                                                            display: flex;
                                                            overflow: auto;
                                                            padding: 0 10px;
                                                        }

                                                        .apexcharts-legend.apexcharts-legend-group-horizontal {
                                                            flex-direction: column;
                                                        }

                                                        .apexcharts-legend-group {
                                                            display: flex;
                                                        }

                                                        .apexcharts-legend-group-vertical {
                                                            flex-direction: column-reverse;
                                                        }

                                                        .apexcharts-legend.apx-legend-position-bottom,
                                                        .apexcharts-legend.apx-legend-position-top {
                                                            flex-wrap: wrap
                                                        }

                                                        .apexcharts-legend.apx-legend-position-right,
                                                        .apexcharts-legend.apx-legend-position-left {
                                                            flex-direction: column;
                                                            bottom: 0;
                                                        }

                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-left,
                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-left,
                                                        .apexcharts-legend.apx-legend-position-right,
                                                        .apexcharts-legend.apx-legend-position-left {
                                                            justify-content: flex-start;
                                                            align-items: flex-start;
                                                        }

                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-center,
                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-center {
                                                            justify-content: center;
                                                            align-items: center;
                                                        }

                                                        .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-right,
                                                        .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
                                                            justify-content: flex-end;
                                                            align-items: flex-end;
                                                        }

                                                        .apexcharts-legend-series {
                                                            cursor: pointer;
                                                            line-height: normal;
                                                            display: flex;
                                                            align-items: center;
                                                        }

                                                        .apexcharts-legend-text {
                                                            position: relative;
                                                            font-size: 14px;
                                                        }

                                                        .apexcharts-legend-text *,
                                                        .apexcharts-legend-marker * {
                                                            pointer-events: none;
                                                        }

                                                        .apexcharts-legend-marker {
                                                            position: relative;
                                                            display: flex;
                                                            align-items: center;
                                                            justify-content: center;
                                                            cursor: pointer;
                                                            margin-right: 1px;
                                                        }

                                                        .apexcharts-legend-series.apexcharts-no-click {
                                                            cursor: auto;
                                                        }

                                                        .apexcharts-legend .apexcharts-hidden-zero-series,
                                                        .apexcharts-legend .apexcharts-hidden-null-series {
                                                            display: none !important;
                                                        }

                                                        .apexcharts-inactive-legend {
                                                            opacity: 0.45;
                                                        }
                                                    </style>
                                                </foreignObject>
                                                <g class="apexcharts-datalabels-group"
                                                    transform="translate(0, 0) scale(1)"></g>
                                                <g class="apexcharts-datalabels-group"
                                                    transform="translate(0, 0) scale(1)"></g>
                                                <g class="apexcharts-yaxis" rel="0"
                                                    transform="translate(-8, 0)">
                                                    <g class="apexcharts-yaxis-texts-g"></g>
                                                </g>
                                                <g class="apexcharts-inner apexcharts-graphical"
                                                    transform="translate(0, 30)">
                                                    <defs>
                                                        <linearGradient x1="0" y1="0" x2="0"
                                                            y2="1" id="SvgjsLinearGradient1012">
                                                            <stop stop-opacity="0.4"
                                                                stop-color="rgba(216,227,240,0.4)" offset="0">
                                                            </stop>
                                                            <stop stop-opacity="0.5"
                                                                stop-color="rgba(190,209,230,0.5)" offset="1">
                                                            </stop>
                                                            <stop stop-opacity="0.5"
                                                                stop-color="rgba(190,209,230,0.5)" offset="1">
                                                            </stop>
                                                        </linearGradient>
                                                        <clipPath id="gridRectMaskk31pm21w">
                                                            <rect width="500.3218746185303" height="296.348" x="-2"
                                                                y="-2" rx="0" ry="0" opacity="1"
                                                                stroke-width="0" stroke="none" stroke-dasharray="0"
                                                                fill="#fff"></rect>
                                                        </clipPath>
                                                        <clipPath id="gridRectBarMaskk31pm21w">
                                                            <rect width="500.3218746185303" height="296.348" x="-2"
                                                                y="-2" rx="0" ry="0" opacity="1"
                                                                stroke-width="0" stroke="none" stroke-dasharray="0"
                                                                fill="#fff"></rect>
                                                        </clipPath>
                                                        <clipPath id="gridRectMarkerMaskk31pm21w">
                                                            <rect width="496.3218746185303" height="292.348" x="0"
                                                                y="0" rx="0" ry="0" opacity="1"
                                                                stroke-width="0" stroke="none" stroke-dasharray="0"
                                                                fill="#fff"></rect>
                                                        </clipPath>
                                                        <clipPath id="forecastMaskk31pm21w"></clipPath>
                                                        <clipPath id="nonForecastMaskk31pm21w"></clipPath>
                                                        <filter id="SvgjsFilter1014" filterUnits="userSpaceOnUse"
                                                            width="200%" height="200%" x="-50%" y="-50%">
                                                            <feColorMatrix id="SvgjsFeColorMatrix1013"
                                                                result="brightness" in="SourceGraphic" type="matrix"
                                                                values="
          2 0 0 0 0
          0 2 0 0 0
          0 0 2 0 0
          0 0 0 1 0
        ">
                                                            </feColorMatrix>
                                                        </filter>
                                                    </defs>
                                                    <rect width="28.361249978201734" height="292.348"
                                                        x="233.09686549050468" y="0" rx="0" ry="0"
                                                        opacity="1" stroke-width="0" stroke="#b6b6b6"
                                                        stroke-dasharray="3" fill="url(#SvgjsLinearGradient1012)"
                                                        class="apexcharts-xcrosshairs" y2="292.348" filter="none"
                                                        fill-opacity="0.9" x1="233.09686549050468"
                                                        x2="233.09686549050468"></rect>
                                                    <line x1="0" y1="292.348" x2="0"
                                                        y2="298.348" stroke="#e0e0e0" stroke-dasharray="0"
                                                        stroke-linecap="butt" class="apexcharts-xaxis-tick">
                                                    </line>
                                                    <line x1="70.90312494550433" y1="292.348"
                                                        x2="70.90312494550433" y2="298.348" stroke="#e0e0e0"
                                                        stroke-dasharray="0" stroke-linecap="butt"
                                                        class="apexcharts-xaxis-tick"></line>
                                                    <line x1="141.80624989100866" y1="292.348"
                                                        x2="141.80624989100866" y2="298.348" stroke="#e0e0e0"
                                                        stroke-dasharray="0" stroke-linecap="butt"
                                                        class="apexcharts-xaxis-tick"></line>
                                                    <line x1="212.709374836513" y1="292.348" x2="212.709374836513"
                                                        y2="298.348" stroke="#e0e0e0" stroke-dasharray="0"
                                                        stroke-linecap="butt" class="apexcharts-xaxis-tick"></line>
                                                    <line x1="283.6124997820173" y1="292.348"
                                                        x2="283.6124997820173" y2="298.348" stroke="#e0e0e0"
                                                        stroke-dasharray="0" stroke-linecap="butt"
                                                        class="apexcharts-xaxis-tick"></line>
                                                    <line x1="354.51562472752164" y1="292.348"
                                                        x2="354.51562472752164" y2="298.348" stroke="#e0e0e0"
                                                        stroke-dasharray="0" stroke-linecap="butt"
                                                        class="apexcharts-xaxis-tick"></line>
                                                    <line x1="425.41874967302596" y1="292.348"
                                                        x2="425.41874967302596" y2="298.348" stroke="#e0e0e0"
                                                        stroke-dasharray="0" stroke-linecap="butt"
                                                        class="apexcharts-xaxis-tick"></line>
                                                    <line x1="496.3218746185303" y1="292.348"
                                                        x2="496.3218746185303" y2="298.348" stroke="#e0e0e0"
                                                        stroke-dasharray="0" stroke-linecap="butt"
                                                        class="apexcharts-xaxis-tick"></line>
                                                    <g class="apexcharts-grid">
                                                        <g class="apexcharts-gridlines-horizontal">
                                                            <line x1="0" y1="58.4696"
                                                                x2="496.3218746185303" y2="58.4696"
                                                                stroke="#e2e4e6" stroke-dasharray="5"
                                                                stroke-linecap="butt" class="apexcharts-gridline">
                                                            </line>
                                                            <line x1="0" y1="116.9392"
                                                                x2="496.3218746185303" y2="116.9392"
                                                                stroke="#e2e4e6" stroke-dasharray="5"
                                                                stroke-linecap="butt" class="apexcharts-gridline">
                                                            </line>
                                                            <line x1="0" y1="175.40879999999999"
                                                                x2="496.3218746185303" y2="175.40879999999999"
                                                                stroke="#e2e4e6" stroke-dasharray="5"
                                                                stroke-linecap="butt" class="apexcharts-gridline">
                                                            </line>
                                                            <line x1="0" y1="233.8784"
                                                                x2="496.3218746185303" y2="233.8784"
                                                                stroke="#e2e4e6" stroke-dasharray="5"
                                                                stroke-linecap="butt" class="apexcharts-gridline">
                                                            </line>
                                                        </g>
                                                        <g class="apexcharts-gridlines-vertical"></g>
                                                        <line x1="0" y1="292.348" x2="496.3218746185303"
                                                            y2="292.348" stroke="transparent" stroke-dasharray="0"
                                                            stroke-linecap="butt"></line>
                                                        <line x1="0" y1="1" x2="0"
                                                            y2="292.348" stroke="transparent" stroke-dasharray="0"
                                                            stroke-linecap="butt"></line>
                                                    </g>
                                                    <g class="apexcharts-grid-borders">
                                                        <line x1="0" y1="0" x2="496.3218746185303"
                                                            y2="0" stroke="#e2e4e6" stroke-dasharray="5"
                                                            stroke-linecap="butt" class="apexcharts-gridline">
                                                        </line>
                                                        <line x1="0" y1="292.348" x2="496.3218746185303"
                                                            y2="292.348" stroke="#e2e4e6" stroke-dasharray="5"
                                                            stroke-linecap="butt" class="apexcharts-gridline">
                                                        </line>
                                                        <line x1="0" y1="292.348" x2="496.3218746185303"
                                                            y2="292.348" stroke="#e0e0e0" stroke-dasharray="0"
                                                            stroke-width="1" stroke-linecap="butt"></line>
                                                    </g>
                                                    <g class="apexcharts-bar-series apexcharts-plot-series">
                                                        <g class="apexcharts-series" seriesName="Outstanding"
                                                            rel="1" data:realIndex="0">
                                                            <path
                                                                d="M 21.2709374836513 292.349 L 21.2709374836513 292.349 L 49.632187461853036 292.349 L 49.632187461853036 292.349 z"
                                                                fill="none" fill-opacity="1" stroke="#7539ff"
                                                                stroke-opacity="1" stroke-linecap="square"
                                                                stroke-width="0" stroke-dasharray="0"
                                                                class="apexcharts-bar-area " index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 21.2709374836513 292.349 L 21.2709374836513 292.349 L 49.632187461853036 292.349 L 49.632187461853036 292.349 z"
                                                                pathFrom="M 21.2709374836513 292.349 L 21.2709374836513 292.349 L 49.632187461853036 292.349 L 49.632187461853036 292.349 L 49.632187461853036 292.349 L 49.632187461853036 292.349 L 49.632187461853036 292.349 L 21.2709374836513 292.349 z"
                                                                cy="292.348" cx="92.17406242915563" j="0"
                                                                val="0" barHeight="0"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 92.17406242915563 292.349 L 92.17406242915563 268.1142 C 92.17406242915563 265.6142 94.67406242915563 263.1142 97.17406242915563 263.1142 L 115.53531240735737 263.1142 C 118.03531240735737 263.1142 120.53531240735737 265.6142 120.53531240735737 268.1142 L 120.53531240735737 292.349 z "
                                                                fill="rgba(117,57,255,1)" fill-opacity="1"
                                                                stroke="#7539ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-bar-area apexcharts-flip-y"
                                                                index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 92.17406242915563 292.349 L 92.17406242915563 268.1142 C 92.17406242915563 265.6142 94.67406242915563 263.1142 97.17406242915563 263.1142 L 115.53531240735737 263.1142 C 118.03531240735737 263.1142 120.53531240735737 265.6142 120.53531240735737 268.1142 L 120.53531240735737 292.349 z "
                                                                pathFrom="M 92.17406242915563 292.349 L 92.17406242915563 292.349 L 120.53531240735737 292.349 L 120.53531240735737 292.349 L 120.53531240735737 292.349 L 120.53531240735737 292.349 L 120.53531240735737 292.349 L 92.17406242915563 292.349 z"
                                                                cy="263.1132" cx="163.07718737465996" j="1"
                                                                val="10" barHeight="29.2348"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 163.07718737465996 292.349 L 163.07718737465996 209.6446 C 163.07718737465996 207.1446 165.57718737465996 204.6446 168.07718737465996 204.6446 L 186.43843735286168 204.6446 C 188.93843735286168 204.6446 191.43843735286168 207.1446 191.43843735286168 209.6446 L 191.43843735286168 292.349 z "
                                                                fill="rgba(117,57,255,1)" fill-opacity="1"
                                                                stroke="#7539ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-bar-area apexcharts-flip-y"
                                                                index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 163.07718737465996 292.349 L 163.07718737465996 209.6446 C 163.07718737465996 207.1446 165.57718737465996 204.6446 168.07718737465996 204.6446 L 186.43843735286168 204.6446 C 188.93843735286168 204.6446 191.43843735286168 207.1446 191.43843735286168 209.6446 L 191.43843735286168 292.349 z "
                                                                pathFrom="M 163.07718737465996 292.349 L 163.07718737465996 292.349 L 191.43843735286168 292.349 L 191.43843735286168 292.349 L 191.43843735286168 292.349 L 191.43843735286168 292.349 L 191.43843735286168 292.349 L 163.07718737465996 292.349 z"
                                                                cy="204.6436" cx="233.98031232016427" j="2"
                                                                val="30" barHeight="87.7044"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 233.98031232016427 292.349 L 233.98031232016427 151.175 C 233.98031232016427 148.675 236.48031232016427 146.175 238.98031232016427 146.175 L 257.341562298366 146.175 C 259.841562298366 146.175 262.341562298366 148.675 262.341562298366 151.175 L 262.341562298366 292.349 z "
                                                                fill="rgba(117,57,255,1)" fill-opacity="1"
                                                                stroke="#7539ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-bar-area apexcharts-flip-y"
                                                                index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 233.98031232016427 292.349 L 233.98031232016427 151.175 C 233.98031232016427 148.675 236.48031232016427 146.175 238.98031232016427 146.175 L 257.341562298366 146.175 C 259.841562298366 146.175 262.341562298366 148.675 262.341562298366 151.175 L 262.341562298366 292.349 z "
                                                                pathFrom="M 233.98031232016427 292.349 L 233.98031232016427 292.349 L 262.341562298366 292.349 L 262.341562298366 292.349 L 262.341562298366 292.349 L 262.341562298366 292.349 L 262.341562298366 292.349 L 233.98031232016427 292.349 z"
                                                                cy="146.174" cx="304.8834372656686" j="3"
                                                                val="50" barHeight="146.174"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 304.8834372656686 292.349 L 304.8834372656686 224.26200000000003 C 304.8834372656686 221.76200000000003 307.3834372656686 219.26200000000003 309.8834372656686 219.26200000000003 L 328.2446872438703 219.26200000000003 C 330.7446872438703 219.26200000000003 333.2446872438703 221.76200000000003 333.2446872438703 224.26200000000003 L 333.2446872438703 292.349 z "
                                                                fill="rgba(117,57,255,1)" fill-opacity="1"
                                                                stroke="#7539ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-bar-area apexcharts-flip-y"
                                                                index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 304.8834372656686 292.349 L 304.8834372656686 224.26200000000003 C 304.8834372656686 221.76200000000003 307.3834372656686 219.26200000000003 309.8834372656686 219.26200000000003 L 328.2446872438703 219.26200000000003 C 330.7446872438703 219.26200000000003 333.2446872438703 221.76200000000003 333.2446872438703 224.26200000000003 L 333.2446872438703 292.349 z "
                                                                pathFrom="M 304.8834372656686 292.349 L 304.8834372656686 292.349 L 333.2446872438703 292.349 L 333.2446872438703 292.349 L 333.2446872438703 292.349 L 333.2446872438703 292.349 L 333.2446872438703 292.349 L 304.8834372656686 292.349 z"
                                                                cy="219.26100000000002" cx="375.7865622111729" j="4"
                                                                val="25" barHeight="73.087"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 375.7865622111729 292.349 L 375.7865622111729 186.25676 C 375.7865622111729 183.75676 378.2865622111729 181.25676 380.7865622111729 181.25676 L 399.14781218937463 181.25676 C 401.64781218937463 181.25676 404.14781218937463 183.75676 404.14781218937463 186.25676 L 404.14781218937463 292.349 z "
                                                                fill="rgba(117,57,255,1)" fill-opacity="1"
                                                                stroke="#7539ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-bar-area apexcharts-flip-y"
                                                                index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 375.7865622111729 292.349 L 375.7865622111729 186.25676 C 375.7865622111729 183.75676 378.2865622111729 181.25676 380.7865622111729 181.25676 L 399.14781218937463 181.25676 C 401.64781218937463 181.25676 404.14781218937463 183.75676 404.14781218937463 186.25676 L 404.14781218937463 292.349 z "
                                                                pathFrom="M 375.7865622111729 292.349 L 375.7865622111729 292.349 L 404.14781218937463 292.349 L 404.14781218937463 292.349 L 404.14781218937463 292.349 L 404.14781218937463 292.349 L 404.14781218937463 292.349 L 375.7865622111729 292.349 z"
                                                                cy="181.25576" cx="446.6896871566772" j="5"
                                                                val="38" barHeight="111.09224"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 446.6896871566772 292.349 L 446.6896871566772 180.40980000000002 C 446.6896871566772 177.90980000000002 449.1896871566772 175.40980000000002 451.6896871566772 175.40980000000002 L 470.05093713487895 175.40980000000002 C 472.55093713487895 175.40980000000002 475.05093713487895 177.90980000000002 475.05093713487895 180.40980000000002 L 475.05093713487895 292.349 z "
                                                                fill="rgba(117,57,255,1)" fill-opacity="1"
                                                                stroke="#7539ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-bar-area apexcharts-flip-y"
                                                                index="0"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 446.6896871566772 292.349 L 446.6896871566772 180.40980000000002 C 446.6896871566772 177.90980000000002 449.1896871566772 175.40980000000002 451.6896871566772 175.40980000000002 L 470.05093713487895 175.40980000000002 C 472.55093713487895 175.40980000000002 475.05093713487895 177.90980000000002 475.05093713487895 180.40980000000002 L 475.05093713487895 292.349 z "
                                                                pathFrom="M 446.6896871566772 292.349 L 446.6896871566772 292.349 L 475.05093713487895 292.349 L 475.05093713487895 292.349 L 475.05093713487895 292.349 L 475.05093713487895 292.349 L 475.05093713487895 292.349 L 446.6896871566772 292.349 z"
                                                                cy="175.4088" cx="517.5928121021816" j="6"
                                                                val="40" barHeight="116.9392"
                                                                barWidth="28.361249978201734"></path>
                                                            <g class="apexcharts-bar-goals-markers">
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                            </g>
                                                        </g>
                                                        <g class="apexcharts-series" seriesName="Receivedx"
                                                            rel="2" data:realIndex="1">
                                                            <path
                                                                d="M 21.2709374836513 287.34999999999997 L 21.2709374836513 209.64559999999997 C 21.2709374836513 207.14559999999997 23.7709374836513 204.64559999999997 26.2709374836513 204.64559999999997 L 44.632187461853036 204.64559999999997 C 47.132187461853036 204.64559999999997 49.632187461853036 207.14559999999997 49.632187461853036 209.64559999999997 L 49.632187461853036 287.34999999999997 C 49.632187461853036 289.84999999999997 47.132187461853036 292.34999999999997 44.632187461853036 292.34999999999997 L 26.2709374836513 292.34999999999997 C 23.7709374836513 292.34999999999997 21.2709374836513 289.84999999999997 21.2709374836513 287.34999999999997 Z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 21.2709374836513 287.34999999999997 L 21.2709374836513 209.64559999999997 C 21.2709374836513 207.14559999999997 23.7709374836513 204.64559999999997 26.2709374836513 204.64559999999997 L 44.632187461853036 204.64559999999997 C 47.132187461853036 204.64559999999997 49.632187461853036 207.14559999999997 49.632187461853036 209.64559999999997 L 49.632187461853036 287.34999999999997 C 49.632187461853036 289.84999999999997 47.132187461853036 292.34999999999997 44.632187461853036 292.34999999999997 L 26.2709374836513 292.34999999999997 C 23.7709374836513 292.34999999999997 21.2709374836513 289.84999999999997 21.2709374836513 287.34999999999997 Z "
                                                                pathFrom="M 21.2709374836513 292.34999999999997 L 21.2709374836513 292.34999999999997 L 49.632187461853036 292.34999999999997 L 49.632187461853036 292.34999999999997 L 49.632187461853036 292.34999999999997 L 49.632187461853036 292.34999999999997 L 49.632187461853036 292.34999999999997 L 21.2709374836513 292.34999999999997 Z"
                                                                cy="204.64459999999997" cx="92.17406242915563" j="0"
                                                                val="30" barHeight="87.7044"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 92.17406242915563 263.11519999999996 L 92.17406242915563 180.41079999999997 C 92.17406242915563 177.91079999999997 94.67406242915563 175.41079999999997 97.17406242915563 175.41079999999997 L 115.53531240735737 175.41079999999997 C 118.03531240735737 175.41079999999997 120.53531240735737 177.91079999999997 120.53531240735737 180.41079999999997 L 120.53531240735737 263.11519999999996 z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 92.17406242915563 263.11519999999996 L 92.17406242915563 180.41079999999997 C 92.17406242915563 177.91079999999997 94.67406242915563 175.41079999999997 97.17406242915563 175.41079999999997 L 115.53531240735737 175.41079999999997 C 118.03531240735737 175.41079999999997 120.53531240735737 177.91079999999997 120.53531240735737 180.41079999999997 L 120.53531240735737 263.11519999999996 z "
                                                                pathFrom="M 92.17406242915563 263.11519999999996 L 92.17406242915563 263.11519999999996 L 120.53531240735737 263.11519999999996 L 120.53531240735737 263.11519999999996 L 120.53531240735737 263.11519999999996 L 120.53531240735737 263.11519999999996 L 120.53531240735737 263.11519999999996 L 92.17406242915563 263.11519999999996 z"
                                                                cy="175.40979999999996" cx="163.07718737465996" j="1"
                                                                val="30" barHeight="87.7044"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 163.07718737465996 204.6456 L 163.07718737465996 -24.2328 C 163.07718737465996 -26.7328 165.57718737465996 -29.2328 168.07718737465996 -29.2328 L 186.43843735286168 -29.2328 C 188.93843735286168 -29.2328 191.43843735286168 -26.7328 191.43843735286168 -24.2328 L 191.43843735286168 204.6456 z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 163.07718737465996 204.6456 L 163.07718737465996 -24.2328 C 163.07718737465996 -26.7328 165.57718737465996 -29.2328 168.07718737465996 -29.2328 L 186.43843735286168 -29.2328 C 188.93843735286168 -29.2328 191.43843735286168 -26.7328 191.43843735286168 -24.2328 L 191.43843735286168 204.6456 z "
                                                                pathFrom="M 163.07718737465996 204.6456 L 163.07718737465996 204.6456 L 191.43843735286168 204.6456 L 191.43843735286168 204.6456 L 191.43843735286168 204.6456 L 191.43843735286168 204.6456 L 191.43843735286168 204.6456 L 163.07718737465996 204.6456 z"
                                                                cy="-29.233800000000002" cx="233.98031232016427" j="2"
                                                                val="80" barHeight="233.8784"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 233.98031232016427 146.17600000000002 L 233.98031232016427 -53.46759999999998 C 233.98031232016427 -55.96759999999998 236.48031232016427 -58.46759999999998 238.98031232016427 -58.46759999999998 L 257.341562298366 -58.46759999999998 C 259.841562298366 -58.46759999999998 262.341562298366 -55.96759999999998 262.341562298366 -53.46759999999998 L 262.341562298366 146.17600000000002 z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 233.98031232016427 146.17600000000002 L 233.98031232016427 -53.46759999999998 C 233.98031232016427 -55.96759999999998 236.48031232016427 -58.46759999999998 238.98031232016427 -58.46759999999998 L 257.341562298366 -58.46759999999998 C 259.841562298366 -58.46759999999998 262.341562298366 -55.96759999999998 262.341562298366 -53.46759999999998 L 262.341562298366 146.17600000000002 z "
                                                                pathFrom="M 233.98031232016427 146.17600000000002 L 233.98031232016427 146.17600000000002 L 262.341562298366 146.17600000000002 L 262.341562298366 146.17600000000002 L 262.341562298366 146.17600000000002 L 262.341562298366 146.17600000000002 L 262.341562298366 146.17600000000002 L 233.98031232016427 146.17600000000002 z"
                                                                cy="-58.46859999999998" cx="304.8834372656686" j="3"
                                                                val="70" barHeight="204.6436"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 304.8834372656686 219.26300000000003 L 304.8834372656686 -9.61539999999997 C 304.8834372656686 -12.11539999999997 307.3834372656686 -14.61539999999997 309.8834372656686 -14.61539999999997 L 328.2446872438703 -14.61539999999997 C 330.7446872438703 -14.61539999999997 333.2446872438703 -12.11539999999997 333.2446872438703 -9.61539999999997 L 333.2446872438703 219.26300000000003 z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 304.8834372656686 219.26300000000003 L 304.8834372656686 -9.61539999999997 C 304.8834372656686 -12.11539999999997 307.3834372656686 -14.61539999999997 309.8834372656686 -14.61539999999997 L 328.2446872438703 -14.61539999999997 C 330.7446872438703 -14.61539999999997 333.2446872438703 -12.11539999999997 333.2446872438703 -9.61539999999997 L 333.2446872438703 219.26300000000003 z "
                                                                pathFrom="M 304.8834372656686 219.26300000000003 L 304.8834372656686 219.26300000000003 L 333.2446872438703 219.26300000000003 L 333.2446872438703 219.26300000000003 L 333.2446872438703 219.26300000000003 L 333.2446872438703 219.26300000000003 L 333.2446872438703 219.26300000000003 L 304.8834372656686 219.26300000000003 z"
                                                                cy="-14.61639999999997" cx="375.7865622111729" j="4"
                                                                val="80" barHeight="233.8784"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 375.7865622111729 181.25776000000002 L 375.7865622111729 -47.62063999999999 C 375.7865622111729 -50.12063999999999 378.2865622111729 -52.62063999999999 380.7865622111729 -52.62063999999999 L 399.14781218937463 -52.62063999999999 C 401.64781218937463 -52.62063999999999 404.14781218937463 -50.12063999999999 404.14781218937463 -47.62063999999999 L 404.14781218937463 181.25776000000002 z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 375.7865622111729 181.25776000000002 L 375.7865622111729 -47.62063999999999 C 375.7865622111729 -50.12063999999999 378.2865622111729 -52.62063999999999 380.7865622111729 -52.62063999999999 L 399.14781218937463 -52.62063999999999 C 401.64781218937463 -52.62063999999999 404.14781218937463 -50.12063999999999 404.14781218937463 -47.62063999999999 L 404.14781218937463 181.25776000000002 z "
                                                                pathFrom="M 375.7865622111729 181.25776000000002 L 375.7865622111729 181.25776000000002 L 404.14781218937463 181.25776000000002 L 404.14781218937463 181.25776000000002 L 404.14781218937463 181.25776000000002 L 404.14781218937463 181.25776000000002 L 404.14781218937463 181.25776000000002 L 375.7865622111729 181.25776000000002 z"
                                                                cy="-52.621639999999985" cx="446.6896871566772" j="5"
                                                                val="80" barHeight="233.8784"
                                                                barWidth="28.361249978201734"></path>
                                                            <path
                                                                d="M 446.6896871566772 175.41080000000002 L 446.6896871566772 -53.46759999999998 C 446.6896871566772 -55.96759999999998 449.1896871566772 -58.46759999999998 451.6896871566772 -58.46759999999998 L 470.05093713487895 -58.46759999999998 C 472.55093713487895 -58.46759999999998 475.05093713487895 -55.96759999999998 475.05093713487895 -53.46759999999998 L 475.05093713487895 175.41080000000002 z "
                                                                fill="rgba(248,245,255,1)" fill-opacity="1"
                                                                stroke="#f8f5ff" stroke-opacity="1"
                                                                stroke-linecap="square" stroke-width="0"
                                                                stroke-dasharray="0" class="apexcharts-bar-area "
                                                                index="1"
                                                                clip-path="url(#gridRectBarMaskk31pm21w)"
                                                                pathTo="M 446.6896871566772 175.41080000000002 L 446.6896871566772 -53.46759999999998 C 446.6896871566772 -55.96759999999998 449.1896871566772 -58.46759999999998 451.6896871566772 -58.46759999999998 L 470.05093713487895 -58.46759999999998 C 472.55093713487895 -58.46759999999998 475.05093713487895 -55.96759999999998 475.05093713487895 -53.46759999999998 L 475.05093713487895 175.41080000000002 z "
                                                                pathFrom="M 446.6896871566772 175.41080000000002 L 446.6896871566772 175.41080000000002 L 475.05093713487895 175.41080000000002 L 475.05093713487895 175.41080000000002 L 475.05093713487895 175.41080000000002 L 475.05093713487895 175.41080000000002 L 475.05093713487895 175.41080000000002 L 446.6896871566772 175.41080000000002 z"
                                                                cy="-58.46859999999998" cx="517.5928121021816" j="6"
                                                                val="80" barHeight="233.8784"
                                                                barWidth="28.361249978201734"></path>
                                                            <g class="apexcharts-bar-goals-markers">
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                                <g className="apexcharts-bar-goals-groups"
                                                                    class="apexcharts-hidden-element-shown"
                                                                    clip-path="url(#gridRectMarkerMaskk31pm21w)">
                                                                </g>
                                                            </g>
                                                        </g>
                                                        <g class="apexcharts-datalabels" data:realIndex="0"></g>
                                                        <g class="apexcharts-datalabels" data:realIndex="1"></g>
                                                    </g>
                                                    <line x1="0" y1="0" x2="496.3218746185303"
                                                        y2="0" stroke="#b6b6b6" stroke-dasharray="0"
                                                        stroke-width="1" stroke-linecap="butt"
                                                        class="apexcharts-ycrosshairs"></line>
                                                    <line x1="0" y1="0" x2="496.3218746185303"
                                                        y2="0" stroke="#b6b6b6" stroke-dasharray="0"
                                                        stroke-width="0" stroke-linecap="butt"
                                                        class="apexcharts-ycrosshairs-hidden"></line>
                                                    <g class="apexcharts-xaxis" transform="translate(0, 0)">
                                                        <g class="apexcharts-xaxis-texts-g"
                                                            transform="translate(0, -4)">
                                                            <text x="35.451562472752165" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Mon</tspan>
                                                                <title>Mon</title>
                                                            </text><text x="106.3546874182565" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Tue</tspan>
                                                                <title>Tue</title>
                                                            </text><text x="177.25781236376085" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Wed</tspan>
                                                                <title>Wed</title>
                                                            </text><text x="248.16093730926517" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Thu</tspan>
                                                                <title>Thu</title>
                                                            </text><text x="319.06406225476945" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Fri</tspan>
                                                                <title>Fri</title>
                                                            </text><text x="389.96718720027377" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Sat</tspan>
                                                                <title>Sat</title>
                                                            </text><text x="460.8703121457781" y="320.348"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="12px"
                                                                font-family="Helvetica, Arial, sans-serif"
                                                                font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-xaxis-label "
                                                                style="font-family: Helvetica, Arial, sans-serif;">
                                                                <tspan>Sun</tspan>
                                                                <title>Sun</title>
                                                            </text>
                                                        </g>
                                                    </g>
                                                    <g class="apexcharts-yaxis-annotations"></g>
                                                    <g class="apexcharts-xaxis-annotations"></g>
                                                    <g class="apexcharts-point-annotations"></g>
                                                </g>
                                            </svg>
                                            <div class="apexcharts-legend" style="max-height: 180px;"></div>
                                            <div class="apexcharts-tooltip apexcharts-theme-light"
                                                style="left: 247.277px; top: -62.7px;">
                                                <div class="apexcharts-tooltip-title"
                                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                    Thu
                                                </div>
                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-0"
                                                    style="order: 1; display: none;"><span
                                                        class="apexcharts-tooltip-marker" shape="circle"
                                                        style="color: rgb(248, 245, 255);"></span>
                                                    <div class="apexcharts-tooltip-text"
                                                        style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                class="apexcharts-tooltip-text-y-label">Received :
                                                            </span><span
                                                                class="apexcharts-tooltip-text-y-value">70</span>
                                                        </div>
                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                        </div>
                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-1 apexcharts-active"
                                                    style="order: 2; display: flex;"><span
                                                        class="apexcharts-tooltip-marker" shape="circle"
                                                        style="color: rgb(248, 245, 255);"></span>
                                                    <div class="apexcharts-tooltip-text"
                                                        style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                        <div class="apexcharts-tooltip-y-group"><span
                                                                class="apexcharts-tooltip-text-y-label">Received :
                                                            </span><span
                                                                class="apexcharts-tooltip-text-y-value">70</span>
                                                        </div>
                                                        <div class="apexcharts-tooltip-goals-group"><span
                                                                class="apexcharts-tooltip-text-goals-label"></span><span
                                                                class="apexcharts-tooltip-text-goals-value"></span>
                                                        </div>
                                                        <div class="apexcharts-tooltip-z-group"><span
                                                                class="apexcharts-tooltip-text-z-label"></span><span
                                                                class="apexcharts-tooltip-text-z-value"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="apexcharts-yaxistooltip apexcharts-yaxistooltip-0 apexcharts-yaxistooltip-left apexcharts-theme-light">
                                                <div class="apexcharts-yaxistooltip-text"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end card body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->


                        <div class="col-xl-6 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="mb-1">Customers</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-nowrap table-borderless custom-table">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="customer-details.html"
                                                                class="avatar avatar-lg rounded-circle me-2 flex-shrink-0">
                                                                <img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}"
                                                                    class="rounded-circle" alt="img">
                                                            </a>
                                                            <div>
                                                                <h6 class="fs-14 fw-medium mb-1"><a
                                                                        href="customer-details.html">Emily
                                                                        Clark</a>
                                                                </h6>
                                                                <p class="fs-13">No of Invoices : 45</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="mb-1">Outstanding </p>
                                                        <h6 class="fs-14 fw-semibold">$10000</h6>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="customer-details.html"
                                                                class="avatar avatar-lg rounded-circle me-2 flex-shrink-0">
                                                                <img src="{{ asset('assets/img/profiles/avatar-03.jpg') }}"
                                                                    class="rounded-circle" alt="img">
                                                            </a>
                                                            <div>
                                                                <h6 class="fs-14 fw-medium mb-1"><a
                                                                        href="customer-details.html">John Smith</a>
                                                                </h6>
                                                                <p class="fs-13">No of Invoices : 16</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="mb-1">Outstanding </p>
                                                        <h6 class="fs-14 fw-semibold">$5426</h6>
                                                    </td>
                                                    <td>
                                                        <div
                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                            <a href="add-invoice.html"
                                                                class="btn btn-icon btn-sm btn-light"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-title="New Invoice"><i
                                                                    class="isax isax-add-circle"></i></a>
                                                            <div data-bs-toggle="tooltip" data-bs-title="Add Ledger">
                                                                <a href="#"
                                                                    class="btn btn-icon btn-sm btn-light"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#add_ledger"><i
                                                                        class="isax isax-document-text-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="customer-details.html"
                                                                class="avatar avatar-lg rounded-circle me-2 flex-shrink-0">
                                                                <img src="{{ asset('assets/img/profiles/avatar-05.jpg') }}"
                                                                    class="rounded-circle" alt="img">
                                                            </a>
                                                            <div>
                                                                <h6 class="fs-14 fw-medium mb-1"><a
                                                                        href="customer-details.html">Olivia
                                                                        Harris</a>
                                                                </h6>
                                                                <p class="fs-13">No of Invoices : 23</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="mb-1">Outstanding </p>
                                                        <h6 class="fs-14 fw-semibold">$1493</h6>
                                                    </td>
                                                    <td>
                                                        <div
                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                            <a href="add-invoice.html"
                                                                class="btn btn-icon btn-sm btn-light"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-title="New Invoice"><i
                                                                    class="isax isax-add-circle"></i></a>
                                                            <div data-bs-toggle="tooltip" data-bs-title="Add Ledger">
                                                                <a href="#"
                                                                    class="btn btn-icon btn-sm btn-light"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#add_ledger"><i
                                                                        class="isax isax-document-text-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="customer-details.html"
                                                                class="avatar avatar-lg rounded-circle me-2 flex-shrink-0">
                                                                <img src="{{ asset('assets/img/profiles/avatar-07.jpg') }}"
                                                                    class="rounded-circle" alt="img">
                                                            </a>
                                                            <div>
                                                                <h6 class="fs-14 fw-medium mb-1"><a
                                                                        href="customer-details.html">William
                                                                        Parker</a>
                                                                </h6>
                                                                <p class="fs-13">No of Invoices : 58</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="mb-1">Outstanding </p>
                                                        <h6 class="fs-14 fw-semibold">$7854</h6>
                                                    </td>
                                                    <td>
                                                        <div
                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                            <a href="add-invoice.html"
                                                                class="btn btn-icon btn-sm btn-light"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-title="New Invoice"><i
                                                                    class="isax isax-add-circle"></i></a>
                                                            <div data-bs-toggle="tooltip" data-bs-title="Add Ledger">
                                                                <a href="#"
                                                                    class="btn btn-icon btn-sm btn-light"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#add_ledger"><i
                                                                        class="isax isax-document-text-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="customer-details.html"
                                                                class="avatar avatar-lg rounded-circle me-2 flex-shrink-0">
                                                                <img src="{{ asset('assets/img/profiles/avatar-08.jpg') }}"
                                                                    class="rounded-circle" alt="img">
                                                            </a>
                                                            <div>
                                                                <h6 class="fs-14 fw-medium mb-1"><a
                                                                        href="customer-details.html">Charlotte
                                                                        Brown</a>
                                                                </h6>
                                                                <p class="fs-13">No of Invoices : 09</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="mb-1">Outstanding </p>
                                                        <h6 class="fs-14 fw-semibold">$4989</h6>
                                                    </td>
                                                    <td>
                                                        <div
                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                            <a href="add-invoice.html"
                                                                class="btn btn-icon btn-sm btn-light"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-title="New Invoice"><i
                                                                    class="isax isax-add-circle"></i></a>
                                                            <div data-bs-toggle="tooltip" data-bs-title="Add Ledger">
                                                                <a href="#"
                                                                    class="btn btn-icon btn-sm btn-light"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#add_ledger"><i
                                                                        class="isax isax-document-text-1"></i></a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="customers.html"
                                        class="btn btn-light btn-lg w-100 text-decoration-underline mt-3">All
                                        Customers</a>
                                </div> <!-- end card body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->
                    </div>
                @endif




            </div>
        </div>
    </div>
</div>
