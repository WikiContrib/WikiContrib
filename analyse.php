<?php session_start(); ?>
<?php
include 'func.php';
$nom = trim($_POST['nom']);
$wikisite = $_POST['wikisite'];
$annee = $_POST['annee'];
$_SESSION['nom'] = $nom;
$_SESSION['wikisite'] = $wikisite;
$_SESSION['annee'] = $annee;
if (!user_exist($nom,$wikisite)){
	$_SESSION['erreur'] = true;
	$_SESSION['erreur_detail'] = 'L\'utilisateur ' .$nom.  ' n\'existe pas';
	header( 'Location: index.php' ) ;
}else{
	$_SESSION['erreur'] = false;
	header('location: affichage.php');

}
?>