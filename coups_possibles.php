<?php

//***FONCTION PRINCIPALE***
//La fonction case_coup effectue les vérifications nécessaires à la possibilité d'un coup
//La fonction longs_coups gère les coups à longue portée (fou, tour, dame)

function calcul_coups($plateau, $trait){
//Calcul des coups possibles pour le joueur adverse + vues liées au pion

  //Initialisation
  $coups_possibles = [];
  $vues_adv = [];

  //Parcours du plateau
  for($i = 0; $i <= 7; $i++){
    for($j = 0 ; $j <= 7; $j++){

      //Pour chaque case, on teste si elle contient une pièce appartenant au joueur
      if(case_contenu($plateau, $i, $j, $trait) == "alliee"){

        $piece = $plateau[$i][$j][0];

        if($piece == 'p'){
          $coups_vues = coups_pion($plateau, $i, $j, $trait);
          if(!empty($coups_vues[0])){
            $coups_possibles = array_merge($coups_possibles, $coups_vues[0]);
          }
          $vues_adv = array_merge($vues_adv, $coups_vues[1]);
        }
        else if($piece == 'C'){
          $coups_c = coups_cavalier($plateau, $i, $j, $trait);
          if(!empty($coups_c)){
            $coups_possibles = array_merge($coups_possibles, $coups_c);
          }
        }
        else if($piece == 'T'){
          $coups_t = coups_tour($plateau, $i, $j, $trait);
          if(!empty($coups_t)){
            $coups_possibles = array_merge($coups_possibles, $coups_t);
          }
        }
        else if($piece == 'F'){
          $coups_f = coups_fou($plateau, $i, $j, $trait);
          if(!empty($coups_f)){
            $coups_possibles = array_merge($coups_possibles, $coups_f);
          }
        }
        else if($piece == 'D'){
          $coups_d = coups_dame($plateau, $i, $j, $trait);
          if(!empty($coups_d)){
            $coups_possibles = array_merge($coups_possibles, $coups_d);
          }
        }
        else if($piece == 'R'){
          $coups_r = coups_roi($plateau, $i, $j, $trait);
          if(!empty($coups_r)){
            $coups_possibles = array_merge($coups_possibles, $coups_r);
          }
        }

      }

    }
  }

  return [$coups_possibles, $vues_adv];
}

//***FONCTIONS PARTICULIERES***

function coups_pion($plateau, $i, $j, $trait){
  //On gère à la fois les coups possibles pour le pion et les vues liées aux pièces ennemies bloquant le pion

  //Initialisation
  $coups = [];
  $vues = [];

  //Sens de déplacement du pion en fonction du trait
  if($trait ==1){$pas = 1;}
  else{$pas = -1;}

  //Déplacement ordinaire du pion (une case en avant)
  if(case_existe($i + $pas, $j)){
    if(case_contenu($plateau, $i + $pas, $j, $trait) == "vide"){

      //Gestion des promotions
      if($i + $pas == 1 or $i + $pas == 8){
        //si on atteint une extrémité du plateau, on peut remplacer le pion par la pièce de son choix
        $coups[] = [$i + 1, $j + 1, $i + $pas + 1, $j + 1, "C"];
        $coups[] = [$i + 1, $j + 1, $i + $pas + 1, $j + 1, "F"];
        $coups[] = [$i + 1, $j + 1, $i + $pas + 1, $j + 1, "T"];
        $coups[] = [$i + 1, $j + 1, $i + $pas + 1, $j + 1, "D"];

      }
      else{
        //pas de promotion possible, coup classique
        $coups[] = [$i + 1, $j + 1, $i + $pas + 1, $j + 1];
      }

    }
    if(case_contenu($plateau, $i + $pas, $j, $trait) == "ennemie"){
      $vues[] = [$i + $pas + 1, $j + 1, $plateau[$i + $pas][$j]];
    }
  }
  
  //TODO : ajouter gestion prise en passant
  //TODO : vues liées à la prise en passant

  //Déplacement +2 cases lors du 1er coup d'un pion
  if(($i == 1 and $trait == 1) or ($i == 6 and $trait == 2)){
    //seulement si le pion est à sa position initiale
    if(case_existe($i + 2*$pas, $j)){
      if(case_contenu($plateau, $i + $pas, $j, $trait) == "vide" && case_contenu($plateau, $i + 2*$pas, $j, $trait) == "vide"){
        //possible seulement si les 2 cases de devant sont vides
        $coups[] = [$i + 1, $j + 1, $i + 2*$pas + 1, $j + 1];
      }
      if(case_contenu($plateau, $i + $pas, $j, $trait) == "vide" && case_contenu($plateau, $i + 2*$pas, $j, $trait) == "ennemie"){
        //la case devant est vide et la suivante est occupée par un ennemie
        $vues[] = [$i + 2*$pas + 1, $j + 1, $plateau[$i][$j + 2*$pas]];
      }
    }
  }

  //Cases mangeables (en diagonale) : uniquement si elles existent et contiennent une pièce ennemie
  if(case_existe($i + $pas, $j - 1)){
    if(case_contenu($plateau, $i + $pas, $j - 1, $trait) == "ennemie"){
      $coups[] = [$i, $j, $i + $pas, $j -1];
    }
  }
  if(case_existe($i + $pas, $j + 1)){
    if(case_contenu($plateau, $i + $pas, $j + 1, $trait) == "ennemie"){
      $coups[] = [$i, $j, $i + $pas, $j + 1];
    }
  }

  return [$coups, $vues];
}

function coups_cavalier($plateau, $i, $j, $trait){

  //Initialisation
  $coups = [];

  //8 coups possibles
  $coup_a = case_coup($plateau, $i, $j, $i - 2, $j + 1, $trait);
  if($coup_a != false){$coups[] = $coup_a;}
  $coup_b = case_coup($plateau, $i, $j, $i - 2, $j - 1, $trait);
  if($coup_b != false){$coups[] = $coup_b;}
  $coup_c = case_coup($plateau, $i, $j, $i - 1, $j + 2, $trait);
  if($coup_c != false){$coups[] = $coup_c;}
  $coup_d = case_coup($plateau, $i, $j, $i - 1, $j - 2, $trait);
  if($coup_d != false){$coups[] = $coup_d;}
  $coup_e = case_coup($plateau, $i, $j, $i + 1, $j + 2, $trait);
  if($coup_e != false){$coups[] = $coup_e;}
  $coup_f = case_coup($plateau, $i, $j, $i + 1, $j - 2, $trait);
  if($coup_f != false){$coups[] = $coup_f;}
  $coup_g = case_coup($plateau, $i, $j, $i + 2, $j + 1, $trait);
  if($coup_g != false){$coups[] = $coup_g;}
  $coup_h = case_coup($plateau, $i, $j, $i + 2, $j - 1, $trait);
  if($coup_h != false){$coups[] = $coup_h;}

  return $coups;
}

function coups_tour($plateau, $i, $j, $trait){

  //Construction des coups dans toutes les directions
  $coups_gauche = longs_coups($plateau, $i, $j, -1, 0, $trait);
  $coups_droite = longs_coups($plateau, $i, $j, 1, 0, $trait);
  $coups_avant = longs_coups($plateau, $i, $j, 0, 1, $trait);
  $coups_arriere = longs_coups($plateau, $i, $j, 0, -1, $trait);

  //Fusion des 4 tableaux
  $coups = array_merge($coups_gauche, $coups_droite, $coups_avant, $coups_arriere);

  return $coups;
}

function coups_fou($plateau, $i, $j, $trait){

  //Construction des vues dans toutes les directions
  $coups_SO = longs_coups($plateau, $i, $j, -1, -1, $trait);
  $coups_NO = longs_coups($plateau, $i, $j, -1, 1, $trait);
  $coups_SE = longs_coups($plateau, $i, $j, 1, -1, $trait);
  $coups_NE = longs_coups($plateau, $i, $j, 1, 1, $trait);

  //Fusion des 4 tableaux
  $coups = array_merge($coups_SO, $coups_NO, $coups_SE, $coups_NE);

  return $coups;
}

function coups_dame($plateau, $i, $j, $trait){
  //La dame combine les coups du fou et de la tour donc on fusionne simplement ces deux tableaux
  $coups = array_merge(coups_fou($plateau, $i, $j, $trait), coups_tour($plateau, $i, $j, $trait));
  return $coups;
}

function coups_roi($plateau, $i, $j, $trait){

  //Initialisation
  $coups = [];

  //8 coups possibles
  $coup_a = case_coup($plateau, $i, $j, $i, $j + 1, $trait);
  if($coup_a != false){$coups[] = $coup_a;}
  $coup_b = case_coup($plateau, $i, $j, $i, $j - 1, $trait);
  if($coup_b != false){$coups[] = $coup_b;}
  $coup_c = case_coup($plateau, $i, $j, $i + 1, $j, $trait);
  if($coup_c != false){$coups[] = $coup_c;}
  $coup_d = case_coup($plateau, $i, $j, $i - 1, $j, $trait);
  if($coup_d != false){$coups[] = $coup_d;}
  $coup_e = case_coup($plateau, $i, $j, $i + 1, $j + 1, $trait);
  if($coup_e != false){$coups[] = $coup_e;}
  $coup_f = case_coup($plateau, $i, $j, $i + 1, $j - 1, $trait);
  if($coup_f != false){$coups[] = $coup_f;}
  $coup_g = case_coup($plateau, $i, $j, $i - 1, $j + 1, $trait);
  if($coup_g != false){$coups[] = $coup_g;}
  $coup_h = case_coup($plateau, $i, $j, $i - 1, $j - 1, $trait);
  if($coup_h != false){$coups[] = $coup_h;}

  return $coups;
}

//***FONCTIONS AUXILIAIRES***

function case_coup($plateau, $i1, $j1, $i2, $j2, $trait){
  //construit le coup d'une case
  //on teste si la case cible existe
  if(case_existe($i2, $j2) and (case_contenu($plateau, $i2, $j2, $trait) == "vide" or case_contenu($plateau, $i2, $j2, $trait) == "ennemie")){
    $coup = [$i1 + 1, $j1 + 1, $i2 + 1, $j2 + 1];
    return $coup;
  }
  else{
    return false;
  }
}

function longs_coups($plateau, $i, $j, $di, $dj, $trait){
  //construit les vues d'une case contenant une tour, un fou ou une dame
  $coups = [];
  $pas = 1;
  $stop = false;
  while(!$stop){
    $contenu = case_contenu($plateau, $i, $j, $trait);
    if(case_existe($i,$j) && ($contenu == "vide" || $contenu == "ennemie")){
      //si la case existe et qu'elle est soit vide, soit occupée par un ennemi, on peut y aller donc on ajoute la vue
      $coups[] = case_coup($plateau, $i, $j, $i + $pas*$di, $j + $pas*$dj, $trait);
    }
    if(!case_existe($i,$j) || $contenu != "vide"){
      //si la case n'existe pas ou contient quelque chose (ennemi ou allié), on ne peut pas aller + loin donc on sort de la boucle
      $stop = true;
    }
  }
  return $coups;
}

 ?>
