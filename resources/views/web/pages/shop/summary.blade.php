@extends('web.layout.app')
@section('seo_title', 'Shop')
@section('seo_description', 'posters | artprints | postcards and much more')
@section('content')
<div id="shop">
  <basket-summary-view>
    <template>
      <div class="checkout__summary">
        <div>
          <header class="page-header">
            <h2>{{__('page.delivery-type')}}</h2>
          </header>
          <p>
            – {{__('page.delivery-type-' . $data['delivery_type'])}}
            @if ($data['delivery_type'] == 'pickup')
              &nbsp;<span>(Instruktionen zur Abholung folgen per Mail)</span>
            @endif
          </p>
        </div>
        @if ($data['delivery_type'] == 'mail')
          <address>
            <div>
              <header class="page-header">
                <h2>{{__('page.delivery-address')}}</h2>
                <a href="{{ localized_route('page.shop.basket') }}">{{__('page.edit')}}</a>
              </header>
              <p>
                {{$data['firstname']}} {{$data['name']}}<br>
                {{$data['street']}} {{$data['street_no']}}<br>
                {{$data['zip']}} {{$data['city']}}<br>
                {{$data['country']}}
              </p>
              <p>
                {{__('page.email')}}: {{$data['email']}}<br>
                {{__('page.phone')}}: {{$data['phone']}}
              </p>
            </div>
            @if ($data['has_alt_address'])
              <div>
                <header class="page-header">
                  <h2>{{__('page.billing-address')}}</h2>
                  <a href="{{ localized_route('page.shop.basket') }}">{{__('page.edit')}}</a>
                </header>
                <p>
                  {{$data['alt_firstname']}} {{$data['alt_name']}}<br>
                  {{$data['alt_street']}} {{$data['alt_street_no']}}<br>
                  {{$data['alt_zip']}} {{$data['alt_city']}}<br>
                  {{$data['alt_country']}}
                </p>
              </div>
            @endif
          </address>
        @endif
        <h2>{{__('page.payment-method')}}</h2>
        <p>{{__('page.info_payment_method_' . $data['region'])}}</p>
        <form method="POST" action="{{ localized_route('page.shop.confirm') }}">
          @csrf
          @if ($errors->any())
            <x-alert type="danger" message="{{__('messages.general_error')}}" />
          @endif
          <x-select-payment name="payment-type" label="{{__('page.select-payment-method')}} *" region="{{$data['region']}}" userValue="{{ $data['payment-type'] ?? '' }}" />
          <div class="form-group flex-sb">
            <x-button name="submit" type="submit" label="{{__('page.finish-order')}}" btnClass="btn-primary" />
            <a href="{{ localized_route('page.shop.basket') }}" class="form-helper" title="{{__('page.back-to-basket')}}">{{__('page.back')}}</a>
          </div>
        </form>
      </div>      
    </template>
  </basket-summary-view>
</div>
@endsection