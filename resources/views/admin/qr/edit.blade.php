@extends('admin.layouts.app')
@section('content')
<div class="container">
    <h1>Edit QR Code</h1>
    <form action="{{ route('admin.qr.update', $qr->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $qr->name }}" required>
        </div>
        <div class="mb-3">
            <label>Booking</label>
            <select name="booking_id" class="form-control">
                @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}" @if($qr->booking_id == $booking->id) selected @endif>{{ $booking->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
            @if($qr->image)
                <img src="/storage/{{ $qr->image }}" width="100"/>
            @endif
        </div>
        <div class="mb-3">
            <label>QR Link</label>
            <input type="text" name="qr_link" class="form-control" value="{{ $qr->qr_link }}">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
