<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardUpdateReadStatusRequest;
use App\Models\Donation;
use App\Models\Follower;
use App\Models\MerchSale;
use App\Models\Subscriber;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index(Request $request)
    {
        try {
            $followers = Follower::query()
                ->select(['id', DB::raw("CONCAT(name, ' followed you!') as event"), 'read', 'created_at', DB::raw("'follower' as source")])
                ->where('user_id', $request->user()->id);
            $subscribers = Subscriber::query()
                ->select(['id', DB::raw("CONCAT(name, ' (Tier', subscription_tier, ') subscribed to you!') as event"), 'read', 'created_at', DB::raw("'subscriber' as source")])
                ->where('user_id', $request->user()->id);
            $donations = Donation::query()
                ->select(['donations.id as id', DB::raw("CONCAT(followers.name, ' donated ', donations.amount, ' ', donations.currency, ' to you! ', '<br>', donations.donation_message) as event"), 'donations.read as read', 'donations.created_at as created_at', DB::raw("'donation' as source")])
                ->join('followers', 'donations.follower_id', '=', 'followers.id')
                ->where('donations.user_id', $request->user()->id);
            $merchSales = MerchSale::query()
                ->select(['merch_sales.id as id', DB::raw("CONCAT(followers.name, ' bought ', merch_sales.item_name, ' from you for ', merch_sales.amount * merch_sales.price, ' CAD!') as event"), 'merch_sales.read as read', 'merch_sales.created_at as created_at', DB::raw("'merch_sale' as source")])
                ->join('followers', 'merch_sales.follower_id', '=', 'followers.id')
                ->where('merch_sales.user_id', $request->user()->id);

            $list = $followers
                ->unionAll($subscribers)
                ->unionAll($donations)
                ->unionAll($merchSales)
                ->orderBy('created_at', 'desc')
                ->paginate(100)
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

            if ($request->ajax()) {
                return view('pagination', ['list' => $list]);
            }

            return view('dashboard', ['data' => $data]);

            //Ideally this function should be pure get api function and return following,
            //for this project I'm using above only to make front end simpler

//            $combinedQuery = $followers
//                ->unionAll($subscribers)
//                ->unionAll($donations)
//                ->unionAll($merchSales)
//                ->orderBy('created_at', 'desc');
//
//            $paginator  = $combinedQuery->paginate(100);
//
//            return fractal()
//                ->collection($paginator->items())
//                ->transformWith(new ListTransformer())
//                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
//                ->toArray();

        } catch (\Exception $e) {
            return view('login'); //here should return error page normally
        }
    }

    /**
     * @param DashboardUpdateReadStatusRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReadStatus(DashboardUpdateReadStatusRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            switch ($request->input('source')) {
                case 'follower':
                    Follower::query()->where('id', $request->input('entry_id'))->update(['read' => !$request->input('status')]);
                    break;
                case 'subscriber':
                    Subscriber::query()->where('id', $request->input('entry_id'))->update(['read' => !$request->input('status')]);
                    break;
                case 'donation':
                    Donation::query()->where('id', $request->input('entry_id'))->update(['read' => !$request->input('status')]);
                    break;
                case 'merch_sale':
                    MerchSale::query()->where('id', $request->input('entry_id'))->update(['read' => !$request->input('status')]);
                    break;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return \response()->json(['error' => $e->getMessage()], 422);
        }

        return \response()->json('succeed', 200);
    }
}
