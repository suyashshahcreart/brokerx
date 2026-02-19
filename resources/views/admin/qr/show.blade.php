@extends('admin.layouts.vertical', ['title' => 'QR Code Details', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.qr.index') }}">QR Codes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
                <h3 class="mb-0">QR Code Details</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.qr.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">QR Code Information</h4>
                    <p class="text-muted mb-0">View all details for this QR code</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $qr->name }}</dd>

                    <dt class="col-sm-3">Code</dt>
                    <dd class="col-sm-9">{{ $qr->code }}</dd>

                    <dt class="col-sm-3">Booking</dt>
                    <dd class="col-sm-9">{{ $qr->booking_id }}</dd>

                    <dt class="col-sm-3">Image</dt>
                    <dd class="col-sm-9">
                        @if($qr->image)
                            <img src="/storage/{{ $qr->image }}" width="100"/>
                        @else
                            N/A
                        @endif
                    </dd>

                    <dt class="col-sm-3">QR Link</dt>
                    <dd class="col-sm-9">{{ $qr->qr_link }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
