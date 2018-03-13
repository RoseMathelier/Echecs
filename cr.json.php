<?php

include("fonction_echecs.php");

//Connexion à la BDD
$link = mysqli_connect("localhost", "root", "", "echec");
if (!$link) {
  echo "Erreur de connexion à la BDD.";
  exit;
}

//Récupération de l'ID de la partie
$id_partie = mysqli_real_escape_string($link, $_GET["partie"]);
$cote = mysqli_real_escape_string($link, $_GET["cote"]);

//Récupération des données de la partie dans la BDD
$requete = "SELECT * FROM partie WHERE id =".$id_partie;
$result = result_db($link, $requete);

//Récupération infos de la BDD
$bdd_tour = $result["tour"];
$bdd_trait = $result["trait"];
$bdd_histo = $result["histo_j".$cote];

$retour_tab = ["tour" => $bdd_tour, "trait" => $bdd_trait, "cote" => $cote, "histo" => $bdd_histo];
$retour_json = json_encode($retour_tab);
echo $retour_json;

 ?>
