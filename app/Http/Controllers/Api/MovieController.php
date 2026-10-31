<?php

namespace App\Http\Controllers\Api;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Interfaces\MovieRepositoryInterface;
use App\Models\Film;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    private MovieRepositoryInterface $movieRepositoryInterface;

    public function __construct(MovieRepositoryInterface $movieRepositoryInterface)
    {
        $this->movieRepositoryInterface = $movieRepositoryInterface;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->movieRepositoryInterface->index();

        return ApiResponseClass::sendResponse(MovieResource::collection($data), '', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $movie = $this->movieRepositoryInterface->create($request->validate($this->rules()));

        return ApiResponseClass::sendResponse(new MovieResource($movie), 'Movie created.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Film $movie)
    {
        return ApiResponseClass::sendResponse(new MovieResource($movie), '', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Film $movie)
    {
        $movie = $this->movieRepositoryInterface->update($movie, $request->validate($this->rules()));

        return ApiResponseClass::sendResponse(new MovieResource($movie), 'Movie updated.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Film $movie)
    {
        $this->movieRepositoryInterface->delete($movie);

        return ApiResponseClass::sendResponse([], 'Movie deleted.', 200);
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
