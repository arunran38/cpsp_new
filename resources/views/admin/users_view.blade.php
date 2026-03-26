@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Users List</h1>

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

        @if(session('error'))
            <div class="p-4 bg-rose-50 border-b border-rose-100 flex items-center gap-3 text-rose-800 text-sm font-medium">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Sl No</th>
                        <th class="px-6 py-4 font-bold">Name</th>
                        <th class="px-6 py-4 font-bold">Designation</th>
                        <th class="px-6 py-4 font-bold">PEN No</th>
                        <th class="px-6 py-4 font-bold">Email</th>
                        <th class="px-6 py-4 font-bold text-center">Mobile No</th>
                        <th class="px-6 py-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->profilePhoto)
                                        <img class="flex-shrink-0 w-8 h-8 rounded-full ring-2 ring-slate-100 object-cover" 
                                             src="{{ asset('storage/' . $user->profilePhoto->file_path) }}" alt="{{ $user->name }}">
                                    @else
                                        <img class="flex-shrink-0 w-8 h-8 rounded-full ring-2 ring-slate-100" 
                                             src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f8fafc&color=4361ee" alt="{{ $user->name }}">
                                    @endif
                                    <span class="font-bold text-slate-900">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $user->designation === 'Others' ? $user->other_designation : $user->designation }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-600">
                                {{ $user->pen }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">
                                {{ $user->mobile_number }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('users.edit', encrypt($user->user_id)) }}" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Edit Profile">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', encrypt($user->user_id)) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                onclick="return confirm('Are you sure you want to remove this user?')"
                                                title="Remove User">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
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