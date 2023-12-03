<?php

namespace App\Http\Controllers;

use App\Http\Requests\InsererFormationRequest;
use App\Http\Requests\ModifierFormationRequest;
use App\Mail\NewAccountOtp;
use App\Mail\NouvelleFormationMail;
use App\Models\Cabinet;
use App\Models\CategorieAppreciation;
use App\Models\EvaluationchaudAppreciation;
use App\Models\Evaluationfroid;
use App\Models\EvaluationfroidAppreciation;
use App\Models\Evaluationschaud;
use App\Models\Formation;
use App\Models\Objectif;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class FormationController extends Controller
{
    public function formulaireInsererFormation(){
        $role = Session::get("role");
        if ($role !== "ADMIN") {
            Session::flash("permission", "Vous n'avez pas accès à cette fonctionnalité");
            return redirect()->route("liste_formations");
        }
        $formateurs = \App\Models\Formateur::with('cabinet')->orderBy("created_at", "desc")->get();
        $utilisateurs = User::with('typeUtilisateur', 'role')->where("id_role", "!=", null)->orderBy("created_at", "desc")->get();
        $objectifs = Objectif::all();
        return view('formation_ajouter', compact("formateurs", "utilisateurs", "objectifs"));
    }
    public function InsererFormation(InsererFormationRequest $formulaire_insertion_formation){
        $role = Session::get("role");
        if ($role !== "ADMIN") {
            Session::flash("permission", "Vous n'avez pas accès à cette fonctionnalité");
            return redirect()->route("liste_formations");
        }
        DB::beginTransaction();
        try {
            $theme_formation = $formulaire_insertion_formation['theme_formation'];
            $nouvelle_formation = new \App\Models\Formation;
            $nouvelle_formation->id_formateur = $formulaire_insertion_formation['id_formateur'];
            $nouvelle_formation->theme_formation = $theme_formation;
            $nouvelle_formation->save();

            foreach ($formulaire_insertion_formation['id_objectif'] as $item){
                $formation_objectif = new \App\Models\FormationObjectif;
                $formation_objectif->id_objectif = $item;
                $formation_objectif->id_formation = $nouvelle_formation->id_formation;
                $formation_objectif->save();
            }

            foreach ($formulaire_insertion_formation['id_utilisateur'] as $id_utilisateur) {
                $debut = Carbon::createFromFormat("d-M-Y", $formulaire_insertion_formation['date_debut']);
                $fin = Carbon::createFromFormat("d-M-Y", $formulaire_insertion_formation['date_fin']);
                $formation_utilisateur = new \App\Models\FormationUtilisateur;
                $formation_utilisateur->id_utilisateur = $id_utilisateur;
                $formation_utilisateur->id_formation = $nouvelle_formation->id_formation;
                $formation_utilisateur->date_debut = $debut->format('Y-m-d');
                $formation_utilisateur->date_fin = $fin->format('Y-m-d');
                $formation_utilisateur->heure_debut = $formulaire_insertion_formation['heure_debut'];
                $formation_utilisateur->heure_fin = $formulaire_insertion_formation['heure_fin'];
                $formation_utilisateur->lieu_formation = $formulaire_insertion_formation['lieu_formation'];
                $formation_utilisateur->save();
                $user = User::find($id_utilisateur);
                Mail::to($user->email)->send(new NouvelleFormationMail($user, $formation_utilisateur, $nouvelle_formation));
                Log::log('info', 'Mail envoyé à '.$user->email.' pour la formation '.$theme_formation);
            }
            DB::commit();
            Session::flash("message", "Formation ajoutée avec succès");
            return redirect()->route("liste_formations");
        }catch (\Exception $e){
            DB::rollBack();
            Log::log('error', $e->getMessage());
            return redirect()->route("liste_formations");
        }

    }

    public function listeFormation(){
        $liste_formations = \App\Models\Formation::with('formateur')
            ->with('formation_utilisateur')->orderBy("formations.id_formation", "desc")->get();
        $user = User::with('role')->find(auth()->user()->id);
        return view("formation", compact("liste_formations", "user"));
    }

    public function participantsFormation($id){
        $liste_participants = User::with('typeUtilisateur', 'role')
            ->join('formation_utilisateur', 'users.id', '=', 'formation_utilisateur.id_utilisateur')
            ->where('formation_utilisateur.id_formation', $id)->orderBy("users.id", "desc")->get();
        $formation = Formation::find($id);
        return view("formation_participants", compact("liste_participants", "formation"));
    }

    public function modifierFormation($id){
        $role = Session::get("role");
        if ($role !== "ADMIN") {
            Session::flash("permission", "Vous n'avez pas accès à cette fonctionnalité");
            return redirect()->route("liste_formations");
        }
        $formation  = \App\Models\Formation::with('formateur')->find($id);
        $formation_utilisateur = \App\Models\FormationUtilisateur::where("id_formation", $id)->first();
        $formateurs = \App\Models\Formateur::with('cabinet')->orderBy("created_at", "desc")->get();
        $utilisateurs = User::with('typeUtilisateur', 'role')->where("id_role", "!=", null)->orderBy("created_at", "desc")->get();
        $participants = User::with('typeUtilisateur', 'role')
            ->join('formation_utilisateur', 'users.id', '=', 'formation_utilisateur.id_utilisateur')
            ->where('formation_utilisateur.id_formation', $id)->orderBy("users.id", "desc")->get();
        $objectifs = Objectif::all();
        $objectifs_formation = \App\Models\FormationObjectif::where("id_formation", $id)->get();
        return view("modifier_formation", compact(
            "formation",
            "formateurs",
            "utilisateurs",
            "formation_utilisateur", "participants", "objectifs", 'objectifs_formation'));
    }

    public function modifierFormationOk(ModifierFormationRequest $formulaire_modification_formation, $id){
        $role = Session::get("role");
        if ($role !== "ADMIN") {
            Session::flash("permission", "Vous n'avez pas accès à cette fonctionnalité");
            return redirect()->route("liste_formations");
        }
        DB::beginTransaction();
        try {
            $formation  = \App\Models\Formation::find($id);
            $data = $formulaire_modification_formation->all();
            $formation->update($data);
            $formation_utilisateurs = \App\Models\FormationUtilisateur::where("id_formation", $id)->get();
            foreach ($formation_utilisateurs as $formation_utilisateur){
                $formation_utilisateur->delete();
            }
            foreach ($formulaire_modification_formation['id_utilisateur'] as $id_utilisateur) {
                $debut = Carbon::createFromFormat("d-M-Y", $formulaire_modification_formation['date_debut']);
                $fin = Carbon::createFromFormat("d-M-Y", $formulaire_modification_formation['date_fin']);
                $formation_utilisateur = new \App\Models\FormationUtilisateur;
                $formation_utilisateur->id_utilisateur = $id_utilisateur;
                $formation_utilisateur->id_formation = $formation->id_formation;
                $formation_utilisateur->date_debut = $debut;
                $formation_utilisateur->date_fin = $fin;
                $formation_utilisateur->heure_debut = $formulaire_modification_formation['heure_debut'];
                $formation_utilisateur->heure_fin = $formulaire_modification_formation['heure_fin'];
                $formation_utilisateur->lieu_formation = $formulaire_modification_formation['lieu_formation'];
                $formation_utilisateur->save();
            }

            $formation_objectifs = \App\Models\FormationObjectif::where("id_formation", $id)->get();
            foreach ($formation_objectifs as $formation_objectif){
                $formation_objectif->delete();
            }

            foreach ($formulaire_modification_formation['id_objectif'] as $item){
                $formation_objectif = new \App\Models\FormationObjectif;
                $formation_objectif->id_objectif = $item;
                $formation_objectif->id_formation = $formation->id_formation;
                $formation_objectif->save();
            }
            DB::commit();
            Session::flash("message", "Formation modifié avec succès");
            return redirect()->route("liste_formations");
        }catch (\Exception $e){
            DB::rollBack();
            return redirect()->route("liste_formations");
        }
    }

    public function supprimerFormation($id){
        $role = Session::get("role");
        if ($role !== "ADMIN") {
            Session::flash("permission", "Vous n'avez pas accès à cette fonctionnalité");
            return redirect()->route("liste_formations");
        }
        $formation  = \App\Models\Formation::find($id);
        $formation->delete();
        return redirect()->route("liste_formations");

    }

    public function evaluationsFormation(){
        $liste_formations = \App\Models\Formation::with('formateur')
            ->join('formation_utilisateur', 'formations.id_formation', '=', 'formation_utilisateur.id_formation')->distinct('formations.id_formation')->orderBy("formations.id_formation", "desc")->get();

        $user = User::with('role')->find(auth()->user()->id);
        return view("formation_evaluation", compact("liste_formations", "user"));
    }

    public function evaluationsFormationParticipant($id){
        $role = Session::get("role");
        $liste_participants = User::with('typeUtilisateur', 'role')
            ->join('formation_utilisateur', 'users.id', '=', 'formation_utilisateur.id_utilisateur')
            ->where('formation_utilisateur.id_formation', $id)->orderBy("users.id", "desc")->get();
        $formation = Formation::find($id);
        $user = User::with('role')->find(auth()->user()->id);
        return view("formation_participants_evaluation", compact("liste_participants", "formation", "user", "role"));
    }

    public function ajouterEvaluationsFormationParticipant($id, $id_utilisateur){
        $groupe_appreciations = CategorieAppreciation::with('appreciations')->get();
        $evaluationchaud = Evaluationschaud::with('formation')->with('formateur')->with('formation_utilisateur')->with('cabinet')->with('utilisateur')
            ->where("id_utilisateur", $id_utilisateur)->where("id_formation", $id)->first();

        if(!is_null($evaluationchaud)){
            $evaluations = EvaluationchaudAppreciation::where("id_evaluationchaud", $evaluationchaud->id)->get();
            return view("evaluation_chaud_ajouter_ok", compact("evaluationchaud", "evaluations", "groupe_appreciations"));
        }


        $formation  = \App\Models\Formation::with('formateur')->find($id);
        $formation_utilisateur = \App\Models\FormationUtilisateur::where("id_formation", $id)->first();
        $user = User::with('typeUtilisateur', 'role')->find($id_utilisateur);
        $cabinet = Cabinet::find($formation->formateur->cabinet_id);
        return view("evaluation_chaud_ajouter", compact(
            "formation",
            "formation_utilisateur", "user", "cabinet", "groupe_appreciations"));


    }

    public function ajouterEvaluationsFormationParticipantResultat($id, $id_utilisateur){
        $groupe_appreciations = CategorieAppreciation::with('appreciations')->get();
        $evaluationchaud = Evaluationschaud::with('formation')->with('formateur')->with('formation_utilisateur')->with('cabinet')->with('utilisateur')
            ->where("id_utilisateur", $id_utilisateur)->where("id_formation", $id)->first();

        if(!is_null($evaluationchaud)){
            $evaluations = EvaluationchaudAppreciation::where("id_evaluationchaud", $evaluationchaud->id)->get();
            return view("evaluation_chaud_ajouter_ok", compact("evaluationchaud", "evaluations", "groupe_appreciations"));
        }
        Session::flash("result", "Ce participant n'a pas encore fait son évaluation à chaud");
        return redirect()->back();
    }

    public function ajouterEvaluationsFormationParticipantOk(Request $request)
    {
        DB::beginTransaction();
        try {
            $evaluation_chaud = new Evaluationschaud();
            $evaluation_chaud->id_utilisateur = $request->id_utilisateur;
            $evaluation_chaud->id_formation = $request->id_formation;
            $evaluation_chaud->id_formateur = $request->id_formateur;
            $evaluation_chaud->id_formation_utilisateur = $request->id_formation_utilisateur;
            $evaluation_chaud->id_cabinet = $request->id_cabinet;
            $evaluation_chaud->save();
            foreach ($request->id_appreciation as $key => $id_appreciation) {
                $evaluation_chaud_appreciation = new EvaluationchaudAppreciation();
                $evaluation_chaud_appreciation->id_evaluationchaud = $evaluation_chaud->id;
                $evaluation_chaud_appreciation->appreciation = $request->id_appreciation[$key];
                $evaluation_chaud_appreciation->evaluation = $request->appreciation[$key];
                if ($request->appreciation[$key] == 'Très satisfaisant'){
                    $evaluation_chaud_appreciation->note = 4;
                }elseif ($request->appreciation[$key] == 'Satisfaisant'){
                    $evaluation_chaud_appreciation->note = 3;
                }elseif ($request->appreciation[$key] == 'Peu satisfaisant'){
                    $evaluation_chaud_appreciation->note = 2;
                }else{
                    $evaluation_chaud_appreciation->note = 1;
                }
                $evaluation_chaud_appreciation->save();
            }
            DB::commit();
            Session::flash("message", "Evaluation à chaud ajouté avec succès");
            return redirect()->route("ajouter_evaluation_formation_participant", [$request->id_formation, $request->id_utilisateur]);
        }catch (\Exception $e){
            DB::rollBack();
        }
    }

    public function ajouterEvaluationsFroidFormationParticipant($id, $id_utilisateur){


        $formation  = \App\Models\Formation::with('formateur')->find($id);
        $formation_utilisateur = \App\Models\FormationUtilisateur::where("id_formation", $id)->first();
        $user = User::with('typeUtilisateur', 'role')->find($id_utilisateur);
        $cabinet = Cabinet::find($formation->formateur->cabinet_id);
        $objectifs = Objectif::join('formation_objectifs', 'objectifs.id', '=', 'formation_objectifs.id_objectif')
            ->where("formation_objectifs.id_formation", $id)->get();

        $evaluationFroid = Evaluationfroid::where("id_utilisateur", $id_utilisateur)->where("id_formation", $id)->first();
        if(!is_null($evaluationFroid)){
            $evaluationsAppreciation = EvaluationfroidAppreciation::where("id_evaluationfroid", $evaluationFroid->id)->get();
            return view("evaluation_froid_ajouter", compact(
                "formation",
                "formation_utilisateur", "user", "cabinet", "objectifs", "evaluationFroid", "evaluationsAppreciation"));
        }
        return view("evaluation_froid_ajouter", compact(
            "formation",
            "formation_utilisateur", "user", "cabinet", "objectifs"));

    }


    public function ajouterEvaluationsFroidFormationParticipantResultat($id, $id_utilisateur){
        $formation  = \App\Models\Formation::with('formateur')->find($id);
        $formation_utilisateur = \App\Models\FormationUtilisateur::where("id_formation", $id)->first();
        $user = User::with('typeUtilisateur', 'role')->find($id_utilisateur);
        $cabinet = Cabinet::find($formation->formateur->cabinet_id);
        $objectifs = Objectif::join('formation_objectifs', 'objectifs.id', '=', 'formation_objectifs.id_objectif')
            ->where("formation_objectifs.id_formation", $id)->get();

        $evaluationFroid = Evaluationfroid::where("id_utilisateur", $id_utilisateur)->where("id_formation", $id)->first();
        if(!is_null($evaluationFroid)){
            $evaluationsAppreciation = EvaluationfroidAppreciation::where("id_evaluationfroid", $evaluationFroid->id)->get();
            return view("evaluation_froid_ajouter", compact(
                "formation",
                "formation_utilisateur", "user", "cabinet", "objectifs", "evaluationFroid", "evaluationsAppreciation"));
        }
        Session::flash("result", "Ce participant n'a pas encore fait son évaluation à froid");
        return redirect()->back();

    }

    public function ajouterEvaluationsFroidFormationParticipantOk(Request $request)
    {
        DB::beginTransaction();
        try {
            $evaluation_froid = new Evaluationfroid();
            $evaluation_froid->id_utilisateur = $request->id_utilisateur;
            $evaluation_froid->id_formation = $request->id_formation;
            $evaluation_froid->id_formation_utilisateur = $request->id_formation_utilisateur;
            $evaluation_froid->save();
            foreach ($request->id_objectifs as $key => $id_objectif) {
                $evaluation_froid_objectif = new EvaluationfroidAppreciation();
                $evaluation_froid_objectif->id_evaluationfroid = $evaluation_froid->id;
                $evaluation_froid_objectif->id_objectif = $request->id_objectifs[$key];
                $evaluation_froid_objectif->note = $request->notes[$key];
                $evaluation_froid_objectif->id_utilisateur = $request->id_utilisateur;
                $evaluation_froid_objectif->save();
            }
            $evaluation_froid->objectif_atteint = $request->objectif_atteint;
            $evaluation_froid->commentaire = $request->commentaire;
            $evaluation_froid->note_globale = $request->note_globale;
            $evaluation_froid->save();
            DB::commit();
            Session::flash("message", "Evaluation à froid ajouté avec succès");
            return redirect()->route("ajouter_evaluation_froid_formation_participant", [$request->id_formation, $request->id_utilisateur]);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            DB::rollBack();
        }
    }
}
