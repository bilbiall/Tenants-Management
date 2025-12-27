<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Carbon\Carbon;

use App\Models\Tenant;
use App\Models\House;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ActivityLog;


class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-s-home';

    protected static ?string $navigationLabel = '';

    protected static ?string $title = '';

    protected static string $view = 'filament.pages.dashboard';

    //dashboard view
    public $totalTenants;
    public $newTenants;
    public $totalHouses;
    public $vacantHouses;
    public $occupiedHouses;
    public $totalInvoices;
    public $paidInvoices;
    public $unpaidInvoices;
    public $partialInvoices;
    public $totalPayments;
    public $totalRevenue;
    public $recentPayments;
    public $occupancyRate;
    public $monthlyRevenueData;
    public $invoiceStatusData;
    public $activityTrendData;
    public $newTenantsThisMonth;
    public $outstandingBalance;

    public function mount(): void
    {
        // Total & New Tenants
        $this->totalTenants = Tenant::count();
        $this->newTenants = Tenant::whereMonth('created_at', now()->month)->count();
        $this->newTenantsThisMonth = Tenant::whereDate('created_at', '>=', now()->startOfMonth())->count();

        // House Stats
        $this->totalHouses = House::count();
        $this->vacantHouses = House::where('house_status', 'Vacant')->count();
        $this->occupiedHouses = House::where('house_status', 'Occupied')->count();
        $this->occupancyRate = $this->totalHouses > 0 ? round(($this->occupiedHouses / $this->totalHouses) * 100) : 0;

        // Invoice Stats
        $this->totalInvoices = Invoice::count();
        $this->paidInvoices = Invoice::where('status', 'paid')->count();
        $this->unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $this->partialInvoices = Invoice::where('status', 'partial')->count();

        // Outstanding Balance
        $this->outstandingBalance = Invoice::where('status', '!=', 'paid')
            ->sum('balance');

        // Payment Stats
        $this->totalPayments = Payment::whereMonth('created_at', now()->month)->sum('amount_paid');
        $this->totalRevenue = Payment::sum('amount_paid');
        $this->recentPayments = Payment::latest()->take(8)->get();

        // Charts Data
        $this->monthlyRevenueData = $this->getMonthlyRevenueData();
        $this->invoiceStatusData = $this->getInvoiceStatusData();
        $this->activityTrendData = $this->getActivityTrendData();
    }

    private function getMonthlyRevenueData()
    {
        $months = [];
        $revenues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            $revenues[] = Payment::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount_paid');
        }

        return [
            'months' => $months,
            'revenues' => $revenues,
        ];
    }

    private function getInvoiceStatusData()
    {
        return [
            'paid' => $this->paidInvoices,
            'unpaid' => $this->unpaidInvoices,
            'partial' => $this->partialInvoices,
        ];
    }

    private function getActivityTrendData()
    {
        $days = [];
        $activities = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');
            $activities[] = ActivityLog::whereDate('created_at', $date)->count();
        }

        return [
            'days' => $days,
            'activities' => $activities,
        ];
    }

}
