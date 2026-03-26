@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.user')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500">Welcome back, {{ Auth::user()->name ?? 'User' }}! Here's what's happening today.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-all rounded-xl shadow-sm bg-primary hover:bg-primary/90 focus:ring-2 focus:ring-primary/20 focus:outline-none">
                <i data-lucide="plus" class="w-4 h-4"></i>
                New Petition
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Stat Card 1 -->
        <a href="{{ route('petitions.reports', ['tab' => 'all']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-2xl hover:shadow-lg hover:shadow-slate-200/50">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <span class="flex items-center gap-1 text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    +12%
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-sm font-medium text-slate-500">Total Petitions</h3>
                <p class="text-2xl font-bold text-slate-900 mt-1">124</p>
            </div>
        </a>

        <!-- Stat Card 2 -->
        <a href="{{ route('petitions.reports', ['tab' => 'forwarded']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-2xl hover:shadow-lg hover:shadow-slate-200/50">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <i data-lucide="send" class="w-6 h-6"></i>
                </div>
                <span class="flex items-center gap-1 text-sm font-medium text-rose-600 bg-rose-50 px-2 py-1 rounded-lg">
                    <i data-lucide="trending-down" class="w-3 h-3"></i>
                    -2%
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-sm font-medium text-slate-500">Forwarded</h3>
                <p class="text-2xl font-bold text-slate-900 mt-1">18</p>
            </div>
        </a>

        <!-- Stat Card 3 -->
        <a href="{{ route('petitions.reports', ['tab' => 'vrs']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-2xl hover:shadow-lg hover:shadow-slate-200/50">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <i data-lucide="file-check" class="w-6 h-6"></i>
                </div>
                <span class="flex items-center gap-1 text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    +8%
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-sm font-medium text-slate-500">VRs Received</h3>
                <p class="text-2xl font-bold text-slate-900 mt-1">92</p>
            </div>
        </a>

        <!-- Stat Card 4 -->
        <a href="{{ route('petitions.reports', ['tab' => 'decisions']) }}" class="block p-6 transition-all bg-white border border-slate-200 rounded-2xl hover:shadow-lg hover:shadow-slate-200/50">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                </div>
                <span class="flex items-center gap-1 text-sm font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    +24%
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-sm font-medium text-slate-500">Final Decisions</h3>
                <p class="text-2xl font-bold text-slate-900 mt-1">14</p>
            </div>
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Recent Activities / Petitions -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">Recent Petitions</h2>
                    <a href="#" class="text-sm font-medium text-primary hover:text-primary/80 transition-colors">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Petition ID</th>
                                <th scope="col" class="px-6 py-3 font-medium">Subject</th>
                                <th scope="col" class="px-6 py-3 font-medium">Date</th>
                                <th scope="col" class="px-6 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">#PT-2026-089</td>
                                <td class="px-6 py-4 text-slate-600">Request for Verification</td>
                                <td class="px-6 py-4 text-slate-500">Mar 19, 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/50">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">#PT-2026-088</td>
                                <td class="px-6 py-4 text-slate-600">Application Update</td>
                                <td class="px-6 py-4 text-slate-500">Mar 18, 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                        Approved
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">#PT-2026-085</td>
                                <td class="px-6 py-4 text-slate-600">Document Submission</td>
                                <td class="px-6 py-4 text-slate-500">Mar 15, 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                        Approved
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">#PT-2026-082</td>
                                <td class="px-6 py-4 text-slate-600">Initial Inquiry</td>
                                <td class="px-6 py-4 text-slate-500">Mar 10, 2026</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/50">
                                        Rejected
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-4">
                    <button class="flex flex-col items-center justify-center p-4 gap-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200 border-dashed text-slate-700 group">
                        <i data-lucide="file-plus" class="w-6 h-6 text-slate-400 group-hover:text-primary transition-colors"></i>
                        <span class="text-xs font-medium text-center">New Petition</span>
                    </button>
                    <button class="flex flex-col items-center justify-center p-4 gap-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200 border-dashed text-slate-700 group">
                        <i data-lucide="upload-cloud" class="w-6 h-6 text-slate-400 group-hover:text-primary transition-colors"></i>
                        <span class="text-xs font-medium text-center">Upload Docs</span>
                    </button>
                    <button class="flex flex-col items-center justify-center p-4 gap-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200 border-dashed text-slate-700 group">
                        <i data-lucide="help-circle" class="w-6 h-6 text-slate-400 group-hover:text-primary transition-colors"></i>
                        <span class="text-xs font-medium text-center">Support</span>
                    </button>
                    <button class="flex flex-col items-center justify-center p-4 gap-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200 border-dashed text-slate-700 group">
                        <i data-lucide="settings" class="w-6 h-6 text-slate-400 group-hover:text-primary transition-colors"></i>
                        <span class="text-xs font-medium text-center">Settings</span>
                    </button>
                </div>
            </div>

            <!-- Notifications / Activity Feed -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <h2 class="text-base font-semibold text-slate-900 mb-4">System Alerts</h2>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="flex-none mt-0.5">
                            <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5"></div>
                        </div>
                        <div>
                            <p class="text-sm text-slate-700">Your profile information needs to be updated by month end.</p>
                            <span class="text-xs text-slate-500 mt-1 block">2 hours ago</span>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-none mt-0.5">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5"></div>
                        </div>
                        <div>
                            <p class="text-sm text-slate-700">Petition <span class="font-medium text-slate-900">#PT-2026-088</span> was successfully approved.</p>
                            <span class="text-xs text-slate-500 mt-1 block">Yesterday</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection