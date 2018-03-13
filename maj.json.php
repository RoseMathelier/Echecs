<?php

//MAIN SCRIPT

//Fonctions utilisées dans ce script
include("vues.php"); //calcul des vues suite au coup du joueur ayant le trait
include("coups_possibles.php"); //calcul des coups possibles
include("menaces.php"); //calcul des menaces
include("fonction_echecs.php"); //fonctions auxiliaires

//Connexion à la BDD
$link = mysqli_connect("localhost", "root", "", "echec");
if (!$link) {
  echo "Erreur de connexion à la BDD.";
  exit;
}

//Récupération des paramètres en entrée
$id_partie = mysqli_real_escape_string($link, $_GET["partie"]);
$cote = mysqli_real_escape_string($link, $_GET["cote"]); //qui fait la requête ? 1 si c'est les blancs, 2 si c'est les noirs
$tour = mysqli_real_escape_string($link, $_GET["tour"]); //numéro du tour, 1er tour = 1
$trait = mysqli_real_escape_string($link, $_GET["trait"]); //1 si c'est aux blancs de jouer, 2 si c'est aux noirs

//On déduit le trait de l'adversaire
if($trait == 1){$trait_adv = 2;}
else{$trait_adv =1;}

//Récupération des données de la partie dans la BDD
$requete = "SELECT * FROM partie WHERE id =".$id_partie;
$result = result_db($link, $requete);

//Vérifications
if($trait == $result["trait"] && $tour == $result["tour"]){ //test : le tour et le trait sont-ils valides ?

  if(isset($_GET["coup"])){ //test : le paramètre optionnel "coup" est-il fourni ?

      //Récupération infos de la BDD
      $bdd_plateau = json_decode($result["plateau"], true); //état actuel du plateau
      $bdd_histo_cp = json_decode($result["histo_j".$trait], true); //récupère l'historique du joueur (current player)
      $bdd_histo_op = json_decode($result["histo_j".$trait_adv], true); // récupère l'historique du joueur adverse
      $bdd_pieces_prises = json_decode($result["pieces_prises"]); //pièces prises depuis le début du jeu et disponibles pour promotion

      //Lecture du coup joué par le joueur ayant le trait
      $rang_coup = $_GET["coup"]-1;
      $coup = $bdd_histo_cp[$tour*2 - 2]["coups"][$rang_coup]; //récupère le coup joué sous la forme [i0,j0,i1,j1,"option"]

      //On enlève 1 à chaque élément du coup pour obtenir ses vraies coordonnées sur le plateau
      $coup[0]--;
      $coup[1]--;
      $coup[2]--;
      $coup[3]--;

      //On vérifie si le coup est un abandon
      if($coup == "abandon"){
        //TODO : gestion abandon
        $fin = "abandon_j".$trait;
        $bdd_histo_cp[] = ["abandon"=>1];
        $bdd_histo_op[] = ["abandon"=>1];

      }

      else{ //cas général

        //***ARBITRAGE***

        //Récupération de la nature de la pièce jouée
        $nature_piece = $bdd_plateau[$coup[0]][$coup[1]][0];

        //Déplacement de la pièce
        $deplacement = deplacer_piece($bdd_plateau, $coup); //DONE
        $bdd_plateau = $deplacement[0];
        $prise = $deplacement[1];
        //TODO : prise en passant et roque

        //On regarde si le roi est mangé
        if(!empty($prise)){
          if($prise[0] == 'R'){
            $fin = "victoire_j".$trait;
          }
        }

        //Calcul des cases vues par le joueur ayant le trait grâce à ce nouveau coup (dans vues.php)
        $vues_trait = calcul_vues($bdd_plateau, $coup, $trait); //DONE
        //TODO : prise en passant et roque

        //Calcul des coups possibles pour l'adversaire suite à ce coup (dans coups_possibles.php)
        $coups_vues = calcul_coups($bdd_plateau, $trait_adv); //DONE
        $coups_possibles = $coups_vues[0];

        //On ajoute aux vues de l'adversaire les pions qui le bloquent éventuellement.
        $vues_adv_blo = $coups_vues[1];
        //TODO : prise en passant et roque

        //On ajoute aux vues de l'adversaire les cases qui le menacent (dans menaces.php)
        $vues_adv_men = calcul_menaces($bdd_plateau, $trait_adv); //DONE
        //TODO : prise en passant et roque
        $vues_adv = array_merge($vues_adv_blo, $vues_adv_men);

        //Tableau des cases visibles pour l'adversaire
        $plateau_vis = plateau_visible($bdd_plateau, $vues_adv, $coups_possibles, $trait_adv); //DONE

        //Application du brouillard de guerre sur le coup joué pour le joueur adverse (dans fonctions_echecs.php)
        $coup_visible = test_visibilite($coup, $plateau_vis); //DONE

        //***CONSTRUCTION DES RETOURS JSON***

        //On remet le coup bien comme il faut
        $coup[0]++;
        $coup[1]++;
        $coup[2]++;
        $coup[3]++;

        //Construction du retour json "je joue" : détail du coup + cases vues grâce à ce nouveau coup
        $je_joue = array('je_joue' => $coup, 'vues' => $vues_trait);
        //TODO : options si nécessaire (abandon, nul, pat...)
        $je_joue_json = json_encode($je_joue);

        //Construction du retour json "il joue"
        //Si l'adversaire peut voir le coup, on rajoute la nature de la pièce dans le retour json
        if($coup_visible != [0,0,0,0]){
          $il_joue = array('nature' => $nature_piece, 'il_joue' => $coup_visible, 'coups' => $coups_possibles, 'vues' => $vues_adv);
        }
        else{
          $il_joue = array('il_joue' => $coup_visible, 'coups' => $coups_possibles, 'vues' => $vues_adv);
        }
        //TODO : options si nécessaire (abandon, nul, pat...)
        $il_joue_json = json_encode($il_joue);

        //On inclue les retours json à la fin de l'historique de chaque joueur
        $bdd_histo_cp[] = json_decode($je_joue_json);
        $bdd_histo_op[] = json_decode($il_joue_json);

        //On met à jour la liste des pièces prises
        $bdd_pieces_prises = array_merge($bdd_pieces_prises, $prise);

        //***RETOUR JSON JE_JOUE***
        echo $je_joue_json;

      }

      //***MAJ de la BDD***

      //Incrémentation du tour (si les noirs ont le trait)
      if($trait == 2){$tour++;}

      //TODO : modification de l'état de la partie si besoin
      if(isset($fin)){
        $sql_fin = ", etat_partie = '".$fin."'";
      }
      else{
        $sql_fin = "";
      }

      //Mise à jour
      $requete_maj = 'UPDATE partie SET tour = '.$tour.
                     ', trait = '.$trait_adv.
                     ', histo_j'.$trait." = '".json_encode($bdd_histo_cp)."'".
                     ', histo_j'.$trait_adv." = '".json_encode($bdd_histo_op)."'".
                     ", plateau = '".json_encode($bdd_plateau)."'".
                     $sql_fin.
                     ", pieces_prises = '".json_encode($bdd_pieces_prises)."'".
                    ' WHERE id = '.$id_partie;

      $result = mysqli_query($link, $requete_maj);

      if($result != 1){
        echo "Problème lors de la mise à jour de la BDD";
      }

    }

  else{

    //***RETOUR JSON IL_JOUE***
    $bdd_histo = json_decode($result["histo_j".$trait], true); // on récupère l'historique du joueur qui vient d'avoir le trait
    $il_joue_json = $bdd_histo[($result["tour"]-1)*2]; // on prend la dernière entrée
    echo json_encode($il_joue_json);

  }

}

else{
  $retour_json_erreur = "{'ras': 1}";
  echo $retour_json_erreur;
}

//Fermeture de la BDD
mysqli_close($link);

 ?>
