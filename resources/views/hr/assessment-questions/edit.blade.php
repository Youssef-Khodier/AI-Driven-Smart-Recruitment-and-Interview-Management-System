@extends('layouts.app')

@section('content')
    <h1>Edit Question</h1>
    <p><strong>Assessment:</strong> {{ $assessment->title }}</p>
    <form method="POST" action="{{ route('hr.assessment-questions.update', $question) }}">
        @include('hr.assessment-questions.form')
    </form>
@endsection
