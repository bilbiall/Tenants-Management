<x-filament::page>
    <div class="space-y-6">

        {{-- Dashboard Heading --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Welcome to the Renty Dashboard</h1>
            <p class="text-sm text-gray-500">Overview of your tenants, invoices, and payments etc</p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Total Tenants --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Total Tenants</p>
                <p class="text-2xl font-bold">{{ $totalTenants }}</p>
            </x-filament::card>

            {{-- New Tenants This Month --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">New Tenants ({{ now()->format('F') }})</p>
                <p class="text-2xl font-bold">{{ $newTenants }}</p>
            </x-filament::card>

            {{-- Total Houses --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Total Houses</p>
                <p class="text-2xl font-bold">{{ $totalHouses }}</p>
            </x-filament::card>

            {{-- Vacant Houses --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Vacant Houses</p>
                <p class="text-2xl font-bold text-red-600">{{ $vacantHouses }}</p>
            </x-filament::card>

            {{-- Occupied Houses --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Occupied Houses</p>
                <p class="text-2xl font-bold text-green-600">{{ $occupiedHouses }}</p>
            </x-filament::card>

            {{-- Total Invoices --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Total Invoices</p>
                <p class="text-2xl font-bold">{{ $totalInvoices }}</p>
            </x-filament::card>

            {{-- Paid Invoices --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Paid Invoices</p>
                <p class="text-2xl font-bold text-green-600">{{ $paidInvoices }}</p>
            </x-filament::card>

            {{-- Unpaid Invoices --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Unpaid Invoices</p>
                <p class="text-2xl font-bold text-red-600">{{ $unpaidInvoices }}</p>
            </x-filament::card>

            {{-- Partial Invoices --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Partial Invoices</p>
                <p class="text-2xl font-bold text-yellow-500">{{ $partialInvoices }}</p>
            </x-filament::card>

            {{-- Total Payments This Month --}}
            <x-filament::card>
                <p class="text-sm text-gray-500">Payments ({{ now()->format('F') }})</p>
                <p class="text-2xl font-bold text-blue-600">KES {{ number_format($totalPayments) }}</p>
            </x-filament::card>
        </div>

        {{-- Recent Payments Table --}}
        <!--<div class="mt-10">-->
        <div class="mt-10 flex justify-center">
            <div class="w-full max-w-4xl">
            <h2 class="text-lg font-semibold mb-4">Recent Payments</h2>

            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    {{ $payment->tenant->tenant_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                                    KES {{ number_format($payment->amount_paid) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    {{ $payment->reference ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No recent payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>

    </div>
</x-filament::page>

