<?php

//DIVERSES FONCTIONS AUXILIAIRES

function result_db($db, $request){
  //Mise en forme du résultat d'une requête à la BDD

  //Requête à la BDD
  $reponse = mysqli_query($db, $request);
  if (!$reponse) {
    echo "Erreur dans la requête.";
    exit;
  }
  else{
    $reponse_tab = mysqli_fetch_assoc($reponse);
  }

  //Construction du résultat

  return $reponse_tab;
}

function deplacer_piece($plateau, $coup){
  //Réalise le déplacement concret de la pièce sur le plateau en gérant les différents coups spéciaux (prise en passant, roque, promotion)

  $i_ini = $coup[0];
  $j_ini = $coup[1];
  $i_new = $coup[2];
  $j_new = $coup[3];
  $prise = [];

  //On regarde si le coup est une prise en passant ou un roque
  if(isset($coup[4])){

    $option = $coup[4];

    if($option == "pp"){
      //gestion prise en passant
    }
    else if($option == "r"){
      //gestion roque
    }
    else{
      //gestion promotion
      $plateau[$i_ini][$j_ini] = "";
      $plateau[$i_new][$j_new] = $option;
    }

  }

  else{ //Déplacement normal

    $piece_deplacee = $plateau[$i_ini][$j_ini];
    $plateau[$i_ini][$j_ini] = "";
    //gestion de la prise si pièce ennemie présente
    if($plateau[$i_new][$j_new] != ""){
      $prise = $plateau[$i_new][$j_new];
    }
    $plateau[$i_new][$j_new] = $piece_deplacee;

  }

  return [$plateau, $prise];
}


function case_existe($i, $j){
  if($i >=0 && $i <=7 && $j >= 0 && $j <= 7){return true;}
  else{return false;}
}


function case_contenu($plateau, $i, $j, $trait){
  $contenu = $plateau[$i][$j];
  if($contenu == ""){return "vide";}
  else if($contenu[1] != $trait){return "ennemie";}
  else{return "alliee";}
}


function plateau_visible($plateau, $vues_adv, $coups_possibles, $trait){
  //On remplit le plateau de false
  $plateau_vis = array_fill(0, 7, array_fill(0, 7, false));
  //On passe en true les cases de vues_adv
  foreach($vues_adv as $vue){
    $plateau_vis[$vue[0]-1][$vue[1]-1] = true;
  }
  //On passe en true les cases où un coup est possible
  foreach($coups_possibles as $coup){
    $plateau_vis[$coup[2]][$coup[3]] = true;
  }
  //On passe en true les cases où le joueur a une pièce
  for($i = 0; $i <= 7; $i++){
    for($j = 0; $j <= 7; $j++){
      if(case_contenu($plateau, $i, $j, $trait) == "alliee"){
        $plateau_vis[$i][$j] = true;
      }
    }
  }
  return $plateau_vis;
}

function test_visibilite($coup, $plateau_vis){

  //Départ
  if($plateau_vis[$coup[0]][$coup[1]]){
    $coup_vis = [$coup[0], $coup[1]];
  }
  else{
    $coup_vis = [0,0];
  }

  //Arrivée
  if($plateau_vis[$coup[2]][$coup[3]]){
    $coup_vis[] = $coup[2];
    $coup_vis[] = $coup[3];
  }
  else{
    $coup_vis[] = 0;
    $coup_vis[] = 0;
  }

return $coup_vis;

}

 ?>
