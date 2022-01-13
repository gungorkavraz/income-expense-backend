<?php

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ExchangeRateApi
{
    /**
     * @return Collection
     */
    private function callApi(): Collection
    {
        $start_date = Carbon::now()->format('d-m-Y');
        $end_date = Carbon::now()->format('d-m-Y');
        $response = Http::get('https://evds2.tcmb.gov.tr/service/evds/series=TP.DK.USD.A-TP.DK.EUR.A&startDate=' . $start_date . '&endDate=' . $end_date . '&type=json&key=2W4FSUYLnO')->json();
        $collection = collect($response);

        return $collection;
    }

    /**
     * @return mixed
     */
    function getExchangeRate($currency)
    {
        if ($currency === 'TRY') {
            return 1;
        } else {
            return collect($this->callApi()
                ->
                get('items')[0])->get('TP_DK_' . $currency . '_A');
        }
    }
}
