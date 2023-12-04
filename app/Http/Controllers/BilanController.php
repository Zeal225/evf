<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BilanController extends Controller
{
    public function bilan()
    {
        $bilan = DB::select("select f.id_formation,
       f.theme_formation,
       e.id_utilisateur,
       u.name,
       sum(ea.note) as sum_note,
       count(ea.note) as count_note from
      formations f
      join evaluationfroids e on f.id_formation = e.id_formation
      join public.evaluationfroid_appreciations ea on e.id = ea.id_evaluationfroid
    join users u on e.id_utilisateur = u.id
group by e.id_utilisateur, f.id_formation, f.theme_formation, u.name");
        return view("bilan", compact('bilan'));
    }

    public function bilanchaud()
    {
        $bilan = DB::select("SELECT
                f.id_formation,
                f.theme_formation,
                SUM(ea.note) AS sum_note,
                COUNT(ea.note) AS count_note
            FROM
                formations f
                    JOIN
                evaluationschauds e ON f.id_formation = e.id_formation
                    JOIN
                evaluationchaud_appreciations ea ON e.id = ea.id_evaluationchaud
            GROUP BY
                f.id_formation, f.theme_formation
            ORDER BY
                f.id_formation
            ");
        return view("bilan_chaud", compact('bilan'));
    }
}
