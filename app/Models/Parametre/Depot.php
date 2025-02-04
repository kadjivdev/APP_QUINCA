<?php

namespace App\Models\Parametre;

use App\Models\parametre\PointDeVente;
use App\Models\parametre\TypeDepot;
use App\Models\Stock\StockDepot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_depot',
        'libelle_depot',
        'adresse_depot',
        'tel_depot',
        'depot_principal',
        'actif',
        'type_depot_id',
        'point_de_vente_id'
    ];

    protected $casts = [
        'depot_principal' => 'boolean',
        'actif' => 'boolean'
    ];


    /**
     * Obtenir le type de magasin associé à ce magasin.
     */
    public function typeDepot()
    {
        return $this->belongsTo(TypeDepot::class);
    }

    /**
     * Relation avec les points de vente
     */
    public function pointsVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    /**
     * Relation avec les stocks
     */
    public function stocks()
    {
        return $this->hasMany(StockDepot::class);
    }

    /**
     * Vérifie si le magasin est un magasin principal
     */
    public function isPrincipal()
    {
        return $this->typeDepot->code_type_depot === TypeDepot::PRINCIPAL;
    }

    /**
     * Vérifie si le magasin est un magasin de transit
     */
    public function isTransit()
    {
        return $this->typeDepot->code_type_depot === TypeDepot::TRANSIT;
    }

    /**
     * Vérifie si le magasin est un point de vente
     */
    public function isPointVente()
    {
        return $this->typeDepot->code_type_depot === TypeDepot::POINT_VENTE;
    }

    /**
     * Vérifie si le magasin est un magasin de stockage
     */
    public function isStockage()
    {
        return $this->typeDepot->code_type_depot === TypeDepot::STOCKAGE;
    }

    /**
     * Scope pour filtrer les dépôts actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour filtrer les dépôts principaux
     */
    public function scopePrincipal($query)
    {
        return $query->where('depot_principal', true);
    }

    /**
     * Scope pour filtrer les dépôts par type
     */
    public function scopeOfType($query, $type)
    {
        return $query->whereHas('typeDepot', function($q) use ($type) {
            $q->where('code_type_depot', $type);
        });
    }

    /**
     * Retourne le libellé du type de magasin
     */
    public function getTypeLibelle()
    {
        return $this->typeDepot ? $this->typeDepot->libelle_type_depot : null;
    }

    /**
     * Vérifie si le magasin est d'un type spécifique
     */
    public function isOfType($type)
    {
        return $this->typeDepot && $this->typeDepot->code_type_depot === $type;
    }
}
