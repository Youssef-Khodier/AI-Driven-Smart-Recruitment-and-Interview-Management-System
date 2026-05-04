@extends('layouts.app')

@section('content')
    <h1>My Applications</h1>

    @if ($applications->isEmpty())
        <p>You have not submitted any applications yet.</p>
    @else
        <table>
            <thead><tr><th>Job</th><th>Status</th><th>Score</th><th>Applied</th><th>Details</th></tr></thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr>
                        <td>{{ $application->jobRequisition->title }}</td>
                        <td>{{ $application->status->value }}</td>
                        <td>{{ $application->match_score }} simulated advisory</td>
                        <td>{{ $application->applied_at->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('candidate.applications.show', $application) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
