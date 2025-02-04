<?php

namespace App\Http\Controllers\Portail;


use App\Http\Controllers\Controller;
use Carbon\Carbon;



class PortailController extends Controller
{

    public function index()
    {

         // Configuration de la locale en français
         Carbon::setLocale('fr');

         // Formatage de la date
         $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY, HH:mm');

        // Retourne la vue avec la date
        return view('pages.portail.index', compact('date'));
    }
}
