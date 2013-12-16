<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="css/style.css" rel="stylesheet" type="text/css" />
        <title>WikiContrib présenté par GRISOU</title>
        <link rel="shortcut icon" href="img/wiki.png" />
    </head>

    <body>
        <?php include("error.php"); ?>

        <br />
        <form method="post" action="analyse.php" >
            <h1>WikiContrib</h1><a href="http://fr.wikipedia.org"><img class="wikilogo" src="img/wiki.png" /></a>
            <div class="inset">
                <p>
                    <label for="nom">NOM DU COMPTE :</label>
                    <input type="text" name="nom" id="nom">
                </p>
                <p>
                    <label for="wikisite">QUEL SITE UTILISER :</label>
                    <SELECT name="wikisite">
                        <OPTION selected value="http://fr.wikipedia.org">fr.wikipedia.org</OPTION>
                        <OPTION value="http://en.wikipedia.org">en.wikipedia.org</OPTION>
                    </SELECT>
                </p>
                <p>
                    <label for="annee">POUR QUELLE ANNÉE ?</label>
                    <SELECT name="annee">
                        <OPTION value="2014">2014</OPTION>
                        <OPTION selected value="2013">2013</OPTION>
                        <OPTION value="2012">2012</OPTION>
                        <OPTION value="2011">2011</OPTION>
                        <OPTION value="2010">2010</OPTION>
                        <OPTION value="2009">2009</OPTION>
                        <OPTION value="2008">2008</OPTION>
                    </SELECT>
                </p>
            </div>
            <p class="p-container">
                <input type="submit" value="RECHERCHER" onclick="toggle_visibility('recherche');">
            </p>
        </form>

        <div id="recherche" style="display:none;" >
            <table class="recherche" border="0" bgcolor="ff0000" height="40" width="300" >
                <tr align="center">
                    <td align="center"><label>ANALYSE EN COURS... VEUILLEZ PATIENTER SVP</label></td>
                </tr>
            </table>
        </div>


        <!///////////////////////////////////////////////////////////////////////>
        <script type="text/javascript">
            function toggle_visibility(id) {
                var e = document.getElementById(id);
                if (e.style.display == 'block')
                    e.style.display = 'none';
                else
                    e.style.display = 'block';
            }
        </script>
        <!///////////////////////////////////////////////////////////////////////>

    </body>
</html>
