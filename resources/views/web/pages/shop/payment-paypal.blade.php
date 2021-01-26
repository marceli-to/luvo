@extends('web.layout.app')
@section('seo_title', 'Shop')
@section('seo_description', 'posters | artprints | postcards and much more')
@section('content')
<section class="shop">
  <div>
    <div class="checkout">
      <div class="checkout__card-payment">
        <h2>{{__('page.confirm-payment-by-paypal')}}</h2>
        <p>{{__('page.paypal-transaction-window')}}</p>
        <div class="payment-buttons" id="paypal-button-container"></div>
      </div>
    </div>
  </div>
</section>
<script>
  paypal.Buttons({
    createOrder: function(data, actions) {
      return actions.order.create({
        purchase_units: [{
          amount: {
            value: {{$total}}
          }
        }]
      });
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        alert('Transaction completed by ' + details.payer.name.given_name);
      });
    }
  }).render('#paypal-button-container');
</script>
@endsection