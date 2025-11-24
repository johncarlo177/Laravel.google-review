<script>
    setTimeout(() => {
        document.querySelectorAll('.confirming').forEach(e => e.remove())
        document.querySelectorAll('.confirmed').forEach(e => e.setAttribute('style', ''))
    }, 10000);
</script>

<h1>{{ t('Thank you') }}</h1>

<p class="confirming">{{ t('We are confirming your payment with PayPal, please wait ...') }}</p>

<p class="confirmed" style="display: none">{{ t('Your payment has been confirmed, you can login now ...') }}</p>

<qrcg-loader class="confirming"></qrcg-loader>

<a class="button primary confirmed" style="display: none" href="{{ url('/account/login') }}">
    {{ t('Login') }}
</a>