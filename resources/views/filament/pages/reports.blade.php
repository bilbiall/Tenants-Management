<x-filament::page>
    <div class="space-y-6">
        <div class="flex gap-4 items-end">
            <form method="GET" class="flex gap-2 items-end">
                <div>
                    <label class="block text-sm text-gray-500 dark:text-gray-300">From</label>
                    <input type="month" name="from" value="{{ request()->query('from') ? \Carbon\Carbon::parse(request()->query('from'))->format('Y-m') : \Carbon\Carbon::parse($from)->format('Y-m') }}" class="mt-1 block border rounded px-2 py-1" />
                </div>
                <div>
                    <label class="block text-sm text-gray-500 dark:text-gray-300">To</label>
                    <input type="month" name="to" value="{{ request()->query('to') ? \Carbon\Carbon::parse(request()->query('to'))->format('Y-m') : \Carbon\Carbon::parse($to)->format('Y-m') }}" class="mt-1 block border rounded px-2 py-1" />
                </div>
                <div>
                    <label class="block text-sm text-gray-500 dark:text-gray-300">Tenant</label>
                    <input type="search" name="tenant_search" placeholder="Search tenant name or phone" value="{{ request()->query('tenant_search', $tenant_search ?? '') }}" class="mt-1 block border rounded px-2 py-1 w-64" />
                </div>
                <div>
                    <button class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded">Apply</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-white dark:bg-gray-900 shadow rounded">
                <p class="text-sm text-gray-500 dark:text-gray-300">Total Invoiced</p>
                <p class="text-2xl font-bold">KES {{ number_format($summary['total_invoiced'] ?? 0) }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-900 shadow rounded">
                <p class="text-sm text-gray-500 dark:text-gray-300">Total Paid</p>
                <p class="text-2xl font-bold">KES {{ number_format($summary['total_paid'] ?? 0) }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-900 shadow rounded">
                <p class="text-sm text-gray-500 dark:text-gray-300">Outstanding</p>
                <p class="text-2xl font-bold text-red-600">KES {{ number_format($summary['outstanding'] ?? 0) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-white dark:bg-gray-900 shadow rounded" style="height:340px;">
                <h3 class="font-semibold mb-2 dark:text-gray-100">Invoices vs Payments</h3>
                <div style="height:280px;">
                    <canvas id="chartInvoices" style="height:100%" ></canvas>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-gray-900 shadow rounded" style="height:340px;">
                <h3 class="font-semibold mb-2 dark:text-gray-100">Payments</h3>
                <div style="height:280px;">
                    <canvas id="chartPayments" style="height:100%"></canvas>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white dark:bg-gray-900 shadow rounded p-4">
            <h3 class="font-semibold mb-3 dark:text-gray-100">Invoices</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-sm text-gray-600 dark:text-gray-300">Invoice #</th>
                            <th class="px-3 py-2 text-left text-sm text-gray-600 dark:text-gray-300">Tenant</th>
                            <th class="px-3 py-2 text-left text-sm text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-3 py-2 text-right text-sm text-gray-600 dark:text-gray-300">Amount</th>
                            <th class="px-3 py-2 text-right text-sm text-gray-600 dark:text-gray-300">Paid</th>
                            <th class="px-3 py-2 text-right text-sm text-gray-600 dark:text-gray-300">Balance</th>
                            <th class="px-3 py-2 text-left text-sm text-gray-600 dark:text-gray-300">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoices as $invoice)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $invoice->invoice_number }}</td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ optional($invoice->tenant)->tenant_name }}<br><span class="text-xs text-gray-500">{{ optional($invoice->tenant)->phone_number }}</span></td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-700 dark:text-gray-200">KES {{ number_format($invoice->amount ?? 0) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-700 dark:text-gray-200">KES {{ number_format($invoice->payments->sum('amount_paid') ?? 0) }}</td>
                                <td class="px-3 py-2 text-sm text-right text-gray-700 dark:text-gray-200">KES {{ number_format($invoice->balance ?? 0) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ ucfirst($invoice->status ?? 'n/a') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode($labels) !!};
        const invoiceData = {!! json_encode($invoiceTotals) !!};
        const paymentData = {!! json_encode($paymentTotals) !!};

        const ctxInv = document.getElementById('chartInvoices');
        if (ctxInv) {
            new Chart(ctxInv.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Invoices', data: invoiceData, backgroundColor: 'rgba(99,102,241,0.8)' },
                        { label: 'Payments', data: paymentData, backgroundColor: 'rgba(34,197,94,0.8)' },
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const ctxPay = document.getElementById('chartPayments');
        if (ctxPay) {
            new Chart(ctxPay.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Payments', data: paymentData, borderColor: 'rgba(34,197,94,1)', tension: 0.3, fill: false }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    </script>
</x-filament::page>
