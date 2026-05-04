@extends('layouts.app')

@section('content')
    <h1>Edit Job Requisition</h1>
    <form method="POST" action="{{ route('hr.requisitions.update', $requisition) }}">
        @include('hr.requisitions.form')
    </form>
@endsection
