@extends('layouts.app')

@section('content')
    <h1>Add Question</h1>
    <p><strong>Assessment:</strong> {{ $assessment->title }}</p>
    <form method="POST" action="{{ route('hr.assessment-questions.store', $assessment) }}">
        @include('hr.assessment-questions.form')
    </form>
@endsection
