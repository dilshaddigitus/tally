@extends('layouts.admin')

@section('title', 'Devices')

@section('content')
    <h1 class="h3 mb-4">Connector devices</h1>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Tally version</th>
                    <th>Companies</th>
                    <th>Last heartbeat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($devices as $device)
                    <tr>
                        <td>{{ $device->device_name }}</td>
                        <td>{{ $device->customer->name }}</td>
                        <td>
                            <span class="badge bg-{{ $device->status === 'connected' ? 'success' : ($device->status === 'device_revoked' ? 'dark' : 'warning') }}">
                                {{ str_replace('_', ' ', $device->status) }}
                            </span>
                        </td>
                        <td>{{ $device->tallyInstallation?->detected_version ?? '—' }}</td>
                        <td>{{ $device->tallyInstallation?->companies->count() ?? 0 }}</td>
                        <td>{{ $device->last_heartbeat_at?->diffForHumans() ?? 'never' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No connectors have paired yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $devices->links() }}</div>
@endsection
