@extends('admin.layouts.vertical', ['title' => 'Dashboard demo','subTitle' => 'Admin Dashboard demo'])

@section('content')
    <div class="row">
        <!-- propert count caard -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:buildings-2-broken"
                                class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">No. of Properties</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">{{ number_format($totalProperties) }}</h3>
                        </div> <!-- end col -->
                        <div class="col-6">
                            <div id="total_property_card" class="apex-charts" ></div>
                        </div> <!-- end col -->
                    </div> <!-- end row-->
                </div> <!-- end card body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
        <!-- live tour count card -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:users-group-two-rounded-broken"
                                              class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Live Tours</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">{{ number_format($liveTours) }}</h3>
                        </div> <!-- end col -->
                        <div class="col-6 text-end">
                            <div id="live_tours_card" class="apex-charts"></div>
                        </div> <!-- end col -->
                    </div> <!-- end row-->
                </div> <!-- end card body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
        <!-- total customer card -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-5">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:shield-user-broken"
                                              class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Customers</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">{{ number_format($totalCustomers) }}</h3>
                        </div> <!-- end col -->
                        <div class="col-6 text-end">
                            <div id="total_customer_card" class="apex-charts"></div>
                        </div> <!-- end col -->
                    </div> <!-- end row-->
                </div> <!-- end card body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
        <!-- total revenue -->
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-5">
                            <div class="avatar-md bg-light bg-opacity-50 rounded">
                                <iconify-icon icon="solar:money-bag-broken"
                                              class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                            <p class="text-muted mb-2 mt-3">Revenue</p>
                            <h3 class="text-dark fw-bold d-flex align-items-center gap-2 mb-0">₹{{ number_format($totalRevenue / 100, 2) }}</h3>
                        </div> <!-- end col -->
                        <div class="col-6 text-end">
                            <div id="total_revenue_card" class="apex-charts"></div>
                        </div> <!-- end col -->
                    </div> <!-- end row-->
                </div> <!-- end card body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
    <!-- Sales Analytic -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center pb-1">
                    <div>
                        <h4 class="card-title">Sales Analytic</h4>
                    </div>
                        <div class="dropdown" id="sales-analytic-dropdown">
                            <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" aria-expanded="false">
                                Period
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item" data-type="week">This Week</a>
                                <a href="#" class="dropdown-item" data-type="month">This Month</a>
                                <a href="#" class="dropdown-item" data-type="year">This Year</a>
                            </div>
                        </div>
                </div>
                <div class="card-body">
                    <div class="text-end">
                        <p class="mb-0 fs-18 fw-medium text-dark"><i class="ri-wallet-3-fill"></i> Earnings : <span
                                class="text-primary fw-bold">₹{{ number_format($monthlyEarning / 100, 2) }}</span></p>
                    </div>
                    <div class="row align-items-top text-center">
                        <div class="col-lg-12">
                                <div class="apex-charts mt-2" id="sales_analytic" data-api-url="{{ route('admin.dashboard.chart-data') }}"></div>
                        </div>

                    </div>
                </div>
                <div class="card-footer p-2 bg-light-subtle text-center">
                    <div class="row g-3">
                        <div class="col-md-6 border-end">
                            <p class="text-muted mb-1">Bookings</p>
                            <p class="text-dark fs-18 fw-medium d-flex align-items-center justify-content-center gap-2 mb-0">
                                {{ array_sum($monthlyBookings) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Customers</p>
                            <p class="text-dark fs-18 fw-medium d-flex align-items-center justify-content-center gap-2 mb-0">
                                {{ array_sum($monthlyCustomers) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- my balance  -->
        <div class="d-none col-xl-4">
            <div class="card bg-primary bg-gradient">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-xl-7 col-lg-6 col-md-6">
                            <h3 class="text-white fw-bold"></h3>
                            <p class="text-white-50">My Balance</p>
                            <div class="row mt-4">
                                <div class="col-lg-6 col-lg-6 col-md-6 col-6">
                                    <div class="d-flex gap-2">
                                        <div class="avatar-sm flex-shrink-0">
                                                                      <span
                                                                          class="avatar-title bg-success bg-opacity-50 text-white rounded">
                                                                           <i class="ri-arrow-down-line fs-4"></i>
                                                                      </span>
                                        </div>
                                        <div class="d-block">
                                            <h5 class="text-white fw-medium mb-0">$13,321.12</h5>
                                            <p class="mb-0 text-white-50">Income</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-lg-6 col-md-6 col-6">
                                    <div class="d-flex gap-2">
                                        <div class="avatar-sm flex-shrink-0">
                                                                      <span
                                                                          class="avatar-title bg-danger bg-opacity-50 text-white rounded">
                                                                           <i class="ri-arrow-up-line fs-4"></i>
                                                                      </span>
                                        </div>
                                        <div class="d-block">
                                            <h5 class="text-white fw-medium mb-0">$7,566.11</h5>
                                            <p class="mb-0 text-white-50">Expanse</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 g-2">
                                <div class="col-xl-6 col-lg-6 col-md-6">
                                    <a href="#!" class="btn btn-warning w-100 btn-sm">Send</a>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6">
                                    <a href="#!" class="btn bg-light bg-opacity-25 text-white w-100 btn-sm">Receive</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5 col-lg-4 col-md-4">
                            <img src="/images/money.png" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div class="row g-3">
                        <div class="col-xl-6 col-md-6">
                            <div class="text-center p-3 border-end">
                                <h5 class="card-title mb-0 text-dark fw-medium">Property</h5>
                                <div class="avatar-md bg-light bg-opacity-50 rounded mx-auto my-3">
                                    <iconify-icon icon="solar:home-bold-duotone"
                                                  class="fs-32 text-primary avatar-title"></iconify-icon>
                                </div>
                                <h4 class="text-dark fw-medium">15,780</h4>

                                <p class="text-muted">60% Target</p>

                                <div class="progress mt-3" style="height: 10px;">
                                    <div
                                        class="progress-bar progress-bar  progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar" style="width: 60%" aria-valuenow="70" aria-valuemin="0"
                                        aria-valuemax="70"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6">
                            <div class="text-center p-3">
                                <h5 class="card-title mb-0 text-dark fw-medium">Revenue</h5>
                                <div class="avatar-md bg-light bg-opacity-50 rounded mx-auto my-3">
                                    <iconify-icon icon="solar:money-bag-bold-duotone"
                                                  class="fs-32 text-success avatar-title"></iconify-icon>
                                </div>
                                <h4 class="text-dark fw-medium">$78.3M</h4>

                                <p class="text-muted">80% Target</p>

                                <div class="progress mt-3" style="height: 10px;">
                                    <div
                                        class="progress-bar progress-bar  progress-bar-striped progress-bar-animated bg-success"
                                        role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0"
                                        aria-valuemax="80">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top mt-1">
                    <a href="#!" class="link-dark fw-medium">View More <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>

        </div>
        <!-- property sales -->
         <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tours Live</h4>
                </div>
                <div class="card-body">
                    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="/images/properties/p-9.jpg" class="d-block w-100 rounded" alt="img-6">
                            </div>
                            <div class="carousel-item">
                                <img src="/images/properties/p-7.jpg" class="d-block w-100 rounded" alt="img-7">
                            </div>
                            <div class="carousel-item">
                                <img src="/images/properties/p-8.jpg" class="d-block w-100 rounded" alt="img-5">
                            </div>
                            <div class="carousel-item">
                                <img src="/images/properties/p-6.jpg" class="d-block w-100 rounded" alt="img-">
                            </div>
                            <div class="carousel-item">
                                <img src="/images/properties/p-10.jpg" class="d-block w-100 rounded" alt="img-5">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                                data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                                data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <div id="tour_live_card" class="apex-charts mt-4"></div>
                </div>
                <div class="card-footer border-top d-flex align-items-center justify-content-between">
                    <p class="text-muted fw-medium fs-15 mb-0"><span
                            class="text-dark me-1">Total Property Seals : </span>{{ number_format($liveTours) }}</p>
                    <div>
                        <a href="#!" class="btn btn-primary btn-sm">View More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center pb-1">
                    <div>
                        <h4 class="card-title mb-1">Social Source</h4>
                        <p class="fs-13 mb-0">Total Traffic In This Week</p>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            This Month
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a href="#!" class="dropdown-item">Week</a>
                            <!-- item-->
                            <a href="#!" class="dropdown-item">Months</a>
                            <!-- item-->
                            <a href="#!" class="dropdown-item">Years</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="own-property" class="apex-charts"></div>
                    <p class="mb-0 fs-18 fw-medium text-dark"><i class="ri-group-fill"></i> Buyers : <span
                            class="text-primary fw-bold">70</span></p>
                </div>
                <div class="card-footer border-top d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">See More Statistic</h5>
                    <div>
                        <a href="#!" class="btn btn-primary btn-sm">See Details</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center pb-1">
                    <div>
                        <h4 class="card-title">Most Sales Location</h4>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Asia
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">U.S.A</a>
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">Russia</a>
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">China</a>
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">Canada</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12">
                            <div id="most-sales-location" class="mt-3" style="height: 322px">
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-5 overflow-visible" style="height: 25px;">
                        <div class="progress-bar bg-primary  position-relative overflow-visible rounded-start"
                             role="progressbar" style="width: 20%" aria-valuenow="15" aria-valuemin="0"
                             aria-valuemax="100">
                            <p class="progress-value text-start text-dark mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -50px !important;">Canada</p>
                            <p class="progress-value text-start text-light mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -30px !important;">|</p>
                            <p class="mb-0  text-start ps-1 ps-lg-2 text-white fs-14">71.1%</p>
                        </div>
                        <div class="progress-bar bg-primary bg-opacity-75 position-relative overflow-visible"
                             role="progressbar" style="width: 20%" aria-valuenow="30" aria-valuemin="0"
                             aria-valuemax="100">
                            <p class="progress-value text-start text-dark mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -50px !important;">USA </p>
                            <p class="progress-value text-start text-light mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -30px !important;">| </p>
                            <p class="mb-0  text-start ps-1 ps-lg-2 text-white fs-14">67.0%</p>
                        </div>
                        <div class="progress-bar bg-primary bg-opacity-50 position-relative overflow-visible"
                             role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0"
                             aria-valuemax="100">
                            <p class="progress-value text-start text-dark mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -50px !important;">Brazil</p>
                            <p class="progress-value text-start text-light mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -30px !important;">|</p>
                            <p class="mb-0  text-start ps-1 ps-lg-2 text-white fs-14">53.9%</p>
                        </div>
                        <div class="progress-bar bg-primary bg-opacity-25 position-relative overflow-visible"
                             role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0"
                             aria-valuemax="100">
                            <p class="progress-value text-start text-dark mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -50px !important;">Russia</p>
                            <p class="progress-value text-start text-light mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -30px !important;">| </p>

                            <p class="mb-0  text-start ps-1 ps-lg-2 text-white fs-14">49.2%</p>
                        </div>
                        <div
                            class="progress-bar bg-primary bg-opacity-10 position-relative overflow-visible rounded-end"
                            role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0"
                            aria-valuemax="100">
                            <p class="progress-value text-start text-dark mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -50px !important;">China </p>
                            <p class="progress-value text-start text-light mb-0 mt-1 fs-14 fw-medium"
                               style="left: 0% !important; top: -30px !important;">| </p>

                            <p class="mb-0  text-start ps-1 ps-lg-2 text-white fs-14">38.8%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title">Latest Transaction</h4>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light rounded"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            This Month
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a href="#!" class="dropdown-item">Week</a>
                            <!-- item-->
                            <a href="#!" class="dropdown-item">Months</a>
                            <!-- item-->
                            <a href="#!" class="dropdown-item">Years</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle text-nowrap table-hover table-centered mb-0">
                            <thead class="bg-light-subtle">
                            <tr>
                                <th style="width: 20px;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customCheck1">
                                        <label class="form-check-label" for="customCheck1"></label>
                                    </div>
                                </th>
                                <th>Purchase ID</th>
                                <th>Buyer Name</th>
                                <th>Invoice</th>
                                <th>Purchase Date</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($latestTransactions as $index => $transaction)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customCheck{{ $index + 2 }}">
                                        <label class="form-check-label" for="customCheck{{ $index + 2 }}">&nbsp;</label>
                                    </div>
                                </td>
                                <td><a href="javascript: void(0);" class="text-dark fw-medium">#{{ $transaction->id }}</a></td>
                                <td>{{ $transaction->user->firstname ?? 'N/A' }} {{ $transaction->user->lastname ?? '' }}</td>
                                <td>{{ $transaction->booking_id ? 'BK-' . $transaction->booking_id : 'N/A' }}</td>
                                <td>{{ $transaction->created_at->format('d M, Y') }}</td>
                                <td>₹{{ number_format($transaction->amount / 100, 2) }}</td>
                                <td>{{ ucfirst($transaction->payment_method ?? $transaction->gateway) }}</td>
                                <td>
                                    @if($transaction->status == 'success' || $transaction->status == 'completed')
                                        <span class="badge bg-success-subtle text-success py-1 px-2 fs-12">Completed</span>
                                    @elseif($transaction->status == 'pending')
                                        <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-12">Pending</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.bookings.show' ,$transaction->booking->id ) }}" class="btn btn-light btn-sm">
                                            <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">No transactions found for this month</td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- end table-responsive -->
                </div>
            </div>
        </div>

    </div>
@endsection

@section('script')
    <script>
        window.weeklyLabels = @json($weeklyLabels);
        window.weeklyProperties = @json($weeklyProperties);
        window.weeklyCustomers = @json($weeklyCustomers);
        window.weeklyTours = @json($weeklyTours);
        window.weeklyRevenue = @json($weeklyRevenue);
        window.monthlyBookings = @json($monthlyBookings);
        window.monthlyCustomers = @json($monthlyCustomers);
        window.daysInMonth = {{ $daysInMonth }};
    </script>
    @vite(['resources/js/pages/dashboard-analytics.js'])
@endsection
