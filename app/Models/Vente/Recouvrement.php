<?php

namespace App\Models\Vente;

use App\Models\Vente\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recouvrement extends Model
{
    use HasFactory;

    protected $fillable = [
        "client_id",
        "comments",
        "verified"
    ];

    function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }
}
