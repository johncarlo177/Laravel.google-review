@if ($composer->isAccountCreditBilling())
    @include('blue.sections.pricing.account-credit')
@else
    @include('blue.sections.pricing.plans')
    @include('blue.sections.free-trial-notice')
@endif
