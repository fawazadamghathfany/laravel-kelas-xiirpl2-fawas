@extends('templates.index')

@push('style')
  <style>
    .admin-dashboard { margin: 30px auto; }
    .admin-sidebar { background: #f5f5f5; border: 1px solid #ddd; padding: 18px; }
    .admin-sidebar a { display: block; padding: 8px 0; }
    .admin-content { overflow-x: auto; }
    .admin-actions { display: flex; gap: 6px; align-items: center; }
    .admin-actions form { margin: 0; }
    .admin-poster { width: 48px; height: 68px; object-fit: cover; }
    @media (max-width: 767px) {
      .admin-sidebar { margin-bottom: 20px; }
      .admin-actions { min-width: 150px; }
    }
  </style>
@endpush

@section('content')
  <div class="container admin-dashboard">
    <div class="row">
      <aside class="col-sm-3">
        @include('components.admin.navigation')
      </aside>
      <main class="col-sm-9 admin-content">
        <div class="clearfix">
          <h2 class="pull-left">Kelola Film</h2>
          <a class="btn btn-primary pull-right" href="{{ route('admin.films.create') }}">Tambah Film</a>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="form-inline" action="{{ route('admin') }}" method="get" style="margin: 15px 0;">
          <label class="sr-only" for="film-search">Cari film</label>
          <input class="form-control" id="film-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul atau tahun">
          <button class="btn btn-default" type="submit">Cari</button>
          @if (request()->filled('search'))
            <a class="btn btn-link" href="{{ route('admin') }}">Reset</a>
          @endif
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Poster</th>
                <th>Judul</th>
                <th>Tahun</th>
                <th>Genre</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($films as $film)
                <tr>
                  <td><img class="admin-poster" src="{{ asset($film->poster) }}" alt="Poster {{ $film->title }}"></td>
                  <td>{{ $film->title }}</td>
                  <td>{{ $film->year }}</td>
                  <td>{{ $film->genre?->name ?? 'Genre tidak tersedia' }}</td>
                  <td>
                    <div class="admin-actions">
                      <a class="btn btn-xs btn-default" href="{{ route('movies.show', $film) }}">Lihat</a>
                      <a class="btn btn-xs btn-primary" href="{{ route('admin.films.edit', $film) }}">Edit</a>
                      <form action="{{ route('admin.films.destroy', $film) }}" method="post" onsubmit="return confirm('Hapus film ini beserta komentar dan data cast terkait?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-xs btn-danger" type="submit">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5">Film tidak ditemukan.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $films->links() }}
      </main>
    </div>
  </div>
@endsection
