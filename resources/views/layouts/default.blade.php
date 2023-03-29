<!DOCTYPE html>
<html>
  <head>
    <title>@yield('title', 'Weibo App')</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" >
  </head>
  <body>
    @include('layouts._header')

    <div class="container">
       @yield('content')
    </div>

    @include('layouts._footer')

  </body>
</html>
