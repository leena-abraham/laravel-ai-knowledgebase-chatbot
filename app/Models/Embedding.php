<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Embedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'chunk_id',
        'company_id',
        'vector',
        'model',
        'dimensions',
    ];

    protected $casts = [
        'vector' => 'array',
    ];

    public function chunk()
    {
        return $this->belongsTo(Chunk::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Calculate cosine similarity between this embedding and another vector
     */
    public function cosineSimilarity(array $otherVector): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($this->vector as $i => $value) {
            $dotProduct += $value * $otherVector[$i];
            $magnitudeA += $value * $value;
            $magnitudeB += $otherVector[$i] * $otherVector[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}
