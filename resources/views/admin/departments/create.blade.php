@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold tracking-tight text-white">Create Department</h1>
        </div>

        @include('admin.departments.partials.form')
    </div>
@section('scripts')
    @include('components.admin-form-validation-script')
@endsection
@endsection
