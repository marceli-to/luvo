@extends('web.layout.app')
@section('seo_title', 'Passwort bestätigen')
@section('content')
<section class="content-auth">
  <div>
    <article>
      <h1>{{ __('Confirm Password') }}</h1>
      <div>
        <p>{{ __('Please confirm your password before continuing.') }}</p>
        @if ($errors->any())
          <x-alert type="danger" message="{{__('messages.general_error')}}" />
        @endif
        @error('password')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
        @enderror
        <form method="POST" action="{{ route('password.confirm') }}">
          @csrf
          <div class="form-group">
            <label>{{ __('Password') }}</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
          </div>
          <div class="form-buttons align-end">
            @if (Route::has('password.request'))
              <a class="btn btn-link" href="{{ route('password.request') }}">
                {{ __('Forgot Your Password?') }}
              </a>
            @endif
            <x-button label="{{ __('Confirm Password') }}" name="reset_password" btnClass="btn-primary js-btn-loader" type="submit" />
          </div>
        </form>
      </div>
    </article>
  </div>
</section>
@endsection
