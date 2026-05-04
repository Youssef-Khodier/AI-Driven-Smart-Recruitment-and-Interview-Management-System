@extends('layouts.app')

@section('content')
    <h1>Edit Assessment</h1>
    <p><strong>Job:</strong> {{ $requisition->title }}</p>
    <form method="POST" action="{{ route('hr.assessments.update', $assessment) }}">
        @include('hr.assessments.form')
    </form>
@endsection
