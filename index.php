<?php 
session_start();
ini_set('display_errors',1); 
error_reporting(E_ALL);

if (isset($_GET["id"])){
    //Récup les recettes qui contienent l'aliment donc l'id_cat = id
    //echo $_GET["id"];
    //echo "ui";
} else {
    //Pas d'id donc page index de base donc on est en haut de la hiérarchie
    $_SESSION['data'] = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <title>Page d'accueil de Cocktail Treasure</title>
</head>
<body>
    <?php include_once("header.php");?>
</br>

    <a href="init_bdd.php">Clique ici bg</a>
    </br></br>
    <?php include_once("filtre.php");?>
    </br>
    </br>
    </br>
    </br>
    </br>
    </br>
    HOVER LE COCKTAIL TOUT EN HAUT MDR
    </br>
    </br>
    </br>

</body>
<?php include_once("footer.php");?>
</html>