import $ from 'jquery';
import 'datatables.net-bs5';

$(function() {
    const table = $('#qr-table');
    if (!table.length) return;
    table.DataTable({
        processing: true,
        serverSide: true,
        ajax: table.data('ajax') || table.data('url') || table.attr('data-ajax') || table.attr('data-url') || window.qrIndexAjaxUrl || '',
        columns: [
            { data: 'id', name: 'id', className: 'fw-semibold' },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'booking_id', name: 'booking_id' },
            { data: 'image', name: 'image', orderable: false, searchable: false, render: function(data) { return data ? `<img src="/storage/${data}" width="50"/>` : ''; } },
            { data: 'created_by', name: 'created_by' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[0, 'desc']],
        responsive: true,
        language: {
            search: "Search QR Codes:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            zeroRecords: "No matching QR codes found"
        }
    });
});
