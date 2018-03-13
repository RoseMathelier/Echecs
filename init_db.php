<?php

//Connexion à la BDD
$link = mysqli_connect("localhost", "root", "", "echec");
if (!$link) {
  echo "Erreur de connexion à la BDD.";
  exit;
}

//Création de la table
$req_create = "CREATE TABLE IF NOT EXISTS `partie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(142) NOT NULL,
  `tour` int(4) NOT NULL,
  `trait` int(1) NOT NULL,
  `j1` varchar(142) NOT NULL,
  `j2` varchar(142) NOT NULL,
  `histo_j1` text NOT NULL,
  `histo_j2` text NOT NULL,
  `plateau` text NOT NULL,
  `pieces_prises` text NOT NULL,
  `etat_partie` varchar(142) DEFAULT NULL,
  PRIMARY KEY (`id`)
)";

mysqli_query($link, $req_create);


//Initialisation du plateau de joueur
$plateau = array();
$plateau[] = array(array('T',1),array('C',1),array('F',1),array('R',1),array('D',1),array('F',1),array('C',1),array('T',1));
$plateau[] = array(array('p',1),array('p',1),array('p',1),array('p',1),array('p',1),array('p',1),array('p',1),array('p',1));
$plateau[] = array('','','','','','','','');
$plateau[] = array('','','','','','','','');
$plateau[] = array('','','','','','','','');
$plateau[] = array('','','','','','','','');
$plateau[] = array(array('p',2),array('p',2),array('p',2),array('p',2),array('p',2),array('p',2),array('p',2),array('p',2));
$plateau[] = array(array('T',2),array('C',2),array('F',2),array('R',2),array('D',2),array('F',2),array('C',2),array('T',2));
$plateau = json_encode($plateau);

//Initialisation des historiques
$histo_j2 = array();
$histo_j1 = array(array(
  'coups' => array(array(2,1,3,1),array(2,1,4,1),array(2,2,3,2),array(2,2,4,2),array(2,3,3,3),array(2,3,4,3),array(2,4,3,4),array(2,4,4,4),array(2,5,3,5),array(2,5,4,5),array(2,6,3,6),array(2,6,4,6),array(2,7,3,7),array(2,7,4,7),array(2,8,3,8),array(2,8,4,8), array(1,2,3,1), array(1,2,3,3), array(1,7,3,6), array(1,7,3,8))
));
$histo_j2 = json_encode($histo_j2);
$histo_j1 = json_encode($histo_j1);

//Initialisation de la liste des pièces prises
$pieces_prises = array();
$pieces_prises = json_encode($pieces_prises);

//Initialisation de la partie
$req_init = "INSERT INTO `partie` (`id`, `nom`, `tour`, `trait`, `j1`, `j2`, `histo_j1`, `histo_j2`,`plateau`,`pieces_prises`, `etat_partie`) VALUES
(1, 'partie1', 1, 1, 'joueur1', 'joueur2', '$histo_j1', '$histo_j2', '$plateau', '$pieces_prises', 'en cours')";

$result = mysqli_query($link,$req_init);

if($result != 1){
  echo "Base de donnée initialisée avec succès !";
}
else{
  echo "Problème lors de l'initialisation de la base de données";
}

 ?>
