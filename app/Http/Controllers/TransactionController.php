<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\Transaction;
use App\Utils\ExchangeRateApi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Collection $transactions */
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
                        return $query->where('amount', 'like', '%' . $this->escapeLike($request->input('filter_value')) . '%');
                    elseif ($request->has('description'))
                        return $query->where('description', 'like', '%' . $this->escapeLike($request->input('filter_value')) . '%');
                    elseif ($request->has('currency'))
                        return $query->where('currency', 'like', '%' . $this->escapeLike($request->input('filter_value')) . '%');
                    elseif ($request->has('process_date'))
                        return $query->where('process_date', 'like', '%' . $this->escapeLike($request->input('filter_value')) . '%');
                    elseif ($request->has('category_id')) {
                        return $query->whereHas('category', function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $this->escapeLike($request->input('filter_value')) . '%');
                        });
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
            'net_amount' => $this->calculateAmount($transactions)
        ]);
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

        $message = 'There is no recorded transaction with this id';
        if (count($transaction) > 0)
            $message = 'Transaction transferred this page successfully.';


        return response()->json([
            'success' => true,
            'transactionToUpdate' => $transaction,
            'message' => $message
        ]);

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
            'message' => 'Transaction updated successfully . '
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
        $deleted = Transaction::where('user_id', '=', $this->getAuthenticatedUserId())->where('id', '=', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully . ',
            'transactionId' => $id
        ]);
    }

    /**
     * @return mixed
     */
    private function getAuthenticatedUserId()
    {
        return auth('api')->user()->id;
    }

    /**
     * @param Collection $transactions
     * @return mixed
     */
    private function calculateAmount(Collection $transactions)
    {
        $exchangeRateApi = new ExchangeRateApi();
        return $transactions->map(function ($transaction) use ($exchangeRateApi) {
            return ($transaction->amount * $exchangeRateApi->getExchangeRate($transaction->currency)) * ($transaction->category->is_income ? 1 : -1);
        })->sum();
    }

    /**
     * @param string $value
     * @param string $char
     * @return array|string|string[]
     */
    private function escapeLike(string $value, string $char = '\\')
    {
        return str_replace(
            [$char, '%', '_'],
            [$char . $char, $char . ' % ', $char . '_'],
            $value
        );
    }
}
