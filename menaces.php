<?php
//***FONCTION PRINCIPALE***
//La fonction case_menace effectue les vérifications nécessaires à la possibilité d'un coup
//La fonction longues_menaces gère les menaces à longue portée (fou, tour, dame)

function calcul_menaces($plateau, $trait){
  //Calcul des cases menacées par l'adversaire et donc visibles

  //Initialisation
  $cases_menaces = [];

  //Parcours du plateau
  for($i = 0; $i <= 7; $i++){
    for($j = 0 ; $j <= 7; $j++){

      //Pour chaque case, on teste si elle contient une pièces ennemie
      if(case_contenu($plateau, $i, $j, $trait) == "ennemie"){

        $piece = $plateau[$i][$j][0];

        if($piece == 'p'){
          $menaces_p = menaces_pion($plateau, $i, $j, $trait);
          if(!empty($menaces_p)){
            $cases_menaces = array_merge($cases_menaces, $menaces_p);
          }
        }
        else if($piece == 'C'){
          $menaces_c = menaces_cavalier($plateau, $i, $j, $trait);
          if(!empty($menaces_c)){
            $cases_menaces = array_merge($cases_menaces, $menaces_c);
          }
        }
        else if($piece == 'T'){
          $menaces_t = menaces_tour($plateau, $i, $j, $trait);
          if(!empty($menaces_t)){
            $cases_menaces = array_merge($cases_menaces, $menaces_t);
          }
        }
        else if($piece == 'F'){
          $menaces_f = menaces_fou($plateau, $i, $j, $trait);
          if(!empty($menaces_f)){
            $cases_menaces = array_merge($cases_menaces, $menaces_f);
          }
        }
        else if($piece == 'D'){
          $menaces_d = menaces_dame($plateau, $i, $j, $trait);
          if(!empty($menaces_d)){
            $cases_menaces = array_merge($cases_menaces, $menaces_d);
          }
        }
        else if($piece == 'R'){
          $menaces_r = menaces_roi($plateau, $i, $j, $trait);
          if(!empty($menaces_r)){
            $cases_menaces = array_merge($cases_menaces, $menaces_r);
          }
        }

      }

    }
  }

  //On supprime les doublons
  $menaces = array_unique($cases_menaces, SORT_REGULAR);

  return $menaces;

}

//***FONCTIONS PARTICULIERES***

function menaces_pion($plateau, $i, $j, $trait){

  //Initialisation
  $menaces = [];

  //Sens de déplacement du pion en fonction du trait
  if($trait ==1){$pas = 1;}
  else{$pas = -1;}

  //Le pion est une menace seulement en diagonale
  $menace_a = case_menace($plateau, $i, $j, $i - 1, $j + $pas, $trait);
  if($menace_a != false){$menaces[] = $menace_a;}
  $menace_b = case_menace($plateau, $i, $j, $i + 1, $j + $pas, $trait);
  if($menace_b != false){$menaces[] = $menace_b;}

  return $menaces;
}

function menaces_cavalier($plateau, $i, $j, $trait){

  //Initialisation
  $menaces = [];

  //8 coups possibles
  $menace_a = case_menace($plateau, $i, $j, $i - 2, $j + 1, $trait);
  if($menace_a != false){$menaces[] = $menace_a;}
  $menace_b = case_menace($plateau, $i, $j, $i - 2, $j - 1, $trait);
  if($menace_b != false){$menaces[] = $menace_b;}
  $menace_c = case_menace($plateau, $i, $j, $i - 1, $j + 2, $trait);
  if($menace_c != false){$menaces[] = $menace_c;}
  $menace_d = case_menace($plateau, $i, $j, $i - 1, $j - 2, $trait);
  if($menace_d != false){$menaces[] = $menace_d;}
  $menace_e = case_menace($plateau, $i, $j, $i + 1, $j + 2, $trait);
  if($menace_e != false){$menaces[] = $menace_e;}
  $menace_f = case_menace($plateau, $i, $j, $i + 1, $j - 2, $trait);
  if($menace_f != false){$menaces[] = $menace_f;}
  $menace_g = case_menace($plateau, $i, $j, $i + 2, $j + 1, $trait);
  if($menace_g != false){$menaces[] = $menace_g;}
  $menace_h = case_menace($plateau, $i, $j, $i + 2, $j - 1, $trait);
  if($menace_h != false){$menaces[] = $menace_h;}

  return $menaces;
}

function menaces_tour($plateau, $i, $j, $trait){

  //Construction des coups dans toutes les directions
  $menaces_gauche = longues_menaces($plateau, $i, $j, -1, 0, $trait);
  $menaces_droite = longues_menaces($plateau, $i, $j, 1, 0, $trait);
  $menaces_avant = longues_menaces($plateau, $i, $j, 0, 1, $trait);
  $menaces_arriere = longues_menaces($plateau, $i, $j, 0, -1, $trait);

  //Fusion des 4 tableaux
  $menaces = array_merge($menaces_gauche, $menaces_droite, $menaces_avant, $menaces_arriere);

  return $menaces;
}

function menaces_fou($plateau, $i, $j, $trait){

  //Construction des vues dans toutes les directions
  $menaces_SO = longues_menaces($plateau, $i, $j, -1, -1, $trait);
  $menaces_NO = longues_menaces($plateau, $i, $j, -1, 1, $trait);
  $menaces_SE = longues_menaces($plateau, $i, $j, 1, -1, $trait);
  $menaces_NE = longues_menaces($plateau, $i, $j, 1, 1, $trait);

  //Fusion des 4 tableaux
  $menaces = array_merge($menaces_SO, $menaces_NO, $menaces_SE, $menaces_NE);

  return $menaces;
}

function menaces_dame($plateau, $i, $j, $trait){
  //La dame combine les coups du fou et de la tour donc on fusionne simplement ces deux tableaux
  $menaces = array_merge(menaces_fou($plateau, $i, $j, $trait), menaces_tour($plateau, $i, $j, $trait));
  return $menaces;
}

function menaces_roi($plateau, $i, $j, $trait){

  //Initialisation
  $menaces = [];

  //8 coups possibles
  $menace_a = case_menace($plateau, $i, $j, $i, $j + 1, $trait);
  if($menace_a != false){$menaces[] = $menace_a;}
  $menace_b = case_menace($plateau, $i, $j, $i, $j - 1, $trait);
  if($menace_b != false){$menaces[] = $menace_b;}
  $menace_c = case_menace($plateau, $i, $j, $i + 1, $j, $trait);
  if($menace_c != false){$menaces[] = $menace_c;}
  $menace_d = case_menace($plateau, $i, $j, $i - 1, $j, $trait);
  if($menace_d != false){$menaces[] = $menace_d;}
  $menace_e = case_menace($plateau, $i, $j, $i + 1, $j + 1, $trait);
  if($menace_e != false){$menaces[] = $menace_e;}
  $menace_f = case_menace($plateau, $i, $j, $i + 1, $j - 1, $trait);
  if($menace_f != false){$menaces[] = $menace_f;}
  $menace_g = case_menace($plateau, $i, $j, $i - 1, $j + 1, $trait);
  if($menace_g != false){$menaces[] = $menace_g;}
  $menace_h = case_menace($plateau, $i, $j, $i - 1, $j - 1, $trait);
  if($menace_h != false){$menaces[] = $menace_h;}

  return $menaces;
}

//***FONCTIONS AUXILIAIRES***

function case_menace($plateau, $i1, $j1, $i2, $j2, $trait){
  //on teste si la case existe
  if(case_existe($i2, $j2) and case_contenu($plateau, $i2, $j2, $trait) == "alliee"){
    $menace = [$i1 + 1, $j1 + 1, $plateau[$i1][$j1]];
    return $menace;
  }
  else{
    return false;
  }
}

function longues_menaces($plateau, $i, $j, $di, $dj, $trait){
  //construit les menaces d'une case contenant une tour, un fou ou une dame
  $menaces = [];
  $pas = 1;
  $stop = false;
  while(!$stop){
    $contenu = case_contenu($plateau, $i, $j, $trait);
    if(case_existe($i, $j) && ($contenu == "vide" || $contenu == "ennemie")){
      //si la case existe et qu'elle est soit vide, soit occupée par un ennemi, on peut y aller donc on ajoute la vue
      $menace_case = case_menace($plateau, $i, $j, $i + $pas*$di, $j + $pas*$dj, $trait);
      if($menace_case != false){$menaces[] = $menace_case;}
    }
    if(!case_existe($i, $j) || $contenu != "vide"){
      //si la case n'existe pas ou contient quelque chose (ennemi ou allié), on ne peut pas aller + loin donc on sort de la boucle
      $stop = true;
    }
  }
  return $menaces;
}

 ?>
