<x-filament::page>
    <div class="space-y-8">

        {{-- House Information --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Tenant's House --}}
            <div class="bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold">House</h2>
                <p class="text-xl">{{ $houseName }}</p>
            </div>

            {{-- Pending Rent --}}
            <div class="bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold">Pending Rent</h2>
                <p class="text-xl text-red-600">KES {{ number_format($pendingAmount) }}</p>
            </div>

        </div>

        {{-- Recent Invoices --}}
        <div>
            <h3 class="text-lg font-semibold">Recent Invoices</h3>
            <table class="w-full border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border">#</th>
                        <th class="p-2 border">Total</th>
                        <th class="p-2 border">Balance</th>
                        <th class="p-2 border">Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recentInvoices as $invoice)
                    <tr>
                        <td class="p-2 border">{{ $invoice->invoice_number }}</td>
                        <td class="p-2 border">{{ number_format($invoice->amount) }}</td>
                        <td class="p-2 border">{{ number_format($invoice->balance) }}</td>
                        <td class="p-2 border">{{ $invoice->invoice_date }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center p-2 border">No invoices yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Payments --}}
        <div>
            <h3 class="text-lg font-semibold">Recent Payments</h3>
            <table class="w-full border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border">#</th>
                        <th class="p-2 border">Amount Paid</th>
                        <th class="p-2 border">Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recentPayments as $payment)
                    <tr>
                        <td class="p-2 border">{{ $payment->id }}</td>
                        <td class="p-2 border">{{ number_format($payment->amount_paid) }}</td>
                        <td class="p-2 border">{{ $payment->payment_date ?? $payment->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center p-2 border">No payments yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-filament::page>
