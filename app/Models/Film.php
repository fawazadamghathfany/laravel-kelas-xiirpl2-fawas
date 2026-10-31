<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Film extends Model
{
    use HasFactory;

    protected $table = 'films';

    protected $fillable = ['title', 'sinopsis', 'year', 'poster', 'genre_id'];

    public function perans(): HasMany
    {
        return $this->hasMany(Peran::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function kritiks(): HasMany
    {
        return $this->hasMany(Kritik::class);
    }
}
