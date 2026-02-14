<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul class="sidebar-vertical">

                <!-- MAIN SECTION -->
                <li class="menu-title"><span>Main</span></li>
                <li>
                    <a href="{{ route('home') }}"><i class="fe fe-home"></i> <span>Dashboard</span></a>
                </li>

                @auth
                    @if (auth()->user()->hasPermission('locations_read'))
                        <li>
                            <a href="{{ route('location.accounts.index') }}"><i class="fe fe-clipboard"></i> <span>Manage Locations</span></a>
                        </li>
                    @endif
                @endauth

                @if (Auth::user() && Auth::user()->active_business_key != 0)

                    <!-- QUICK ACTIONS SECTION -->
                    <li class="menu-title"><span>Quick Actions</span></li>
                    <li>
                        <a href="customers.html"><i class="fe fe-shopping-cart"></i> <span>POS Sales</span></a>
                    </li>
                    <li>
                        <a href="customers.html"><i class="fe fe-clipboard"></i> <span>Manage Invoices</span></a>
                    </li>
                    <li>
                        <a href="expenses.html"><i class="fe fe-file-plus"></i> <span>Expense Tracking</span></a>
                    </li>
                    <li>
                        <a href="credit-notes.html"><i class="fe fe-edit"></i> <span>Credit Notes</span></a>
                    </li>
                    <li>
                        <a href="customers.html"><i class="fe fe-users"></i> <span>Customers</span></a>
                    </li>

                    <!-- INVENTORY SECTION -->
                    <li class="menu-title"><span>Inventory</span></li>
                    <li class="submenu">
                        <a href=""><i class="fe fe-package"></i> <span>Products / Services</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="{{ Route('products.index') }}">Product List</a></li>
                            <li><a href="{{ Route('products.category.index') }}">Category</a></li>
                            <li><a href="{{ route('products.unit.index') }}">Units</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="purchases.html"><i class="fe fe-shopping-cart"></i> <span>Purchases</span></a>
                    </li>
                    <li>
                        <a href="inventory.html"><i class="fe fe-book"></i> <span>Vendors</span></a>
                    </li>

                    <!-- ACCOUNT & REPORTS SECTION -->
                    <li class="menu-title"><span>Account / Reports</span></li>
                    <li>
                        <a href="payment-summary.html"><i class="fe fe-credit-card"></i> <span>Payment Summary</span></a>
                    </li>
                    <li>
                        <a href="payment-summary.html"><i class="fe fe-credit-card"></i> <span>Payment Payroll</span></a>
                    </li>
                    <li class="submenu">
                        <a href="#"><i class="fe fe-box"></i> <span>Reports</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a href="expense-report.html">Expense Report</a></li>
                            <li><a href="purchase-report.html">Purchase Report</a></li>
                            <li><a href="purchase-return.html">Purchase Return Report</a></li>
                            <li><a href="sales-report.html">Sales Report</a></li>
                            <li><a href="sales-return-report.html">Sales Return Report</a></li>
                            <li><a href="quotation-report.html">Quotation Report</a></li>
                            <li><a href="payment-report.html">Payment Report</a></li>
                            <li><a href="stock-report.html">Stock Report</a></li>
                            <li><a href="low-stock-report.html">Low Stock Report</a></li>
                            <li><a href="income-report.html">Income Report</a></li>
                            <li><a href="tax-purchase.html">Tax Report</a></li>
                            <li><a href="profit-loss-list.html">Profit & Loss</a></li>
                        </ul>
                    </li>

                    <!-- USER MANAGEMENT SECTION -->
                    @if (auth()->user()->hasPermission('users_read'))
                        <li class="menu-title"><span>User Management</span></li>
                        <li>
                            <a href="{{ url('employee') }}"><i class="fe fe-user"></i> <span>Manage Users</span></a>
                        </li>
                    @endif

                    <!-- MEMBERSHIP SECTION -->
                    @if (auth()->user()->hasPermission('subscriptions_read'))
                        <li class="menu-title"><span>Membership</span></li>
                        <li class="submenu">
                            <a href="#"><i class="fe fe-book"></i> <span>Subscription</span> <span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="membership-plans.html">Membership Plans</a></li>
                                <li><a href="membership-addons.html">Membership Addons</a></li>
                                <li><a href="subscribers.html">Subscribers</a></li>
                                <li><a href="transactions.html">Transactions</a></li>
                            </ul>
                        </li>
                    @endif

                    <!-- SETTINGS SECTION -->
                    <li class="menu-title"><span>Settings</span></li>
                    <li>
                        <a href="settings.html"><i class="fe fe-settings"></i> <span>Settings</span></a>
                    </li>

                @endif

                <!-- LOGOUT SECTION -->
                <li>
                    <a href="login.html"><i class="fe fe-power"></i> <span>Logout</span></a>
                </li>

            </ul>
        </div>
    </div>
</div>
