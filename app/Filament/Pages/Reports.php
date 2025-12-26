<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $slug = 'reports';
    protected static ?string $navigationGroup = 'Analytics';
    protected static string $view = 'filament.pages.reports';

    public $from;
    public $to;
    public $labels = [];
    public $invoiceTotals = [];
    public $paymentTotals = [];
    public $summary = [];
    public $tenant_search;
    public $invoices = [];

    public function mount(): void
    {
        // Default to last 6 months
        $this->to = request()->query('to') ? Carbon::parse(request()->query('to')) : Carbon::now();
        $this->from = request()->query('from') ? Carbon::parse(request()->query('from')) : (clone $this->to)->subMonths(5)->startOfMonth();
        $this->tenant_search = request()->query('tenant_search');

        $this->buildStats();
    }

    public function buildStats(): void
    {
        $start = Carbon::parse($this->from)->startOfMonth();
        $end = Carbon::parse($this->to)->endOfMonth();

        $months = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $this->labels = array_map(fn($m) => Carbon::parse($m . '-01')->format('M Y'), $months);

        $this->invoiceTotals = [];
        $this->paymentTotals = [];

        foreach ($months as $m) {
            $periodStart = Carbon::parse($m . '-01')->startOfMonth();
            $periodEnd = Carbon::parse($m . '-01')->endOfMonth();

            $invTotal = Invoice::whereBetween('invoice_date', [$periodStart, $periodEnd])->sum('amount');
            $payTotal = Payment::whereBetween('payment_date', [$periodStart, $periodEnd])->sum('amount_paid');

            $this->invoiceTotals[] = (float) $invTotal;
            $this->paymentTotals[] = (float) $payTotal;
        }

        $totalInvoiced = array_sum($this->invoiceTotals);
        $totalPaid = array_sum($this->paymentTotals);
        $outstanding = max(0, $totalInvoiced - $totalPaid);

        $this->summary = [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
        ];

        // Fetch invoice list for the table filtered by the same period and optional tenant search
        $invoicesQuery = Invoice::with(['tenant', 'payments'])
            ->whereBetween('invoice_date', [$start, $end])
            ->orderBy('invoice_date', 'desc');

        if ($this->tenant_search) {
            $term = '%' . $this->tenant_search . '%';
            $invoicesQuery->whereHas('tenant', function ($q) use ($term) {
                $q->where('tenant_name', 'like', $term)
                  ->orWhere('phone_number', 'like', $term);
            });
        }

        $this->invoices = $invoicesQuery->get();
    }

    public function updatedFrom(): void
    {
        $this->buildStats();
    }

    public function updatedTo(): void
    {
        $this->buildStats();
    }

    public function updatedTenantSearch(): void
    {
        $this->buildStats();
    }
}
