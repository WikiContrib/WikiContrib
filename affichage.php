<?php
session_start();
$username = $_SESSION['nom'];
$wikisite = $_SESSION['wikisite'];
$year = $_SESSION['annee'];
/**
 *  if the GET variable receives a value for the field "annee", the $_SESSION['annee'] value will be overwritten
 *  this operation is done to allow displaying statistic information for for different years
 */
if (isset($_GET['annee'])) {
    $_SESSION['annee'] = $_GET['annee'];
    $year = $_SESSION['annee'];
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
        $allArticles = createdByUser($username, $wikisite, $year);
        $subscription = subscription($username, $wikisite);
        $sexe = sexe($username, $wikisite);
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
        echo "<table class=\"affichage\">
			<tr>
				<td class=\"id\">Articles créé en <b>" . $year . "</b> par <b>" . $username . "</b> :</td>
				<td class=\"id\"><b>" . $numberOfCreatedArticles . "</b> nouveaux articles</td>
			</tr>
			<tr>
				<td class=\"id\">Site Web :<b> " . $wikisite . "</b></td>
				<td class=\"id\">Analysé en <b><font color=\"red\">" . $time_taken . "</font></b> secondes</td>
			</tr>
			<tr>
				<td class=\"id\">Inscription :<b> " . $subscription . "</b></td>
				<td class=\"id\">" . $username . " est de sexe " . $sexe . "</td>
			</tr>
		</table>	
		";

        for ($i = 0; $i < count($allArticles); $i++) {
            $pageId = $allArticles[$i][0];                                      // the page id
            $pageTitle = $allArticles[$i][1];                                   // the page title
            $dateOfCreationTimestamp = $allArticles[$i][2];                     // timestamp of the date when the page was created
            $dateOfCreation = timestampToDate($dateOfCreationTimestamp);        // the date when the page was created
            /**
             *  this part of code displays a table that contains
             *  the following statistics: the page id, its title and a js button leading to the additional statistics
             *  that the current script can provide: number of views for each page, number of modifications in the given year,
             *  number of modifications since the last contribution of the user, number of days since the last modification
             *  and number of days since the last modification of the user
             */
            echo "<table class=\"affichage\">
				<tr>
					<td class=\"id\"><b>Article ID : </b> " . $pageId . "</td>
					<td class=\"titre\"><b>Titre : </b>" . urldecode($pageTitle) . "</td>
					<td><input type=\"submit\" value=\"PLUS\" onclick=\"plus('" . $pageTitle . "','" . $wikisite . "','" . $year . "','" . $i . "','" . $pageId . "','" . $username . "','" . $dateOfCreation . "');\"></td>
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
             * @param pageTitle      - page title
             * @param wikisite       - wiki website
             * @param year           - year
             * @param i              - id of the recently created div
             * @param pageId         - page id
             * @param username       - username
             * @param dateOfCreation - date of creation
             */
            function plus(pageTitle, wikisite, year, i, pageId, username, dateOfCreation) {
                $.ajax({
                    async: true,
                    url: "ajax.php",
                    dataType: "json",
                    type: "POST",
                    data: {
                        titre: pageTitle, wikisite: wikisite, annee: year, id: pageId, nom: username
                    }
                }).done(function(data) {
                    console.log(data);
                    document.getElementById(i).innerHTML = "<table class=\"affichage\">" +
                            "<tr><td class=\"stat\">Date de la création : </td><td class=\"result\">" + dateOfCreation + "</td><td></td></tr>" +
                            "<tr><td class=\"stat\">Nombre de visites en " + year + " : </td><td class=\"result\">" + data[0] + " visites" + "</td><td></td></tr>" +
                            "<tr><td class=\"stat\">Nombre de modifications en " + year + " : </td><td class=\"result\">" + data[1] + " modifications" + "</td><td></td></tr>" +
                            "<tr><td class=\"stat\">Nombre de modifications depuis la dernière intervention : </td><td class=\result\">" + data[2] + " modifications" + "</td><td></td></tr>" +
                            "<tr><td class=\"stat\">Jours depuis la dernière intervention : </td><td class=\"result\">" + data[3] + " jours</td><td></td></tr>" +
                            "<tr><td class=\"stat\">Jours depuis la dernière intervention de l'auteur : </td><td class=\"result\">" + data[4] + " jours</td><td></td></tr>" +
                            "<tr><td class=\"stat\">Redirigée : </td><td class=\"result\">" + data[5] + "</td><td></td></tr>" +
                            "<tr><td class=\"stat\">URL de la page : </td><td class=\"result\"><a href='" + data[6] + "'>URL</a> </td><td></td></tr>" +
                            "</table>";
                    toggle_visibility(i);
                });
            }
        </script>
    </body>
</html>
