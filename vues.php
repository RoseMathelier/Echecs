<?php
//***FONCTION GENERALE***
//La fonction case_vue effectue les vérifications nécessaires à l'existence d'une vue
//La fonction longues_vues gère les vues à longue portée (fou, tour, dame)

function calcul_vues($plateau, $coup, $trait){
  //Calcul des cases vues par le joueur ayant le trait grâce au coup qu'il vient de jouer

  $i = $coup[2];
  $j = $coup[3];
  $piece = $plateau[$i][$j][0];

//Le calcul des vues dépend de la nature des déplacements de chaque pièce
  if($piece == 'p'){
    $vues = vues_pion($plateau, $i, $j, $trait);
  }
  else if($piece == 'C'){
    $vues = vues_cavalier($plateau, $i, $j, $trait);
  }
  else if($piece == 'T'){
    $vues = vues_tour($plateau, $i, $j, $trait);
  }
  else if($piece == 'F'){
    $vues = vues_fou($plateau, $i, $j, $trait);
  }
  else if($piece == 'D'){
    $vues = vues_dame($plateau, $i, $j, $trait);
  }
  else if($piece == 'R'){
    $vues = vue_roi($plateau, $i, $j, $trait);
  }

  return $vues;

}

//***FONCTIONS PARTICULIERES***

function vues_pion($plateau, $i, $j, $trait){

  //Initialisation
  $vues = array();

  //Sens de déplacement du pion en fonction du trait
  if($trait ==1){$pas = 1;}
  else{$pas = -1;}

  //Vue liée au déplacement ordinaire du pion (une case en avant)
  $vue_ordinaire = case_vue($plateau, $i+ $pas, $j, $trait);
  $vues[] = $vue_ordinaire;

  //Cases mangeables (en diagonale) : uniquement si elles existent et contiennent une pièce ennemie
  if(case_existe($i + $pas, $j - 1)){
    if(case_contenu($plateau, $i + $pas, $j - 1, $trait) == "ennemie"){
      $vues[] = [$i + $pas + 1, $j, $plateau[$i + $pas][$j - 1][0]];
    }
  }
  if(case_existe($i + $pas, $j + 1)){
    if(case_contenu($plateau, $i + $pas, $j + 1, $trait) == "ennemie"){
      $vues[] = [$i + $pas + 1, $j + 2, $plateau[$i + $pas][$j + 1][0]];
    }
  }

  //Remarque : on ne gère pas le déplacement +2 cases car il ne peut s'agir que d'un premier déplacement donc il ne fait jamais suite à un coup

  return $vues;
}


function vues_cavalier($plateau, $i, $j, $trait){

  //Initialisation
  $vues = [];

  //8 mouvements possibles :
  $vues[] = case_vue($plateau, $i - 2, $j + 1, $trait);
  $vues[] = case_vue($plateau, $i - 2, $j - 1, $trait);
  $vues[] = case_vue($plateau, $i - 1, $j + 2, $trait);
  $vues[] = case_vue($plateau, $i - 1, $j - 2, $trait);
  $vues[] = case_vue($plateau, $i + 1, $j + 2, $trait);
  $vues[] = case_vue($plateau, $i + 1, $j - 2, $trait);
  $vues[] = case_vue($plateau, $i + 2, $j + 1, $trait);
  $vues[] = case_vue($plateau, $i + 2, $j - 1, $trait);

  return $vues;
}


function vues_tour($plateau, $i, $j, $trait){

  //Construction des vues dans toutes les directions
  $vues_gauche = longues_vues($plateau, $i, $j, -1, 0, $trait);
  $vues_droite = longues_vues($plateau, $i, $j, 1, 0, $trait);
  $vues_avant = longues_vues($plateau, $i, $j, 0, 1, $trait);
  $vues_arriere = longues_vues($plateau, $i, $j, 0, -1, $trait);

  //Fusion des 4 tableaux
  $vues = array_merge($vues_gauche, $vues_droite, $vues_avant, $vues_arriere);

  return $vues;
}


function vues_fou($plateau, $i, $j, $trait){

  //Construction des vues dans toutes les directions
  $vues_SO = longues_vues($plateau, $i, $j, -1, -1, $trait);
  $vues_NO = longues_vues($plateau, $i, $j, -1, 1, $trait);
  $vues_SE = longues_vues($plateau, $i, $j, 1, -1, $trait);
  $vues_NE = longues_vues($plateau, $i, $j, 1, 1, $trait);

  //Fusion des 4 tableaux
  $vues = array_merge($vues_SO, $vues_NO, $vues_SE, $vues_NE);

  return $vues;
}



function vues_dame($plateau, $i, $j, $trait){
  //La dame combine les vues du fou et de la tour donc on fusionne simplement ces deux tableaux
  $vues = array_merge(vues_fou($plateau, $i, $j, $trait), vues_tour($plateau, $i, $j, $trait));
  return $vues;
}


function vues_roi($plateau, $i, $j, $trait){

  //Initialisation
  $vues = [];

  //8 coups possibles
  $vues[] = case_vue($plateau, $i, $j + 1, $trait);
  $vues[] = case_vue($plateau, $i, $j - 1, $trait);
  $vues[] = case_vue($plateau, $i + 1, $j, $trait);
  $vues[] = case_vue($plateau, $i - 1, $j, $trait);
  $vues[] = case_vue($plateau, $i + 1, $j + 1, $trait);
  $vues[] = case_vue($plateau, $i + 1, $j - 1, $trait);
  $vues[] = case_vue($plateau, $i - 1, $j + 1, $trait);
  $vues[] = case_vue($plateau, $i - 1, $j - 1, $trait);


  return $vues;
}

//***FONCTIONS AUXILIAIRES***

function case_vue($plateau, $i, $j, $trait){
  //construit la vue d'une case
  //on teste si la case cible existe
  if(case_existe($i, $j)){
    $vue = [$i + 1, $j + 1];
    //si la case contient une pièce ennemie, on précise dans la vue que la case contient cette pièce
    if(case_contenu($plateau, $i, $j, $trait) == "ennemie"){
      $vue[] = $plateau[$i][$j][0];
    }
  }
  return $vue;
}


function longues_vues($plateau, $i, $j, $di, $dj, $trait){
  //construit les vues d'une case contenant une tour, un fou ou une dame
  $vues = [];
  $pas = 1;
  $stop = false;
  while(!$stop){
    $contenu = case_contenu($plateau, $i, $j, $trait);
    if(case_existe() && ($contenu == "vide" || $contenu == "ennemie")){
      //si la case existe et qu'elle est soit vide, soit occupée par un ennemi, on peut y aller donc on ajoute la vue
      $vues[] = case_vue($plateau, $i + $pas*$di, $j + $pas*$dj, $trait);
    }
    if(!case_existe() || $contenu != "vide"){
      //si la case n'existe pas ou contient quelque chose (ennemi ou allié), on ne peut pas aller + loin donc on sort de la boucle
      $stop = true;
    }
  }
  return $vues;
}

 ?>
