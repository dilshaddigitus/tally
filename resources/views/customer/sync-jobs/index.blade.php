@extends('layouts.customer')

@section('title', 'Sync history')

@section('content')
    <h1 class="h3 mb-4">Synchronization</h1>

    @if ($connections->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Request a manual sync</div>
            <ul class="list-group list-group-flush">
                @foreach ($connections as $connection)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $connection->tallyCompany->name }}
                        <form method="POST" action="{{ route('customer.company-connections.sync', $connection) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">Sync now</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Module</th>
                    <th>Status</th>
                    <th>Attempt</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>{{ $job->tallyCompany->name }}</td>
                        <td>{{ $job->job_type }}</td>
                        <td>{{ $job->data_module }}</td>
                        <td>
                            <span class="badge bg-{{ match($job->status) {
                                'completed' => 'success',
                                'failed' => 'danger',
                                'running', 'claimed' => 'info',
                                default => 'secondary',
                            } }}">{{ $job->status }}</span>
                        </td>
                        <td>{{ $job->attempt }}/{{ $job->max_attempts }}</td>
                        <td>{{ $job->completed_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No sync jobs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $jobs->links() }}</div>
@endsection
