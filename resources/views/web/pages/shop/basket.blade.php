@extends('web.layout.app')
@section('seo_title', 'Shop')
@section('seo_description', 'posters | artprints | postcards and much more')
@section('content')
<div id="shop">
  <basket-view>
    <template>
      <div class="checkout__form">
        @if ($errors->any())
          <x-alert type="danger" message="{{__('messages.general_error')}}" />
        @endif
        <form action="{{ localized_route('page.shop.summary') }}" method="POST">
          @csrf
          <input type="hidden" name="has_alt_address" value="{{ old('has_alt_address') || (isset($data['has_alt_address']) && $data['has_alt_address']) ? 1 : 0 }}">
          <fieldset>
            <header class="page-header">
              <h2>{{__('page.contact-info')}}</h2>
            </header>
            <div class="form-grid">
              <x-text-field name="firstname" label="{{__('page.firstname')}}" :required="true" userValue="{{ $data['firstname'] ?? '' }}" />
              <x-text-field name="name" label="{{__('page.name')}}" :required="true" userValue="{{ $data['name'] ?? '' }}" />
            </div>
            <div class="form-grid">
              <x-text-field name="phone" label="{{__('page.phone')}}" :required="true" userValue="{{ $data['phone'] ?? '' }}" />
              <x-text-field name="email" label="{{__('page.email')}}" :required="true" userValue="{{ $data['email'] ?? '' }}" />
            </div>
          </fieldset>
          <fieldset>
            <header class="page-header">
              <h2>{{__('page.delivery-type')}}</h2>
              <x-select-delivery-type name="delivery-type" label="{{__('page.delivery-type')}} *" userValue="{{ $data['delivery-type'] ?? '' }}" /> 
            </header>
          </fieldset>
          <fieldset>
            <header class="page-header">
              <h2>{{__('page.delivery-address')}}</h2>
            </header>
            <div class="form-grid">
              <x-text-field name="street" label="{{__('page.street')}}" :required="true" userValue="{{ $data['street'] ?? '' }}" />
              <x-text-field name="street_no" label="{{__('page.street-number')}}" userValue="{{ $data['street_no'] ?? '' }}" />
            </div>
            <x-text-field name="address_additional" label="{{__('page.additional-address')}}" userValue="{{ $data['address_additional'] ?? '' }}" />
            <div class="form-grid">
              <x-text-field name="zip" label="{{__('page.zip')}}" :required="true" userValue="{{ $data['zip'] ?? '' }}" />
              <x-text-field name="city" label="{{__('page.city')}}" :required="true" userValue="{{ $data['city'] ?? '' }}" />
            </div>
            <x-select-countries name="country_id" label="{{__('page.country')}} *" userValue="{{ $data['country_id'] ?? '' }}" />
          </fieldset>
          <fieldset>
            <header class="page-header">
              <a href="javascript:;" class="btn-toggle {{ old('has_alt_address') || (isset($data['has_alt_address']) && $data['has_alt_address']) ? 'is-on' : 'is-off' }} js-btn-address">
                <span>{{__('page.different-billing')}}?</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-off"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="8" cy="12" r="3"></circle></svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-on"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="16" cy="12" r="3"></circle></svg>
              </a>
            </header>
            <div class="checkout__billing-address js-address" style="display: {{ old('has_alt_address') || (isset($data['has_alt_address']) && $data['has_alt_address']) ? 'block' : 'none' }}">
              <div class="form-grid">
                <x-text-field name="alt_firstname" label="{{__('page.firstname')}} *" userValue="{{ $data['alt_firstname'] ?? '' }}" />
                <x-text-field name="alt_name" label="{{__('page.name')}} *" userValue="{{ $data['alt_name'] ?? '' }}" />
              </div>
              <div class="form-grid">
                <x-text-field name="alt_street" label="{{__('page.street')}} *" userValue="{{ $data['alt_street'] ?? '' }}" />
                <x-text-field name="alt_street_no" label="{{__('page.street-number')}}" userValue="{{ $data['alt_street_no'] ?? '' }}" />
              </div>
              <x-text-field name="alt_address_additional" label="Adresszusatz" userValue="{{ $data['alt_address_additional'] ?? '' }}" />
              <div class="form-grid">
                <x-text-field name="alt_zip" label="{{__('page.zip')}} *" userValue="{{ $data['alt_zip'] ?? '' }}" />
                <x-text-field name="alt_city" label="{{__('page.city')}} *" userValue="{{ $data['alt_city'] ?? '' }}" />
              </div>
              <x-select-countries name="alt_country_id" label="{{__('page.country')}} *" userValue="{{ $data['alt_country_id'] ?? '' }}" /> 
            </div>
          </fieldset>
          <div class="form-group flex-sb">
            <x-button name="submit" type="submit" label="{{__('page.proceed-to-payment')}}" btnClass="btn-primary" />
          </div>
        </form>
      </div>      
    </template>
  </basket-view>
</div>
@endsection