@extends('layouts.app')

@section('content')
    <h1>Create Assessment</h1>
    <p><strong>Job:</strong> {{ $requisition->title }}</p>
    <form method="POST" action="{{ route('hr.assessments.store', $requisition) }}">
        @include('hr.assessments.form')
    </form>
@endsection
