<footer class="site-footer">
  <div>
    <nav class="site-menu-footer">
      <ul>
        <li>
        <a href="{{ localized_route('page.contact') }}">{{__('Kontakt')}}</a>
        </li>
        @include('web.partials.menu.languages')
      </ul>
    </nav>
  </div>
</footer>
<script src="{{ mix('assets/js/app.js') }}" type="text/javascript"></script>
</body>
<!-- made with ❤ by marceli.to & bivgrafik.ch -->
</html>