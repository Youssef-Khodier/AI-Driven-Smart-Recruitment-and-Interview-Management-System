@extends('layouts.app')

@section('content')
    <h1>Applicants for {{ $requisition->title }}</h1>
    <p><strong>Department:</strong> {{ $requisition->department->name }}</p>
    <p>Simulated match scores are advisory and sorted highest first.</p>

    @if ($applications->isEmpty())
        <p>No candidates have applied to this requisition yet.</p>
    @else
        <table>
            <thead><tr><th>Candidate</th><th>Status</th><th>Score</th><th>Applied</th><th>Update</th></tr></thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr>
                        <td>{{ $application->candidate->user->name }}<br>{{ $application->candidate->current_title }}</td>
                        <td>{{ $application->status->value }}</td>
                        <td>{{ $application->match_score }} simulated advisory</td>
                        <td>{{ $application->applied_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('hr.applications.update', $application) }}">
                                @csrf
                                @method('PUT')
                                <select name="status" required>
                                    @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->value }}</option>
                                    @endforeach
                                </select>
                                <input name="reason" placeholder="Optional reason">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
