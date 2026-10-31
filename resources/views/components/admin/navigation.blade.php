<nav class="admin-sidebar" aria-label="Navigasi admin">
  <h3>Admin</h3>
  <a href="{{ route('admin') }}">Kelola Film</a>
  <a href="{{ route('home') }}">Lihat Situs</a>
  <form action="{{ route('logout') }}" method="post">
    @csrf
    <button class="btn btn-link" type="submit" style="padding-left: 0;">Logout</button>
  </form>
</nav>
