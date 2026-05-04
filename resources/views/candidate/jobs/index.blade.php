@extends('layouts.app')

@section('content')
    <h1>Open Jobs</h1>

    @if ($jobs->isEmpty())
        <p>No open jobs are available right now. Check back after HR opens requisitions.</p>
    @else
        <table>
            <thead><tr><th>Title</th><th>Department</th><th>Summary</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($jobs as $job)
                    <tr>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->department->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($job->description, 90) }}</td>
                        <td><a href="{{ route('candidate.jobs.show', $job) }}">View and apply</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
