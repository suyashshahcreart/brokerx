@extends('admin.layouts.app')
@section('content')
<div class="container">
    <h1>Add QR Code</h1>
    <form action="{{ route('admin.qr.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Booking</label>
            <select name="booking_id" class="form-control">
                @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}">{{ $booking->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="mb-3">
            <label>QR Link</label>
            <input type="text" name="qr_link" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>
</div>
@endsection
