@extends('admin.layouts.app')
@section('content')
<div class="container">
    <h1>QR Code Details</h1>
    <div class="mb-3">
        <strong>Name:</strong> {{ $qr->name }}
    </div>
    <div class="mb-3">
        <strong>Code:</strong> {{ $qr->code }}
    </div>
    <div class="mb-3">
        <strong>Booking:</strong> {{ $qr->booking_id }}
    </div>
    <div class="mb-3">
        <strong>Image:</strong>
        @if($qr->image)
            <img src="/storage/{{ $qr->image }}" width="100"/>
        @else
            N/A
        @endif
    </div>
    <a href="{{ route('admin.qr.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
