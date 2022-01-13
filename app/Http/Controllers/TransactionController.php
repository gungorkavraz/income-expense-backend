<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', '=', $this->getAuthenticatedUserId())
            ->when($request->has('process_date_desc'),
                function ($query) {
                    return $query->orderByRaw('process_date DESC');
                }
            )->when($request->has('process_date_inc'),
                function ($query) {
                    return $query->orderByRaw('process_date');
                }
            )->when($request->has('amount_desc'),
                function ($query) {
                    return $query->orderByRaw('amount DESC');
                }
            )->when($request->has('amount_inc'),
                function ($query) {
                    return $query->orderByRaw('amount');
                }
            )->when($request->hasAny(['amount', 'description', 'currency', 'process_date', 'category_id']),
                function ($query) use ($request) {
                    if ($request->has('amount'))
                        return $query->where('amount', 'like', '%' . $request->input('filter_value') . '%');
                    elseif ($request->has('description'))
                        return $query->where('description', 'like', '%' . $request->input('filter_value') . '%');
                    elseif ($request->has('currency'))
                        return $query->where('currency', 'like', '%' . $request->input('filter_value') . '%');
                    elseif ($request->has('process_date'))
                        return $query->where('process_date', 'like', '%' . $request->input('filter_value') . '%');
                    elseif ($request->has('category_id')) {
                        return $query;
                    }

                    return $query;
                }
            )->when($request->has('first_date'),
                function ($query) use ($request) {
                    error_log('first_date');
                    $from = date($request->input('first_date'));
                    $to = date($request->input('last_date'));
                    return $query->whereBetween('process_date', [$from, $to])->orderByRaw('process_date DESC');
                }
            )->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTransactionRequest $request
     * @return JsonResponse
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {

        $data = $request->all();
        $data['user_id'] = $this->getAuthenticatedUserId();

        $transaction = Transaction::create($data);

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
            'message' => 'Transaction created successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $transaction = Transaction::where('user_id', '=', $this->getAuthenticatedUserId())
            ->where('id', '=', $id)
            ->get();

        return response()->json([
            'success' => true,
            'transactionToUpdate' => $transaction,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        error_log($id);
        $data = $request->all();

        $transaction = Transaction::where('user_id', '=', $this->getAuthenticatedUserId())->find($id);
        $transaction->category_id = $data['category_id'];
        $transaction->currency = $data['currency'];
        $transaction->amount = $data['amount'];
        $transaction->description = $data['description'];
        $transaction->process_date = $data['process_date'];
        $transaction->save();


        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Transaction updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $deleted = Transaction::where('id', '=', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully.',
        ]);
    }

    private function getAuthenticatedUserId()
    {
        return auth('api')->user()->id;
    }
}
