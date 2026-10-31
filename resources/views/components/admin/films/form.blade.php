@extends('templates.index')

@push('style')
  <style>
    .admin-dashboard { margin: 30px auto; }
    .admin-sidebar { background: #f5f5f5; border: 1px solid #ddd; padding: 18px; }
    .admin-sidebar a { display: block; padding: 8px 0; }
    .admin-form { background: #fff; border: 1px solid #ddd; padding: 22px; }
    @media (max-width: 767px) {
      .admin-sidebar { margin-bottom: 20px; }
    }
  </style>
@endpush

@section('content')
  <div class="container admin-dashboard">
    <div class="row">
      <aside class="col-sm-3">
        @include('components.admin.navigation')
      </aside>
      <main class="col-sm-9">
        <h2>{{ $film->exists ? 'Edit Film' : 'Tambah Film' }}</h2>

        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="admin-form" action="{{ $film->exists ? route('admin.films.update', $film) : route('admin.films.store') }}" method="post">
          @csrf
          @if ($film->exists)
            @method('PUT')
          @endif

          <div class="form-group">
            <label for="title">Judul film</label>
            <input class="form-control" id="title" name="title" value="{{ old('title', $film->title) }}" maxlength="255" required>
          </div>

          <div class="form-group">
            <label for="sinopsis">Sinopsis</label>
            <textarea class="form-control" id="sinopsis" name="sinopsis" rows="5" maxlength="10000" required>{{ old('sinopsis', $film->sinopsis) }}</textarea>
          </div>

          <div class="form-group">
            <label for="year">Tahun</label>
            <input class="form-control" id="year" type="number" name="year" value="{{ old('year', $film->year) }}" min="1888" max="2100" required>
          </div>

          <div class="form-group">
            <label for="genre_id">Genre</label>
            <select class="form-control" id="genre_id" name="genre_id" required>
              <option value="">Pilih genre</option>
              @foreach ($genres as $genre)
                <option value="{{ $genre->id }}" @selected((string) old('genre_id', $film->genre_id) === (string) $genre->id)>{{ $genre->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="poster">Path atau URL poster</label>
            <input class="form-control" id="poster" name="poster" value="{{ old('poster', $film->poster) }}" maxlength="2048" placeholder="storage/images/nama-file.jpg atau https://..." required>
          </div>

          <button class="btn btn-primary" type="submit">{{ $film->exists ? 'Simpan Perubahan' : 'Tambah Film' }}</button>
          <a class="btn btn-default" href="{{ route('admin') }}">Batal</a>
        </form>
      </main>
    </div>
  </div>
@endsection
