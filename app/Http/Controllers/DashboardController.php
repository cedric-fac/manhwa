<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with statistics.
     */
    public function index()
    {
        $stats = [
            'overview' => $this->getOverviewStats(),
            'reminders' => $this->getReminderStats(),
            'performance' => $this->getPerformanceStats(),
            'recent' => $this->getRecentActivity(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats(): array
    {
        return [
            'total_invoices' => Invoice::count(),
            'total_unpaid' => Invoice::where('status', '!=', InvoiceStatus::PAID)->count(),
            'total_overdue' => Invoice::where('status', InvoiceStatus::OVERDUE)->count(),
            'total_amount_due' => Invoice::where('status', '!=', InvoiceStatus::PAID)
                ->sum('amount_ttc'),
        ];
    }

    /**
     * Get reminder statistics.
     */
    private function getReminderStats(): array
    {
        $reminderStats = DB::table('invoice_reminders')
            ->select('type', DB::raw('count(*) as total'))
            ->where('sent', true)
            ->groupBy('type')
            ->get();

        $byType = [];
        foreach ($reminderStats as $stat) {
            $byType[$stat->type] = $stat->total;
        }

        return [
            'total_sent' => InvoiceReminder::where('sent', true)->count(),
            'total_failed' => InvoiceReminder::whereNotNull('error')->count(),
            'by_type' => $byType,
            'success_rate' => $this->calculateSuccessRate(),
        ];
    }

    /**
     * Get performance statistics.
     */
    private function getPerformanceStats(): array
    {
        // Calculate average days to payment
        $avgDaysToPayment = Invoice::whereNotNull('paid_at')
            ->select(DB::raw('AVG(DATEDIFF(paid_at, created_at)) as avg_days'))
            ->first()
            ->avg_days;

        // Calculate payment rate after reminders
        $paidAfterReminder = Invoice::whereHas('reminders', function ($query) {
            $query->where('sent', true);
        })->where('status', InvoiceStatus::PAID)->count();

        $totalWithReminders = Invoice::whereHas('reminders', function ($query) {
            $query->where('sent', true);
        })->count();

        return [
            'avg_days_to_payment' => round($avgDaysToPayment),
            'payment_rate_after_reminder' => $totalWithReminders > 0 
                ? round(($paidAfterReminder / $totalWithReminders) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get recent activity.
     */
    private function getRecentActivity(): array
    {
        return [
            'recent_reminders' => InvoiceReminder::with(['invoice.client'])
                ->where('sent', true)
                ->latest('sent_at')
                ->take(5)
                ->get(),
            'recent_payments' => Invoice::with('client')
                ->where('status', InvoiceStatus::PAID)
                ->latest('paid_at')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Calculate reminder success rate.
     */
    private function calculateSuccessRate(): float
    {
        $total = InvoiceReminder::count();
        if ($total === 0) {
            return 0;
        }

        $successful = InvoiceReminder::where('sent', true)
            ->whereNull('error')
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
