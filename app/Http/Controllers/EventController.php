<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\MerchSale;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    protected function getModel($model)
    {
        $models = [
            'Follower' => Follower::class,
            'Subscriber' => Subscriber::class,
            'MerchSale' => MerchSale::class,
            'Donation' => Donation::class,
        ];

        return $models[$model] ?? null;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchEvents(Request $request)
    {
        $limit = $request->get('limit', 50);

        $follwers = Follower::my()->select('id', 'created_at', 'name',
            DB::raw('null as tier'),
            DB::raw('0 as count'),
            DB::raw('null as amount'),
            DB::raw('null as currency'),
            DB::raw('null as item_name'),
            DB::raw('null as price'),
            DB::raw('is_read as is_read'),
            DB::raw('"Follower" as model_name'));

        $subscribers = Subscriber::my()->select('id', 'created_at', 'name',
            DB::raw('tier as tier'),
            DB::raw('0 as count'),
            DB::raw('null as amount'),
            DB::raw('null as currency'),
            DB::raw('null as item_name'),
            DB::raw('null as price'),
            DB::raw('is_read as is_read'),
            DB::raw('"Subscriber" as model_name'));

        $merchsales = MerchSale::my()->select('id', 'created_at',
            DB::raw('buyer_name as name'),
            DB::raw('null as tier'),
            DB::raw('count as count'),
            DB::raw('null as amount'),
            DB::raw('null as currency'),
            DB::raw('item_name as item_name'),
            DB::raw('price as price'),
            DB::raw('is_read as is_read'),
            DB::raw('"MerchSale" as model_name'));

        $donations = Donation::my()->select('id', 'created_at',
            DB::raw('donor_name as name'),
            DB::raw('null as tier'),
            DB::raw('0 as count'),
            DB::raw('amount as amount'),
            DB::raw('currency as currency'),
            DB::raw('null as item_name'),
            DB::raw('null as price'),
            DB::raw('is_read as is_read'),
            DB::raw('"Donation" as model_name'));

        $allEvents = $follwers
        ->union($subscribers)
        ->union($merchsales)
        ->union($donations)
        ->orderBy('created_at', 'desc')
        ->paginate($limit);

        return response()->json($allEvents);
    }

    public function markAsRead(Request $request, $id, $model)
    {
        $modelClass = $this->getModel($model);

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        $record = $modelClass::find($id);

        if (!$record) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $record->is_read = 1;
        $record->save();

        return response()->json(['message' => 'Marked as read']);
    }

    public function getDashboardData(Request $request)
    {
        $cacheKey = "user_" . auth()->id() . "_dashboard_data";

        return Cache::remember($cacheKey, 60, function () {
            $startDate = Carbon::now()->subDays(30);

            $donationRevenue = Donation::my()->where('created_at', '>=', $startDate)
                ->sum('amount');

            $merchSalesRevenue = MerchSale::my()->where('created_at', '>=', $startDate)
                ->sum(DB::raw('merch_sales.price * merch_sales.count'));

            $tierPricing = [
                'tier1' => 5,
                'tier2' => 10,
                'tier3' => 15
            ];

            $subscriberRevenue = Subscriber::my()->where('created_at', '>=', $startDate)
                ->get()
                ->reduce(function ($carry, $subscriber) use ($tierPricing) {
                    return $carry + $tierPricing[(string)$subscriber->tier];
                }, 0);

            $totalRevenue = $donationRevenue + $merchSalesRevenue + $subscriberRevenue;

            $topSellingItems = MerchSale::where('created_at', '>=', $startDate)
                ->select('item_name', DB::raw('SUM(price * count) as total_sales'))
                ->groupBy('item_name')
                ->orderBy('total_sales', 'desc')
                ->take(3)
                ->get();

            $totalFollowers = Follower::my()->where('created_at', '>=', $startDate)
                ->get()
                ->count();

            return [
                'totalRevenue' => $totalRevenue,
                'totalFollowers' => $totalFollowers,
                'topSellingItems' => $topSellingItems,
            ];
        });
    }
}
