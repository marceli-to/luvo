@extends('web.layout.app')
@section('seo_title', 'Passwort vergessen')
@section('seo_description', '')
@section('content')
<section class="content-auth">
  <div>
    <article>
      <h1>Passwort vergessen?</h1>
      <p>{{__('messages.password_recovery')}}</p>
      @if ($errors->any())
        <x-alert type="danger" message="{{__('messages.general_error')}}" />
      @endif
      @if (session('status'))
        <x-alert type="success" message="{{ session('status') }}" />
      @endif
      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-text-field label="E-Mail" type="email" name="email" />
        <div class="form-buttons align-end">
          <x-button label="Absenden" name="register" btnClass="btn-primary js-btn-loader" type="submit" />
        </div>
      </form>
    </article>
  </div>
</section>
@endsection