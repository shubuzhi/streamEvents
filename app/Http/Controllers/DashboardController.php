<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Follower;
use App\Models\MerchSale;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $followers = Follower::query()
            ->select(['name as name', 'read', 'created_at', DB::raw("'follower' as source")])
            ->where('user_id', $request->user()->id);
        $subscribers = Subscriber::query()
            ->select([DB::raw("CONCAT(name, ' (Tier', subscription_tier, ') subscribed to you!') as name"), 'read', 'created_at', DB::raw("'subscriber' as source")])
            ->where('user_id', $request->user()->id);
        $donations = Donation::query()
            ->select([DB::raw("CONCAT(followers.name, ' donated ', donations.amount, ' ', donations.currency, ' to you! ', '<br>', donations.donation_message) as name"), 'donations.read as read', 'donations.created_at as created_at', DB::raw("'donation' as source")])
            ->join('followers', 'donations.follower_id', '=', 'followers.id')
            ->where('donations.user_id', $request->user()->id);
        $merchSales = MerchSale::query()
            ->select([DB::raw("CONCAT(followers.name, ' bought ', merch_sales.item_name, ' from you for ', merch_sales.amount * merch_sales.price, ' CAD!') as name"), 'merch_sales.read as read', 'merch_sales.created_at as created_at', DB::raw("'merch_sale' as source")])
            ->join('followers', 'merch_sales.follower_id', '=', 'followers.id')
            ->where('merch_sales.user_id', $request->user()->id);

        $list = $followers
            ->unionAll($subscribers)
            ->unionAll($donations)
            ->unionAll($merchSales)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();

        $donationTotal = Donation::query()->where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('user_id', $request->user()->id)
            ->sum('amount');
        $subscriptionTotal = Subscriber::query()->where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('user_id', $request->user()->id)
            ->sum(DB::raw(("CASE
                WHEN subscription_tier = 1 THEN 5
                WHEN subscription_tier = 2 THEN 10
                WHEN subscription_tier = 3 THEN 15
                ELSE 0
            END")));
        $merchSaleTotal = MerchSale::query()->where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('user_id', $request->user()->id)
            ->sum(DB::raw('amount * price'));

        $totalRevenue = $donationTotal + $subscriptionTotal + $merchSaleTotal;

        $followersGained = Follower::query()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->where('user_id', $request->user()->id)
            ->count();

        $topThreeItems = MerchSale::query()
            ->select(['item_name', DB::raw("SUM(amount * price) as total_sales")])
            ->where('user_id', $request->user()->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('item_name')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get()
            ->toArray();

        $data = [
            'user_id'      => $request->user()->id,
            'list'         => $list,
            'totalRevenue' => $totalRevenue,
            'followers'    => $followersGained,
            'topThree'     => $topThreeItems
        ];

        return view('dashboard', ['data' => $data]);
    }
}
