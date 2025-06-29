<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use App\Models\Tenant;
use App\Models\House;
use App\Models\Invoice;
use App\Models\Payment;


class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-s-home';

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
    public $recentPayments;

    public function mount(): void
    {
        $this->totalTenants = Tenant::count();
        $this->newTenants = Tenant::whereMonth('created_at', now()->month)->count();

        $this->totalHouses = House::count();
        $this->vacantHouses = House::where('house_status', 'Vacant')->count();
        $this->occupiedHouses = House::where('house_status', 'Occupied')->count();

        $this->totalInvoices = Invoice::count();
        $this->paidInvoices = Invoice::where('status', 'paid')->count();
        $this->unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $this->partialInvoices = Invoice::where('status', 'partial')->count();

        $this->totalPayments = Payment::whereMonth('created_at', now()->month)->sum('amount_paid');
        $this->recentPayments = Payment::latest()->take(5)->get();
    }

}
