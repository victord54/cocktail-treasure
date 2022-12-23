<?php 
session_start();
ini_set('display_errors', TRUE);
error_reporting(E_ALL);

if (isset($_GET["id"])){
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
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/index_style.css">
    <link rel="stylesheet" href="styles/recettes_grid_style.css">
    <title>Page d'accueil de Cocktail Treasure</title>
</head>
<body>
    <?php echo "\n"; include_once("header.php");?>
    <div class="parent">
        <?php echo "\n"; include_once("filtre.php");?>
        <?php echo "\n"; include_once("main.php");?>
    </div>
    <?php echo "\n"; include_once("footer.php"); echo "\n";?>
</body>
</html>