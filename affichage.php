<?php
session_start();
$nom = $_SESSION['nom'];
$wikisite = $_SESSION['wikisite'];
$annee = $_SESSION['annee'];
/**
 *  if the GET variable receives a value for the field "annee", the $_SESSION['annee'] value will be overwritten
 *  this operation is done to allow displaying statistic information for for different years
 */
if(isset($_GET['annee'])){
    $_SESSION['annee'] = $_GET['annee'];
    $annee = $_SESSION['annee'];
}
include 'func.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <title>WikiContrib présenté par GRISOU</title>
    <link rel="shortcut icon" href="img/wiki.png"/>

</head>
<body>
<?php
$allArticles = array();     // will contain the id and title of all created pages by this user
$start = microtime(true);
$allArticles = createdByUser($nom, $wikisite, $annee);
$subscription = subscription($nom, $wikisite);
$sexe = sexe($nom, $wikisite);
$time_taken = microtime(true) - $start;
$numberOfCreatedArticles = count($allArticles);

/**
 *  this part of code will create buttons to overwrite the "annee" value or to go back to the first page
 */
echo "<div class='year'>";
echo "<ul>";
echo "<li>";
echo "<a href = 'index.php'>Home</a>";
echo "</li>";
echo "<li>";
echo "<a href = 'affichage.php?annee=2008'>2008</a>";
echo "</li>";
echo "<li>";
echo "<a href = 'affichage.php?annee=2009'>2009</a>";
echo "</li>";
echo "<li>";
echo "<a href = 'affichage.php?annee=2010'>2010</a>";
echo "</li>";
echo "<li>";
echo "<a href = 'affichage.php?annee=2011'>2011</a>";
echo "</li>";
echo "<li>";
echo "<a href = 'affichage.php?annee=2012'>2012</a>";
echo "</li>";
echo "<li>";
echo "<a href = 'affichage.php?annee=2013'>2013</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "<div style='clear: both'>";
echo "</div>";

/**
 *  this part of code displays a table that contains
 *  the following statistics: the year to which statistics belong and the number of articles created in that year
 */
echo  "<table class=\"affichage\">
			<tr>
				<td class=\"id\">Articles créé en <b>" . $annee . "</b> par <b>".$nom."</b> :</td>
				<td class=\"id\"><b>" . $numberOfCreatedArticles . "</b> nouveaux articles</td>
			</tr>
			<tr>
				<td class=\"id\">Site Web :<b> " . $wikisite."</b></td>
				<td class=\"id\">Analysé en <b><font color=\"red\">".$time_taken."</font></b> secondes</td>
			</tr>
			<tr>
				<td class=\"id\">Inscription :<b> " . $subscription."</b></td>
				<td class=\"id\">".$nom." est de sexe ".$sexe."</td>
			</tr>
		</table>	
		";

for ($i = 0; $i < count($allArticles); $i++) {
    $artId = $allArticles[$i][0];   // the page id
    $artTitle = $allArticles[$i][1];// the page title
    /**
     *  this part of code displays a table that contains
     *  the following statistics: the page id, its title and a js button leading to the additional statistics
     *  that the current script can provide: number of views for each page, number of modifications in the given year,
     *  number of modifications since the last contribution of the user, number of days since the last modification
     *  and number of days since the last modification of the user
     */
    echo "<table class=\"affichage\">
				<tr>
					<td class=\"id\"><b>Article ID : </b> " . $artId . "</td>
					<td class=\"titre\"><b>Titre : </b>" . urldecode($artTitle) . "</td>
					<td><input type=\"submit\" value=\"PLUS\" onclick=\"plus('" . $artTitle . "','" . $wikisite . "','" . $annee . "','" . $i . "','" . $artId . "','" . $nom . "');\"></td>
				</tr>
			</table>";

    echo "<div id=\"" . $i . "\" style=\"display:none\"><table class=\"affichage\"><tr><td class=\"id\"></td></tr></table></div>";
}
?>
<script type="text/javascript">
    function toggle_visibility(id) {
        var e = document.getElementById(id);
        if (e.style.display == 'block')
            e.style.display = 'none';
        else
            e.style.display = 'block';
    }
    /**
     * This functions sends to the "ajax.php" page the title, wiki website, year, page id and username
     * and expects to receive the number of page views, number of modifications in the given year,
     * number of modifications since the last contribution of the user, number of days since the last modification
     * and number of days since the last modification of the user
     *
     * then creates a table where it displays the received data
     *
     * @param titre     - page title
     * @param wikisite  - wiki website
     * @param annee     - year
     * @param i         - id of the recently created div
     * @param id        - page id
     * @param nom       - username
     */
    function plus(titre, wikisite, annee, i, id, nom) {
        $.ajax({
            async: true,
            url: "ajax.php",
            dataType: "json",
            type: "POST",
            data: {
                titre: titre, wikisite: wikisite, annee: annee, id: id, nom: nom
            }
        }).done(function (data) {
                console.log(data);
                document.getElementById(i).innerHTML = "<table class=\"affichage\">" +
                    "<tr><td class=\"stat\">Nombre de visites en "+annee+" : </td><td class=\"result\">"+data[0]+" visites"+"</td><td></td></tr>" +
                    "<tr><td class=\"stat\">Nombre de modifications en "+annee+" : </td><td class=\"result\">"+data[1]+" modifications"+"</td><td></td></tr>" +
                    "<tr><td class=\"stat\">Nombre de modifications depuis la dernière intervention : </td><td class=\result\">"+data[2]+" modifications"+"</td><td></td></tr>" +
                    "<tr><td class=\"stat\">Jours depuis la dernière intervention : </td><td class=\"result\">"+data[3]+" jours</td><td></td></tr>" +
                    "<tr><td class=\"stat\">Jours depuis la dernière intervention de l'auteur : </td><td class=\"result\">" + data[4] + " jours</td><td></td></tr>" +
                    "</table>";
                toggle_visibility(i);
            });
    }
</script>
</body>
</html>
