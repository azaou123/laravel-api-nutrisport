<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Site;
use App\Mail\DailyReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailySalesReport extends Command
{
    protected $signature = 'report:daily-sales';
    protected $description = 'Send daily sales report to admin';

    public function handle()
    {
        $adminEmail = config('mail.admin_address') ?: env('MAIL_ADMIN');
        if (empty($adminEmail)) {
            $this->error('Admin email not configured. Set MAIL_ADMIN in .env or define mail.admin_address in config/mail.php');
            return 1;
        }

        $yesterday = now()->subDay()->toDateString();

        // 2. Fetch orders from yesterday
        $orders = Order::whereDate('created_at', $yesterday)->get();

        // 3. If no orders, send empty report and exit
        if ($orders->isEmpty()) {
            $this->info('No orders yesterday. Sending empty report.');
            $report = $this->buildEmptyReport();
            Mail::to($adminEmail)->send(new DailyReportMail($report));
            return 0;
        }

        // 4. Calculate statistics
        $orderIds = $orders->pluck('id');
        $items = OrderItem::with('product')
            ->whereIn('order_id', $orderIds)
            ->get();

        // Group by product
        $productStats = $items->groupBy('product_id')->map(function ($productItems) {
            $quantity = $productItems->sum('quantity');
            $revenue = $productItems->sum('total');
            $name = $productItems->first()->product->name;
            return [
                'product_id' => $productItems->first()->product_id,
                'name' => $name,
                'quantity' => $quantity,
                'revenue' => $revenue,
            ];
        })->values();

        $bestSelling = $productStats->sortByDesc('quantity')->first();
        $worstSelling = $productStats->sortBy('quantity')->first();

        $maxRevenue = $productStats->sortByDesc('revenue')->first();
        $minRevenue = $productStats->sortBy('revenue')->first();

        $revenuePerSite = $orders->groupBy('site_id')->map(function ($siteOrders) {
            return $siteOrders->sum('total');
        });

        $sites = Site::all()->keyBy('id');
        $revenuePerSiteNamed = [];
        foreach ($revenuePerSite as $siteId => $total) {
            $revenuePerSiteNamed[$sites[$siteId]->name ?? "Site $siteId"] = $total;
        }

        $report = [
            'date' => $yesterday,
            'best_selling' => $bestSelling,
            'worst_selling' => $worstSelling,
            'max_revenue' => $maxRevenue,
            'min_revenue' => $minRevenue,
            'revenue_per_site' => $revenuePerSiteNamed,
        ];

        Mail::to($adminEmail)->send(new DailyReportMail($report));

        $this->info('Daily sales report sent successfully.');
        return 0;
    }

    private function buildEmptyReport()
    {
        return [
            'date' => now()->subDay()->toDateString(),
            'best_selling' => null,
            'worst_selling' => null,
            'max_revenue' => null,
            'min_revenue' => null,
            'revenue_per_site' => [],
        ];
    }
}