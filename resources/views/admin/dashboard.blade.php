@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard </h1>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium transition-all bg-white border border-slate-200 rounded-xl shadow-sm hover:bg-slate-50 text-slate-700">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                Export Report
            </button>
        </div>
    </div>

    @if(\App\Models\Seat::countVacant() > 0)
        <div class="p-6 bg-amber-50 border border-amber-200 rounded-3xl shadow-sm animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-600 flex-shrink-0">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-amber-900">Vacant Seats Detected</h4>
                    <p class="text-sm text-amber-800 mt-1">There are <strong>{{ \App\Models\Seat::countVacant() }}</strong> active seats with no officer assigned. Consider assigning officers to ensure smooth petition processing.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(\App\Models\Seat::getVacant() as $vSeat)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white text-amber-700 border border-amber-200 shadow-sm">
                                {{ $vSeat->seat_name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('admin.seatuser.create') }}" class="px-4 py-2 bg-amber-600 text-white text-sm font-bold rounded-xl hover:bg-amber-700 transition-colors shadow-lg shadow-amber-200/50 whitespace-nowrap">
                    Assign Now
                </a>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Users Card -->
        <div class="p-6 transition-all bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-md group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-primary/10 group-hover:bg-primary transition-colors">
                    <i data-lucide="users" class="w-6 h-6 text-primary group-hover:text-white transition-colors"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    +12%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Users</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\User::countUser() }}</h3>
            </div>
        </div>

        <!-- Reports Card -->
        <a href="{{ route('petitions.reports', ['tab' => 'vrs']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-md group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-50 group-hover:bg-amber-500 transition-colors">
                    <i data-lucide="file-text" class="w-6 h-6 text-amber-600 group-hover:text-white transition-colors"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    +5%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">VR Reports</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\PetitionForwarding::countOfVr() }}</h3>
            </div>
        </a>

        <!-- Pending Card -->
        <a href="{{ route('petitions.reports', ['tab' => 'forwarded']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-md group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-50 group-hover:bg-rose-500 transition-colors">
                    <i data-lucide="send" class="w-6 h-6 text-rose-600 group-hover:text-white transition-colors"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                    -2%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Forwarded</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\Petition::countForwardedPetitions() }}</h3>
            </div>
        </a>

        <!-- Decisions Card -->
        <a href="{{ route('petitions.reports', ['tab' => 'decisions']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-md group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 group-hover:bg-emerald-500 transition-colors">
                    <i data-lucide="check-square" class="w-6 h-6 text-emerald-600 group-hover:text-white transition-colors"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    +18%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Decisions</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ \App\Models\Petition::countDecisionPetitions() }}</h3>
            </div>
        </a>
    </div>

    <!-- Charts + Activity -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Chart Area -->
        <div class="p-6 bg-white border border-slate-200 rounded-3xl shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900">Analytics Overview</h3>
                <select class="text-sm border-slate-200 rounded-lg focus:ring-primary/20 bg-slate-50 outline-none">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                </select>
            </div>
            <div class="flex items-center justify-center h-80 bg-slate-50 border border-dashed border-slate-200 rounded-2xl text-slate-400">
                <div class="text-center">
                    <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                    <p class="text-sm font-medium">Chart visualization placeholder</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="p-6 bg-white border border-slate-200 rounded-3xl shadow-sm flex flex-col">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Recent Activity</h3>
            <div class="flex-1 space-y-6">
                <!-- Activity Item -->
                <div class="flex gap-4 relative">
                    <div class="absolute left-4 top-8 bottom-0 w-px bg-slate-100"></div>
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center relative z-10">
                        <i data-lucide="user-plus" class="w-4 h-4 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">New user registered</p>
                        <p class="text-xs text-slate-500 mt-1">John Doe just joined the platform</p>
                        <time class="text-[10px] text-slate-400 mt-1 font-medium italic">2 minutes ago</time>
                    </div>
                </div>
                <!-- Activity Item -->
                <div class="flex gap-4 relative">
                   <div class="absolute left-4 top-8 bottom-0 w-px bg-slate-100"></div>
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center relative z-10">
                        <i data-lucide="file-text" class="w-4 h-4 text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Report submitted</p>
                        <p class="text-xs text-slate-500 mt-1">Annual unit audit report was uploaded</p>
                        <time class="text-[10px] text-slate-400 mt-1 font-medium italic">1 hour ago</time>
                    </div>
                </div>
                 <!-- Activity Item -->
                 <div class="flex gap-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center relative z-10">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">System updated</p>
                        <p class="text-xs text-slate-500 mt-1">v2.1.0 patch was successfully applied</p>
                        <time class="text-[10px] text-slate-400 mt-1 font-medium italic">5 hours ago</time>
                    </div>
                </div>
            </div>
            <button class="mt-8 w-full py-2.5 text-sm font-semibold text-slate-600 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                View All Activity
            </button>
        </div>
    </div>

    <!-- Latest Users Table -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Latest Users</h3>
            <a href="{{ route('users.index') }}" class="text-sm font-semibold text-primary hover:underline">See all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Arun&background=f1f5f9&color=4361ee" alt="">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Arun</p>
                                    <p class="text-xs text-slate-500">arun@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-sm font-bold text-primary hover:text-primary-hover">Edit</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Meera&background=f1f5f9&color=4361ee" alt="">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Meera</p>
                                    <p class="text-xs text-slate-500">meera@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-sm font-bold text-primary hover:text-primary-hover">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
