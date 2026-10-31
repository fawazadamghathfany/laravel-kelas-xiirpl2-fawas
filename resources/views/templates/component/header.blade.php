<div class="header">
  <div class="container">
    <div class="w3layouts_logo">
      <a href="{{ route('home') }}"><h1>One<span>Movies</span></h1></a>
    </div>
    <div class="w3_search">
      <form action="{{ route('home') }}" method="get">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search" required="">
        <input type="submit" value="Go">
      </form>
    </div>
    <div class="w3l_sign_in_register">
      <ul>
        @auth
          <li>{{ Auth::user()->name }}</li>
          @if (Auth::user()->role === 'admin')
            <li><a href="{{ route('admin') }}">Admin</a></li>
          @endif
          <li>
            <form action="{{ route('logout') }}" method="post">
              @csrf
              <button type="submit" class="btn btn-link">Logout</button>
            </form>
          </li>
        @else
          <li><a href="#" data-toggle="modal" data-target="#myModal" data-auth-form="login">Login</a></li>
        @endauth
      </ul>
    </div>
    <div class="clearfix"> </div>
  </div>
</div>