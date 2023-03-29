@extends('layouts.default')

@section('content')
     <div class="bg-light p-3 p-sm-5 rounded">
      <h1>Hello Laravel</h1>
      <p class="lead">
        你现在所看到的是<a href="#"></a>
      </p>
      <p>一切从这里开始</p>
      <p><a href="{{ route('signup')}}" class="btn btn-lg btn-success" role="button">现在注册</a></p>
     </div>
@endsection
