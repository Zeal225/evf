<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::middleware(['rediriger_si_utilisateur_connecte'])->group(function () {
    Route::post("/formation/add", "App\Http\Controllers\FormationController@InsererFormation")->name("inserer_formation");
    Route::get("/formation/add", "App\Http\Controllers\FormationController@formulaireInsererFormation")->name("formation_ajouter");

    Route::get("/formation","App\Http\Controllers\FormationController@listeFormation")->name("liste_formations");
    Route::get("/formation/modifier/id/{id}","App\Http\Controllers\FormationController@modifierFormation")->name("modifier_formation");
    Route::post("/formation/modifier/id/{id}","App\Http\Controllers\FormationController@modifierFormationOk")->name("modifier_formation_ok");
    Route::get("/formation/supprimer/id/{id}","App\Http\Controllers\FormationController@supprimerFormation")->name("supprimer_formation");
    Route::get("/formation/participants/{id}","App\Http\Controllers\FormationController@participantsFormation")->name("participants_formation");
    Route::get("/formation/evaluations","App\Http\Controllers\FormationController@evaluationsFormation")->name("evaluation_formation");
    Route::get("/formation/evaluations/participants/{id}","App\Http\Controllers\FormationController@evaluationsFormationParticipant")->name("evaluation_formation_participant");
    Route::get("/formation/{id}/evaluations/participants/{pt}/ajouter","App\Http\Controllers\FormationController@ajouterEvaluationsFormationParticipant")->name("ajouter_evaluation_formation_participant");
    Route::get("/formation/{id}/evaluations/participants/{pt}/resultat","App\Http\Controllers\FormationController@ajouterEvaluationsFormationParticipantResultat")->name("ajouter_evaluation_formation_participant_resultat");
    Route::post("/formation/evaluations/participants/ajouter","App\Http\Controllers\FormationController@ajouterEvaluationsFormationParticipantOk")->name("ajouter_evaluation_formation_participant_ok");

    Route::get("/formation/{id}/evaluations/froid/participants/{pt}/ajouter","App\Http\Controllers\FormationController@ajouterEvaluationsFroidFormationParticipant")->name("ajouter_evaluation_froid_formation_participant");
    Route::get("/formation/{id}/evaluations/froid/participants/{pt}/resultat","App\Http\Controllers\FormationController@ajouterEvaluationsFroidFormationParticipantResultat")->name("ajouter_evaluation_froid_formation_participant_resultat");
    Route::post("/formation/evaluations/froid/participants/ajouter","App\Http\Controllers\FormationController@ajouterEvaluationsFroidFormationParticipantOk")->name("ajouter_evaluation_froid_formation_participant_ok");

    Route::get('/signup', function () {
        return view('signup');
    });


    //utilisateur
    Route::get("/utilisateurs","App\Http\Controllers\UtilisateurController@listeutilisateur")->name("liste_utilisateurs");
    Route::get("/utilisateur/ajouter","App\Http\Controllers\UtilisateurController@formulaireAjouterUtilisateur")->name("formulaire_ajouter_utilisateurs");
    Route::post("/utilisateur/ajouter","App\Http\Controllers\UtilisateurController@ajouterUtilisateur")->name("ajouter_utilisateurs");
    Route::get("/utilisateur/modifier/{id}","App\Http\Controllers\UtilisateurController@formulaireModifierUtilisateur")->name("formulaire_modifier_utilisateurs");
    Route::post("/utilisateur/modifier","App\Http\Controllers\UtilisateurController@modifierUtilisateur")->name("modifier_utilisateurs");
    Route::get("/utilisateur/supprimer/{id}","App\Http\Controllers\UtilisateurController@supprimerUtilisateur")->name("supprimer_utilisateurs");

    //cabinet
    Route::get("/cabinets","App\Http\Controllers\CabinetController@listeCabinet")->name("liste_cabinets");
    Route::get("/cabinet/ajouter","App\Http\Controllers\CabinetController@formulaireAjouterCabinet")->name("formulaire_ajouter_cabinet");
    Route::post("/cabinet/ajouter","App\Http\Controllers\CabinetController@ajouterCabinet")->name("ajouter_cabinet");
    Route::get("/cabinet/modifier/{id}","App\Http\Controllers\CabinetController@formulaireModifierCabinet")->name("formulaire_modifier_cabinets");
    Route::post("/cabinet/modifier","App\Http\Controllers\CabinetController@modifierCabinet")->name("modifier_cabinet");
    Route::get("/cabinet/supprimer/{id}","App\Http\Controllers\CabinetController@supprimerCabinet")->name("supprimer_cabinets");


    //formateur
    Route::get("/formateurs","App\Http\Controllers\FormateurController@listeFormateur")->name("liste_formateurs");
    Route::get("/formateur/ajouter","App\Http\Controllers\FormateurController@formulaireAjouterFormateur")->name("formulaire_ajouter_formateur");
    Route::post("/formateur/ajouter","App\Http\Controllers\FormateurController@ajouterFormateur")->name("ajouter_formateur");
    Route::get("/formateur/modifier/{id}","App\Http\Controllers\FormateurController@formulaireModifierFormateur")->name("formulaire_modifier_formateur");
    Route::post("/formateur/modifier","App\Http\Controllers\FormateurController@modifierFormateur")->name("modifier_formateur");
    Route::get("/formateur/supprimer/{id}","App\Http\Controllers\FormateurController@supprimerFormateur")->name("supprimer_formateur");

    //objectif
    Route::get("/objectifs","App\Http\Controllers\ObjectifController@listeObjectif")->name("liste_objectifs");
    Route::get("/objectif/ajouter","App\Http\Controllers\ObjectifController@formulaireAjouterObjectif")->name("formulaire_ajouter_objectif");
    Route::post("/objectif/ajouter","App\Http\Controllers\ObjectifController@ajouterObjectif")->name("ajouter_objectif");
    Route::get("/objectif/modifier/{id}","App\Http\Controllers\ObjectifController@formulaireModifierObjectif")->name("formulaire_modifier_objectif");
    Route::post("/objectif/modifier","App\Http\Controllers\ObjectifController@modifierObjectif")->name("modifier_objectif");
    Route::get("/objectif/supprimer/{id}","App\Http\Controllers\ObjectifController@supprimerObjectif")->name("supprimer_objectif");

    //categorie appreciations
    Route::get("/categorieappreciations","App\Http\Controllers\CategorieAppreciationController@listeCategorie")->name("liste_categories");
    Route::get("/categorieappreciations/ajouter","App\Http\Controllers\CategorieAppreciationController@formulaireAjouterCategorie")->name("formulaire_ajouter_categorie");
    Route::post("/categorieappreciations/ajouter","App\Http\Controllers\CategorieAppreciationController@ajouterCategorie")->name("ajouter_categorie");
    Route::get("/categorieappreciations/modifier/{id}","App\Http\Controllers\CategorieAppreciationController@formulaireModifierCategorie")->name("formulaire_modifier_categorie");
    Route::post("/categorieappreciations/modifier","App\Http\Controllers\CategorieAppreciationController@modifierCategorie")->name("modifier_categorie");
    Route::get("/categorieappreciations/supprimer/{id}","App\Http\Controllers\CategorieAppreciationController@supprimerCategorie")->name("supprimer_categorie");

    //appreciations
    Route::get("/appreciations","App\Http\Controllers\AppreciationController@listeAppreciation")->name("liste_appreciations");
    Route::get("/appreciation/ajouter","App\Http\Controllers\AppreciationController@formulaireAjouterAppreciation")->name("formulaire_ajouter_appreciation");
    Route::post("/appreciation/ajouter","App\Http\Controllers\AppreciationController@ajouterAppreciation")->name("ajouter_appreciation");
    Route::get("/appreciation/modifier/{id}","App\Http\Controllers\AppreciationController@formulaireModifierAppreciation")->name("formulaire_modifier_appreciation");
    Route::post("/appreciation/modifier","App\Http\Controllers\AppreciationController@modifierAppreciation")->name("modifier_appreciation");
    Route::get("/appreciation/supprimer/{id}","App\Http\Controllers\AppreciationController@supprimerAppreciation")->name("supprimer_appreciation");

    //type utilisateur
    Route::get("/typeutilisateurs","App\Http\Controllers\TypeUtilisateurController@listeTypeUtilisateur")->name("liste_type_utilisateurs");
    Route::get("/typeutilisateur/ajouter","App\Http\Controllers\TypeUtilisateurController@formulaireAjouterTypeUtilisateur")->name("formulaire_ajouter_type_utilisateur");
    Route::post("/typeutilisateur/ajouter","App\Http\Controllers\TypeUtilisateurController@ajouterTypeUtilisateur")->name("ajouter_type_utilisateur");
    Route::get("/typeutilisateur/modifier/{id}","App\Http\Controllers\TypeUtilisateurController@formulaireModifierTypeUtilisateur")->name("formulaire_modifier_type_utilisateur");
    Route::post("/typeutilisateur/modifier","App\Http\Controllers\TypeUtilisateurController@modifierTypeUtilisateur")->name("modifier_type_utilisateur");
    Route::get("/typeutilisateur/supprimer/{id}","App\Http\Controllers\TypeUtilisateurController@supprimerTypeUtilisateur")->name("supprimer_type_utilisateur");

    //roles
    Route::get("/roles","App\Http\Controllers\RoleController@listeRole")->name("liste_roles");
    Route::get("/role/ajouter","App\Http\Controllers\RoleController@formulaireAjouterRole")->name("formulaire_ajouter_role");
    Route::post("/role/ajouter","App\Http\Controllers\RoleController@ajouterRole")->name("ajouter_role");
    Route::get("/role/modifier/{id}","App\Http\Controllers\RoleController@formulaireModifierRole")->name("formulaire_modifier_role");
    Route::post("/role/modifier","App\Http\Controllers\RoleController@modifierRole")->name("modifier_role");
    Route::get("/role/supprimer/{id}","App\Http\Controllers\RoleController@supprimerRole")->name("supprimer_role");

    //bilan
    Route::get("/stats/bilan","App\Http\Controllers\BilanController@bilan")->name("bilan");
    Route::get("/stats/bilan/chaud","App\Http\Controllers\BilanController@bilanchaud")->name("bilanchaud");


    Route::get('/utilisateurs/add', function () {
        return view('utilisateur_ajouter');
    });
    Route::get('/objectif/add', function () {
        return view('objectif_ajouter');
    });

});

Route::get('/', "App\Http\Controllers\UtilisateurController@connexion")->name("connexion");


Route::post('/connexion', "App\Http\Controllers\UtilisateurController@connexionOk")->name("connexion_ok");
Route::post('/deconnexion', "App\Http\Controllers\UtilisateurController@deconnexion")->name("deconnexion");


























/*Route::get('/', function () {
    return redirect('/connexion');
});*/




