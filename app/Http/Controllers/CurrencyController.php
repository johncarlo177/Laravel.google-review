<?php

namespace App\Http\Controllers;

use App\Events\CurrencyEnabled;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Interfaces\CurrencyManager;
use App\Interfaces\ModelSearchBuilder;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{

    private CurrencyManager $currencies;

    public function __construct(CurrencyManager $currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ModelSearchBuilder $search)
    {
        return $search
            ->init(Currency::class, $request)
            ->inColumns(['name', 'symbol', 'currency_code'])
            ->search()
            ->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCurrencyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCurrencyRequest $request)
    {
        return $this->currencies->store($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function show(Currency $currency)
    {
        return $currency;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCurrencyRequest  $request
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        return $this->currencies->update($currency, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        return $this->currencies->delete($currency);
    }

    public function enableCurrency(Currency $currency, Request $request)
    {
        $currency = $this->currencies->enable($currency);

        event(new CurrencyEnabled($currency));

        return $currency;
    }
}
