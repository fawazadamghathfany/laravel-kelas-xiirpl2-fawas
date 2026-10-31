<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminFilmController extends Controller
{
    public function index(Request $request): View
    {
        $films = Film::with('genre')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('components.admin.films.index', compact('films'));
    }

    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('components.admin.films.form', [
            'film' => new Film,
            'genres' => $genres,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Film::create($validated);

        return redirect()->route('admin')->with('status', 'Film berhasil ditambahkan.');
    }

    public function edit(Film $film): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('components.admin.films.form', compact('film', 'genres'));
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $film->update($validated);

        return redirect()->route('admin')->with('status', 'Film berhasil diperbarui.');
    }

    public function destroy(Film $film): RedirectResponse
    {
        DB::transaction(function () use ($film): void {
            $film->perans()->delete();
            $film->kritiks()->delete();
            $film->delete();
        });

        return redirect()->route('admin')->with('status', 'Film dan data terkait berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'sinopsis' => ['required', 'string', 'max:10000'],
            'year' => ['required', 'integer', 'between:1888,2100'],
            'poster' => ['required', 'string', 'max:2048'],
            'genre_id' => ['required', 'integer', 'exists:genres,id'],
        ];
    }
}
