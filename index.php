<?php 
session_start();
ini_set('display_errors', TRUE);
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <title>Page d'accueil de Cocktail Treasure</title>
</head>
<body>
    <?php echo "\n"; include_once("header.php");?>
    <?php echo "\n"; include_once("filtre.php");?>
    <?php echo "\n"; include_once("main.php");?>
    <?php echo "\n"; include_once("footer.php"); echo "\n";?>
</body>
</html>