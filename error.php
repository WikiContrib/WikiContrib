
<?php
if (isset($_SESSION['erreur']) && isset($_SESSION['erreur_detail'])) {

    $erreur = $_SESSION['erreur'];
    $erreur_detail = $_SESSION['erreur_detail'];
    if ($erreur) {
        echo '<p class="erreur">Une erreur s\'est produite : ' . $erreur_detail . '</p>';
        $_SESSION['erreur'] = false;
    }
}