@extends('layouts.app')

@section('content')
    <h1>Assessments for {{ $requisition->title }}</h1>
    <p><strong>Department:</strong> {{ $requisition->department->name }}</p>
    <p>
        <a class="button" href="{{ route('hr.assessments.create', $requisition) }}">Create assessment</a>
        <a class="button" href="{{ route('hr.assessment-results.index', $requisition) }}">Review results</a>
    </p>

    @if ($assessments->isEmpty())
        <p>No assessments have been created for this job.</p>
    @else
        <table>
            <thead><tr><th>Title</th><th>Duration</th><th>Questions</th><th>Attempts</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($assessments as $assessment)
                    <tr>
                        <td>{{ $assessment->title }}</td>
                        <td>{{ $assessment->duration_minutes }} minutes</td>
                        <td>{{ $assessment->questions_count }}</td>
                        <td>{{ $assessment->candidate_assessments_count }}</td>
                        <td>{{ $assessment->is_active ? 'Active' : 'Inactive' }}</td>
                        <td><a href="{{ route('hr.assessments.show', $assessment) }}">Open</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
