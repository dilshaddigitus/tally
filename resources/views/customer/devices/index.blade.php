@extends('layouts.customer')

@section('title', 'Devices')

@section('content')
    <h1 class="h3 mb-4">Connected devices</h1>

    @forelse ($devices as $device)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ $device->device_name }}</span>
                <span class="badge bg-{{ $device->status === 'connected' ? 'success' : 'warning' }}">
                    {{ str_replace('_', ' ', $device->status) }}
                </span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Tally version: {{ $device->tallyInstallation?->detected_version ?? 'not detected yet' }} &middot;
                    Last seen: {{ $device->last_heartbeat_at?->diffForHumans() ?? 'never' }}
                </p>

                @if ($device->tallyInstallation && $device->tallyInstallation->companies->isNotEmpty())
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Company</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($device->tallyInstallation->companies as $company)
                                <tr>
                                    <td>{{ $company->name }}</td>
                                    <td>
                                        @if ($company->companyConnection)
                                            <span class="badge bg-success">Authorized</span>
                                        @else
                                            <span class="badge bg-secondary">Not authorized</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($company->companyConnection)
                                            <form method="POST" action="{{ route('customer.companies.deauthorize', $company) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('customer.companies.authorize', $company) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success">Authorize &amp; sync</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted mb-0">No companies reported by this connector yet.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            No connector has paired yet. Ask your Super Admin for a pairing code and run the Tally connector installer on the computer running Tally.
        </div>
    @endforelse
@endsection
