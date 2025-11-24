<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Interfaces\ModelSearchBuilder;
use App\Models\Domain;
use App\Support\DomainManager;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    private DomainManager $domainManager;

    public function __construct(DomainManager $domainManager)
    {
        $this->domainManager = $domainManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModelSearchBuilder $search, Request $request)
    {
        return $search->init(Domain::class, $request, false)
            ->inColumns([
                'host',
                'protocol',
                'user.name',
                'user.email'
            ])
            ->withQuery(function ($query) use ($request) {
                $query->with('user');

                $query->orderBy('sort_order', 'asc');

                if ($request->has('status') && $request->status && $request->status != 'all') {
                    $query->whereStatus($request->status);
                }
            })
            ->search()
            ->paginate();
    }

    public function usableDomains(Request $request)
    {
        return $this->domainManager->getUsableDomains($request->user());
    }

    public function myDomains(Request $request)
    {
        return Domain::where('user_id', $request->user()->id)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDomainRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDomainRequest $request)
    {
        return $this->domainManager->store($request->all(), $request->user());
    }

    public function checkConnectivity(Domain $domain)
    {
        $applicationIsAccessible = $this->domainManager->applicationIsAccessible($domain);

        $dnsIsConfiguredResult = $this->domainManager->dnsIsConfigured($domain->host);

        $dnsIsConfigured = $dnsIsConfiguredResult['success'];

        $dnsCurrentValue = $dnsIsConfiguredResult['currentValue'];

        $success = $applicationIsAccessible && $dnsIsConfigured;

        return compact('success', 'dnsIsConfigured', 'dnsCurrentValue', 'applicationIsAccessible');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function show(Domain $domain)
    {
        return $domain;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDomainRequest  $request
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        return $this->domainManager->update($request->all(), $domain, request()->user());
    }

    public function updateStatus(Request $request, Domain $domain)
    {
        $status = $request->status;

        $this->authorize('updateStatus', [$domain, $status]);

        return $this->domainManager->updateStatus($domain, $status);
    }

    public function updateAvailability(Request $request, Domain $domain)
    {
        $availability = $request->availability;

        $this->authorize('updateAvailability', [$domain, $availability]);

        return $this->domainManager->updateAvailability($domain, $availability);
    }

    public function setDefaultDomain(Domain $domain)
    {
        return $this->domainManager->setDefault($domain);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();

        return $domain;
    }
}
