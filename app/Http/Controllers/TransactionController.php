<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $transactions = DB::table('transactions')
            ->where('user_id', '=', $this->getAuthenticatedUserId())
            ->get();
        //->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    public function sortByAmount(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        error_log($sort);
        $transactions = DB::table('transactions')
            ->where('user_id', '=', $this->getAuthenticatedUserId())
            ->orderByRaw('amount DESC')
            ->get();
        //->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    public function sortByDate(Request $request): JsonResponse
    {
        $transactions = DB::table('transactions')
            ->where('user_id', '=', $this->getAuthenticatedUserId())
            ->orderByRaw('process_date DESC')
            ->get();
        //->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    public function filterByValue(Request $request)
    {
        $filterBy = $request->input('filter_by');
        $filter = $request->input('filter_value');
        $transactions = DB::table('transactions')
            ->where('user_id', '=', $this->getAuthenticatedUserId())
            ->where($filterBy, 'like', '%' . $filter . '%')
            ->get();

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
        $transaction = DB::table('transactions')
            ->where('user_id', '=', $this->getAuthenticatedUserId())
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
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {

        error_log('up2342date');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $deleted = DB::table('transactions')
            ->where('id', '=', $id)
            ->delete();

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
