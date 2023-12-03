<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BilanController extends Controller
{
    public function bilan()
    {
        $bilan = DB::select("SELECT
                f.id_formation,
                f.theme_formation,
                SUM(ea.note) AS sum_note,
                COUNT(ea.note) AS count_note
            FROM
                formations f
                    JOIN
                evaluationfroids e ON f.id_formation = e.id_formation
                    JOIN
                evaluationfroid_appreciations ea ON e.id = ea.id_evaluationfroid
            GROUP BY
                f.id_formation, f.theme_formation
            ORDER BY
                f.id_formation
            ");
        return view("bilan", compact('bilan'));
    }

    public function bilanchaud()
    {
        $bilan = DB::select("select f.id_formation, f.theme_formation, sum(ea.note) as sum_note, count(ea.note) as count_note
                    from formations f
                        join evaluationschauds e on f.id_formation = e.id_formation
                        join evaluationchaud_appreciations ea on e.id = ea.id_evaluationchaud
                    group by f.id_formation, f.theme_formation
                    order by f.id_formation");
        return view("bilan_chaud", compact('bilan'));
    }
}
