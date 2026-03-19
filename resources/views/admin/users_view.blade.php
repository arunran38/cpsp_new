@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">System Users</h1>
            <p class="text-sm text-slate-500">Manage officer accounts, designations, and system access permissions.</p>
        </div>
        <x-button variant="primary" size="md" icon="user-plus" onclick="window.location='{{ route('users.create') }}'">
            Add New User
        </x-button>
    </div>

    <!-- Stats Summary (Small) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex items-center gap-4">
            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-primary"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total</p>
                <p class="text-lg font-bold text-slate-900">{{ count($users) }} Users</p>
            </div>
        </div>
        <!-- More stats if needed -->
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border-b border-emerald-100 flex items-center gap-3 text-emerald-800 text-sm font-medium">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Officer</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Information</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img class="flex-shrink-0 w-10 h-10 rounded-full ring-2 ring-slate-100" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f8fafc&color=4361ee" alt="">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-700">
                                        {{ $user->designation === 'Others' ? $user->other_designation : $user->designation }}
                                    </span>
                                    <span class="mt-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 uppercase w-fit">
                                        {{ $user->pen }} • {{ $user->role }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.edit', encrypt($user->user_id)) }}" 
                                       class="text-sm font-bold text-emerald-600 hover:text-emerald-700 transition-colors"
                                       title="Edit Profile">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', encrypt($user->user_id)) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-sm font-bold text-rose-600 hover:text-rose-700 transition-colors"
                                                onclick="return confirm('Are you sure you want to remove this user?')"
                                                title="Remove User">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="users-2" class="w-12 h-12 text-slate-200 mb-4"></i>
                                    <p class="text-base font-semibold text-slate-900">No users found</p>
                                    <p class="text-sm mt-1">There are currently no officer accounts registered in the system.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection