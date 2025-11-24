<form method="POST" style="display: none" action="{{ url('/whatsapp-order/place-order') }}" id="whatsapp-order-form">
    @csrf
    <input name="destination" />
    <input name="slug" />
</form>