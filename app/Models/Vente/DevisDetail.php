<?php

namespace App\Models\Vente;

use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevisDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'devis_id',
        'article_id',
        'qte_cmde',
        'prix_unit',
        'unite_mesure_id',
    ];

    function mesureunit(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, "unite_mesure_id");
    }
}
