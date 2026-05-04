@extends('layouts.app')

@section('content')
    <h1>Job Requisitions</h1>
    <p><a class="button" href="{{ route('hr.requisitions.create') }}">Create requisition</a></p>

    <p>
        <a href="{{ route('hr.requisitions.index') }}">All</a>
        @foreach ($statuses as $status)
            | <a href="{{ route('hr.requisitions.index', ['status' => $status->value]) }}">{{ $status->value }}</a>
        @endforeach
    </p>

    @if ($requisitions->isEmpty())
        <p>No requisitions match this filter. Create a draft to begin the approval workflow.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Creator</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requisitions as $requisition)
                    <tr>
                        <td>{{ $requisition->title }}</td>
                        <td>{{ $requisition->department->name }}</td>
                        <td>{{ $requisition->status->value }}</td>
                        <td>{{ $requisition->creator->name }}</td>
                        <td>{{ $requisition->updated_at->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('hr.requisitions.show', $requisition) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
