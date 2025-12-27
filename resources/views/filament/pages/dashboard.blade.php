<x-filament::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <div class="space-y-6">
        {{-- Dashboard Header --}}
        <div class="space-y-2">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-gray-500 dark:text-gray-400">Welcome back! Here's a snapshot of your rental business.</p>
        </div>

        {{-- KPI Cards - Top Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Revenue --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                        <p class="text-3xl font-bold mt-2">KES {{ number_format($totalRevenue) }}</p>
                        <p class="text-blue-100 text-xs mt-1">All time</p>
                    </div>
                    <div class="text-blue-200 opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Occupancy Rate --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Occupancy Rate</p>
                        <p class="text-3xl font-bold mt-2">{{ $occupancyRate }}%</p>
                        <p class="text-green-100 text-xs mt-1">{{ $occupiedHouses }} of {{ $totalHouses }} houses</p>
                    </div>
                    <div class="text-green-200 opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Outstanding Balance --}}
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Outstanding Balance</p>
                        <p class="text-3xl font-bold mt-2">KES {{ number_format($outstandingBalance) }}</p>
                        <p class="text-orange-100 text-xs mt-1">To be collected</p>
                    </div>
                    <div class="text-orange-200 opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Tenants --}}
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Active Tenants</p>
                        <p class="text-3xl font-bold mt-2">{{ $totalTenants }}</p>
                        <p class="text-purple-100 text-xs mt-1">{{ $newTenantsThisMonth }} new this month</p>
                    </div>
                    <div class="text-purple-200 opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M13 16h2M9 20h5v-2a3 3 0 00-5.856-1.487M9 10h.01M7 20h5v-2a3 3 0 00-5.856-1.487M7 10h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row 1 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Monthly Revenue Chart --}}
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue Trend (Last 7 Months)</h3>
                <canvas id="revenueChart" height="80"></canvas>
            </div>

            {{-- Invoice Status Chart --}}
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice Status Distribution</h3>
                <canvas id="statusChart" height="80"></canvas>
            </div>
        </div>

        {{-- Charts Row 2 --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Activity Trend --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Activity Trend (Last 7 Days)</h3>
                <canvas id="activityChart" height="80"></canvas>
            </div>

            {{-- Quick Stats --}}
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Invoices</span>
                        <span class="font-bold text-lg text-gray-900 dark:text-white">{{ $totalInvoices }}</span>
                    </div>
                    <div class="border-t dark:border-gray-700"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-green-600 dark:text-green-400">Paid</span>
                        <span class="font-bold text-lg text-green-600">{{ $paidInvoices }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-red-600 dark:text-red-400">Unpaid</span>
                        <span class="font-bold text-lg text-red-600">{{ $unpaidInvoices }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-yellow-600 dark:text-yellow-400">Partial</span>
                        <span class="font-bold text-lg text-yellow-600">{{ $partialInvoices }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Payments Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Payments</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y dark:divide-gray-700 divide-gray-200">
                        <thead class="dark:bg-gray-800 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium dark:text-gray-400 text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium dark:text-gray-400 text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium dark:text-gray-400 text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium dark:text-gray-400 text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="dark:bg-gray-900 bg-white divide-y dark:divide-gray-700 divide-gray-200">
                            @forelse ($recentPayments as $payment)
                                <tr class="dark:hover:bg-gray-800 hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300 text-gray-900 font-medium">
                                        {{ $payment->tenant->tenant_name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                                        KES {{ number_format($payment->amount_paid) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300 text-gray-900">
                                        {{ $payment->reference ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-400 text-gray-500">
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm dark:text-gray-400 text-gray-500">No recent payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($monthlyRevenueData['months']),
                datasets: [{
                    label: 'Revenue (KES)',
                    data: @json($monthlyRevenueData['revenues']),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                        }
                    }
                }
            }
        });

        // Invoice Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Unpaid', 'Partial'],
                datasets: [{
                    data: [{{ $invoiceStatusData['paid'] }}, {{ $invoiceStatusData['unpaid'] }}, {{ $invoiceStatusData['partial'] }}],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                    ],
                    borderColor: [
                        '#22c55e',
                        '#ef4444',
                        '#eab308',
                    ],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                            padding: 15,
                        }
                    }
                }
            }
        });

        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: @json($activityTrendData['days']),
                datasets: [{
                    label: 'Activities',
                    data: @json($activityTrendData['activities']),
                    backgroundColor: 'rgba(139, 92, 246, 0.7)',
                    borderColor: '#8b5cf6',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151',
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                        }
                    }
                }
            }
        });
    </script>
</x-filament::page>

