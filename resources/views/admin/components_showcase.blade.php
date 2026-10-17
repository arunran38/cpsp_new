@extends('layouts.admin')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8" x-data="adminFormValidation()">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Component Showcase</h1>
            <p class="mt-1 text-sm text-slate-300">Reusable form and action components used throughout the admin pages.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Form Fields</h2>
                <x-input label="Text Input" name="showcase_text" placeholder="Example text" icon="user" />
                <x-select label="Select Input" name="showcase_select"
                    :options="['' => 'Choose an option', 'first' => 'First option', 'second' => 'Second option']" />
            </section>

            <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Actions</h2>
                <div class="flex flex-wrap items-center gap-3">
                    <x-button type="button" icon="check-circle">Primary Action</x-button>
                    <x-button type="button" variant="light" icon="arrow-left">Secondary Action</x-button>
                </div>
            </section>
        </div>
    </div>
@section('scripts')
    @include('components.admin-form-validation-script')
@endsection
@endsection
