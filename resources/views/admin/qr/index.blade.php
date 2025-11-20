@extends('admin.layouts.vertical',['title' => 'QR Code', 'subTitle' => 'System'])

@section('content')
<div class="container">
    <h1>QR Codes</h1>
    <a href="{{ route('admin.qr.create') }}" class="btn btn-primary mb-3">Add QR Code</a>
    <table class="table table-bordered" id="qr-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Booking</th>
                <th>Image</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @isset($qrs)
            @foreach($qrs as $qr)
                <tr>
                    <td>{{ $qr->id }}</td>
                    <td>{{ $qr->name }}</td>
                    <td>{{ $qr->code }}</td>
                    <td>{{ $qr->booking_id }}</td>
                    <td>@if($qr->image)<img src="/storage/{{ $qr->image }}" width="50"/>@endif</td>
                    <td>{{ $qr->creator ? $qr->creator->firstname.' '.$qr->creator->lastname : '' }}</td>
                    <td>
                        <a href="{{ route('admin.qr.show', $qr->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('admin.qr.edit', $qr->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('admin.qr.destroy', $qr->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this QR code?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        @endisset
        </tbody>
    </table>
</div>
@endsection
@push('scripts')
<script>
$(function() {
    $('#qr-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.qr.index') }}',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'code' },
            { data: 'booking_id' },
            { data: 'image', render: function(data) { return data ? `<img src="/storage/${data}" width="50"/>` : ''; } },
            { data: 'created_by' },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
