@extends('layouts.app')

@section('content')
    <h1>Create Job Requisition</h1>
    <form method="POST" action="{{ route('hr.requisitions.store') }}">
        @include('hr.requisitions.form')
    </form>
@endsection
