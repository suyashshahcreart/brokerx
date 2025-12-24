<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>QR Analytics - Proppik</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 24px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #667eea;
            margin-bottom: 8px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 8px;
        }
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        .table-container {
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #667eea;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 16px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 QR Analytics Dashboard</h1>
            <p>Track and manage your QR codes</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>{{ $qrs->total() }}</h3>
                <p>Total QR Codes</p>
            </div>
            <div class="stat-card">
                <h3>{{ $qrs->where('booking_id', '!=', null)->count() }}</h3>
                <p>Active QR Codes</p>
            </div>
            <div class="stat-card">
                <h3>{{ $qrs->where('booking_id', null)->count() }}</h3>
                <p>Inactive QR Codes</p>
            </div>
        </div>

        <div class="table-container">
            <div class="filters">
                <a href="{{ route('qr.analytics', ['filter' => 'all']) }}" 
                   class="filter-btn {{ $filter == 'all' ? 'active' : '' }}">All</a>
                <a href="{{ route('qr.analytics', ['filter' => 'active']) }}" 
                   class="filter-btn {{ $filter == 'active' ? 'active' : '' }}">Active</a>
                <a href="{{ route('qr.analytics', ['filter' => 'inactive']) }}" 
                   class="filter-btn {{ $filter == 'inactive' ? 'active' : '' }}">Inactive</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Booking</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qrs as $qr)
                    <tr>
                        <td>{{ $qr->id }}</td>
                        <td>{{ $qr->name ?? 'N/A' }}</td>
                        <td><code>{{ $qr->code }}</code></td>
                        <td>
                            <span class="badge {{ $qr->booking_id ? 'badge-active' : 'badge-inactive' }}">
                                {{ $qr->booking_id ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $qr->booking ? $qr->booking->id : 'N/A' }}</td>
                        <td>{{ $qr->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <p>No QR codes found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $qrs->links() }}
            </div>

            <a href="{{ route('qr.welcome') }}" class="btn">← Back to Welcome</a>
        </div>
    </div>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token-value" content="{{ csrf_token() }}">
    <div data-track-url="/track-screen" style="display:none;"></div>
    <script>
        // Make CSRF token globally available
        window.csrfToken = '{{ csrf_token() }}';
        // Override getTourCodeFromUrl for analytics page
        window.getTourCodeFromUrl = function() { return null; };
        window.getPageType = function() { return 'analytics'; };
    </script>
    <script src="{{ url('/js/qr-location-tracker.js') }}"></script>
</body>
</html>

