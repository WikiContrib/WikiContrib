<?php
session_start();
include 'func.php';
$username = trim($_POST['nom']);
$wikisite = $_POST['wikisite'];
$year = $_POST['annee'];
$_SESSION['nom'] = $username;
$_SESSION['wikisite'] = $wikisite;
$_SESSION['annee'] = $year;
if (!user_exist($username, $wikisite)) {
    $_SESSION['erreur'] = true;
    $_SESSION['erreur_detail'] = 'L\'utilisateur ' . $username . ' n\'existe pas';
    header('Location: index.php');
} else {
    $_SESSION['erreur'] = false;
    header('location: affichage.php');
}