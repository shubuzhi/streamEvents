<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Follower;
use App\Models\MerchSale;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->select([DB::raw("CONCAT(followers.name, ' donated ', donations.amount, ' ', donations.currency, ' to you!') as name"), 'donations.read as read', 'donations.created_at as created_at', DB::raw("'donation' as source")])
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

        return view('dashboard');
    }
}
