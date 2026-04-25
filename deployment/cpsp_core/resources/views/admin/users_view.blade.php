@extends('layouts.admin')
@section('container_width', 'max-w-full')

@section('content')

    <div class="space-y-8">
        <!-- Page Header -->
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Users List</h1>

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
                    <p class="text-lg font-bold text-slate-900">{{ $users->total() }} Users registered</p>
                </div>

            </div>
            <!-- More stats if needed -->
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">


            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="px-6 py-4 font-bold text-center">#</th>
                            <th class="px-6 py-4 font-bold">Name</th>
                            <th class="px-6 py-4 font-bold">Designation</th>
                            <th class="px-6 py-4 font-bold">PEN No</th>
                            <th class="px-6 py-4 font-bold">Email</th>
                            <th class="px-6 py-4 font-bold text-center">Mobile No</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-500 text-center">
                                    {{ $users->firstItem() + $loop->index }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex-shrink-0 group">
                                            @php
                                                $hasPhoto = $user->profilePhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profilePhoto->file_path);
                                            @endphp
                                            @if($hasPhoto)
                                                <img class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-md transition-transform group-hover:scale-105"
                                                    src="{{ asset('storage/' . $user->profilePhoto->file_path) }}"
                                                    alt="{{ $user->name }}">
                                            @else

                                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-md border-2 border-white">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            @if($user->status === 'Active')
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full shadow-sm"></div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-900 text-sm leading-tight group-hover:text-primary transition-colors">{{ $user->name }}</span>
                                            <span class="text-[11px] font-medium text-slate-400 mt-0.5 tracking-tight uppercase">{{ $user->role }}</span>
                                        </div>
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
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('users.updateStatus', encrypt($user->user_id)) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="relative flex items-center group/status">
                                            <select name="status" onchange="this.form.submit()"
                                                class="pr-7 pl-3 py-1 text-xs font-semibold rounded-lg border-slate-200 outline-none transition-all cursor-pointer appearance-none shadow-sm group-hover/status:border-slate-300
                                                                        {{ $user->status === 'Active' ? 'bg-emerald-50 text-emerald-800 focus:ring-emerald-200' : 'bg-amber-50 text-amber-800 focus:ring-amber-200' }}"
                                                style="background-image: none !important;">
                                                <option value="Active" {{ $user->status === 'Active' ? 'selected' : '' }}
                                                    class="bg-white text-slate-900">Active</option>
                                                <option value="Transferred" {{ $user->status === 'Transferred' ? 'selected' : '' }}
                                                    class="bg-white text-slate-900">Transferred</option>
                                            </select>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-slate-400 absolute right-2 pointer-events-none group-hover/status:text-slate-600 transition-colors"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('users.edit', encrypt($user->user_id)) }}"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Edit Profile">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form method="POST" action="{{ route('users.destroy', encrypt($user->user_id)) }}"
                                            class="inline-block">
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
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center">

                                        <i data-lucide="users-2" class="w-12 h-12 text-slate-200 mb-4"></i>
                                        <p class="text-base font-semibold text-slate-900">No users found</p>
                                        <p class="text-sm mt-1">There are currently no officer accounts registered in the
                                            system.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection