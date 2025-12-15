@extends('layouts.vertical', ['title' => 'Broker Profile', 'subTitle' => 'Profile'])

@section('content')

	<div class="row">
		<div class="col-xl-9 col-lg-8">
			<!-- Cover image -->
			@if(!empty($brocker->cover_image))
				<div class="mb-3">
					<img src="{{ asset('storage/' . $brocker->cover_image) }}" alt="Cover Image" class="img-fluid rounded" style="max-height: 260px; width: 100%; object-fit: cover;">
				</div>
			@endif

			<!-- Profile Card -->
			<div class="card">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="me-3">
							@if(!empty($brocker->profile_image))
								<img src="{{ asset('storage/' . $brocker->profile_image) }}" alt="Profile" class="rounded-circle" style="width: 84px; height: 84px; object-fit: cover;">
							@else
								<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 84px; height: 84px; color: #fff;">
									<span class="fs-4">{{ strtoupper(substr(($user->firstname ?? 'U'),0,1)) }}</span>
								</div>
							@endif
						</div>
						<div class="flex-grow-1">
							<h4 class="mb-1">{{ trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: 'Unnamed User' }}</h4>
							<p class="mb-0 text-muted">{{ $brocker->position_title ?? 'Broker' }} @if($brocker->company_name) • {{ $brocker->company_name }} @endif</p>
							<p class="mb-0 text-muted">License: <strong>{{ $brocker->license_number }}</strong> @if($brocker->license_verified) <span class="badge bg-success ms-1">Verified</span> @endif</p>
						</div>
						<div class="text-end">
							<a href="{{ route('broker.edit', $brocker->id) }}" class="btn btn-outline-primary">Edit Profile</a>
						</div>
					</div>
				</div>
			</div>

			<!-- About / Bio -->
			@if(!empty($brocker->bio))
			<div class="card mt-3">
				<div class="card-header">
					<h4 class="card-title mb-0">About</h4>
				</div>
				<div class="card-body">
					<p class="mb-0">{{ $brocker->bio }}</p>
				</div>
			</div>
			@endif

			<!-- Details -->
			<div class="card mt-3">
				<div class="card-header">
					<h4 class="card-title mb-0">Details</h4>
				</div>
				<div class="card-body">
					<div class="row g-3">
						<div class="col-md-6">
							<div class="text-muted">Email</div>
							<div>
								{{ $user->email }}
								@if(!empty($user->email_verified_at))
									<span class="badge bg-success ms-1">Verified</span>
								@else
									<span class="badge bg-warning ms-1">Unverified</span>
								@endif
							</div>
						</div>
						<div class="col-md-6">
							<div class="text-muted">Mobile</div>
							<div>
								{{ $user->mobile ?? '-' }}
								@if(!empty($user->mobile_verified_at))
									<span class="badge bg-success ms-1">Verified</span>
								@else
									<span class="badge bg-warning ms-1">Unverified</span>
								@endif
							</div>
						</div>
						<div class="col-md-6">
							<div class="text-muted">Phone</div>
							<div>{{ $brocker->phone_number ?? '-' }}</div>
						</div>
						<div class="col-md-6">
							<div class="text-muted">WhatsApp</div>
							<div>{{ $brocker->whatsapp_number ?? '-' }}</div>
						</div>
						<div class="col-md-6">
							<div class="text-muted">Experience</div>
							<div>{{ $brocker->years_of_experience ?? 0 }} years</div>
						</div>
						<div class="col-md-6">
							<div class="text-muted">Commission Rate</div>
							<div>{{ number_format((float)($brocker->commission_rate ?? 0), 2) }}%</div>
						</div>
						<div class="col-md-12">
							<div class="text-muted">Address</div>
							<div>
								{{ $brocker->address }}
								@if($brocker->city) , {{ $brocker->city }} @endif
								@if($brocker->state) , {{ $brocker->state }} @endif
								@if($brocker->country) , {{ $brocker->country }} @endif
								@if($brocker->pin_code) , {{ $brocker->pin_code }} @endif
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Social Links -->
			@php($links = $brocker->social_links ?? [])
			@if(is_array($links) && count($links))
			<div class="card mt-3">
				<div class="card-header">
					<h4 class="card-title mb-0">Social</h4>
				</div>
				<div class="card-body">
					<ul class="list-unstyled mb-0">
						@foreach($links as $key => $url)
							@if(!empty($url))
								<li class="mb-1">
									<span class="text-capitalize me-2">{{ $key }}:</span>
									<a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $url }}</a>
								</li>
							@endif
						@endforeach
					</ul>
				</div>
			</div>
			@endif

			<!-- Status & Metrics -->
			<div class="card mt-3">
				<div class="card-header">
					<h4 class="card-title mb-0">Status & Metrics</h4>
				</div>
				<div class="card-body">
					<div class="row g-3">
						<div class="col-md-4">
							<div class="text-muted">Status</div>
							<div>
								<span class="badge @if($brocker->status === 'approved') bg-success @elseif($brocker->status === 'pending') bg-warning @else bg-danger @endif">
									{{ ucfirst($brocker->status) }}
								</span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="text-muted">Working</div>
							<div>
								<span class="badge {{ $brocker->working_status ? 'bg-primary' : 'bg-secondary' }}">
									{{ $brocker->working_status ? 'Active' : 'Inactive' }}
								</span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="text-muted">Rating</div>
							<div>{{ number_format((float)($brocker->average_rating ?? 0), 2) }} / 5</div>
						</div>
						<div class="col-md-4">
							<div class="text-muted">Total Sales</div>
							<div>{{ $brocker->total_sales ?? 0 }}</div>
						</div>
						<div class="col-md-4">
							<div class="text-muted">Joined</div>
							<div>{{ optional($brocker->joined_at)->format('M d, Y') }}</div>
						</div>
						<div class="col-md-4">
							<div class="text-muted">Approved At</div>
							<div>{{ optional($brocker->approved_at)->format('M d, Y') ?? '-' }}</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Sidebar: User quick info -->
		<div class="col-xl-3 col-lg-4">
			<div class="card">
				<div class="card-header">
					<h4 class="card-title mb-0">User</h4>
				</div>
				<div class="card-body">
					<div class="mb-2">
						<div class="text-muted">Name</div>
						<div>{{ trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: '-' }}</div>
					</div>
					<div class="mb-2">
						<div class="text-muted">Email</div>
						<div>
							{{ $user->email }}
							@if(!empty($user->email_verified_at))
								<span class="badge bg-success ms-1">Verified</span>
							@else
								<span class="badge bg-warning ms-1">Unverified</span>
							@endif
						</div>
					</div>
					<div class="mb-0">
						<div class="text-muted">Mobile</div>
						<div>
							{{ $user->mobile ?? '-' }}
							@if(!empty($user->mobile_verified_at))
								<span class="badge bg-success ms-1">Verified</span>
							@else
								<span class="badge bg-warning ms-1">Unverified</span>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection