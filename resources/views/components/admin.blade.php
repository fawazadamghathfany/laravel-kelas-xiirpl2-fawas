@extends('templates.index')

@section('content')
  <div class="container">
    <h2>Admin Dashboard</h2>
    <p>Selamat datang, {{ Auth::user()->name }}.</p>
  </div>
@endsection
