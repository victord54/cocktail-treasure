<?php
session_start();
ini_set('display_errors', TRUE);
error_reporting(E_ALL);

$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/index_style.css">
    <link rel="stylesheet" href="styles/recettes_grid_style.css">
    <link rel="stylesheet" href="styles/recette_style.css">
    <title>Document</title>
</head>
<body>
    <?php include_once("header.php"); ?>

    <?php if (empty($_GET['ingredients']) && empty($_GET['no_ingredients'])) { ?>
        <form id="search_form" action="#" method="get">
            <label for="ingredients">Ingédients recherchés :</label><input type="text" name="ingredients" id="ingredients" placeholder="champagne, poire, ...">
            <br>
            <label for="no_ingredients">Ingédients non désirés :</label><input type="text" name="no_ingredients" id="no_ingredients" placeholder="noix, oeufs, ...">
            <br>
            <input type="submit" value="Rechercher">
        </form>
    <?php } else { ?>
        <?php
            $recettes_id = array();
            $no_recettes_id = array();
            if (!empty($_GET['ingredients'])) {
                $sqlQuery = "SELECT id_recette FROM contient_ingredient JOIN categorie USING (id_categorie, id_categorie) WHERE nom LIKE :nom;";
                $statement = $pdo->prepare($sqlQuery);
                $statement->bindValue(':nom', '%'.$_GET['ingredients'].'%');
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $recettes_id = $statement->fetchAll();
            }

            if (!empty($_GET['no_ingredients'])) {
                $sqlQuery = "SELECT id_recette FROM contient_ingredient JOIN categorie USING (id_categorie, id_categorie) WHERE nom LIKE :nom;";
                $statement = $pdo->prepare($sqlQuery);
                $statement->bindValue(':nom', '%'.$_GET['no_ingredients'].'%');
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $no_recettes_id = $statement->fetchAll();
            }

            
            $sqlQuery = "SELECT titre, id_recette FROM recette WHERE ";
            // Ajoute les recettes avec les ingrédients
            foreach ($recettes_id as $id) {
                $sqlQuery = $sqlQuery . 'id_recette = '. $id['id_recette'] . ' OR ';
            }
            if (!empty($recettes_id))
                $sqlQuery = $sqlQuery . '0 ';

            // Ajoute les recettes sans les ingrédients
            foreach ($no_recettes_id as $id) {
                $sqlQuery = $sqlQuery . 'id_recette != '. $id['id_recette'] . ' AND ';
            }
            if (!empty($no_recettes_id))
                $sqlQuery = $sqlQuery . '1';

            $statement = $pdo->prepare($sqlQuery);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $recettes = $statement->fetchAll();
            foreach ($recettes as $recette) { 
            if (isset($id_present)) {
                $sqlQuery ="SELECT id_recette FROM recette WHERE titre=\"$recette\"" ;
                $statement = $pdo->prepare($sqlQuery);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $id_recette = $statement->fetchAll();
                $id_recette = $id_recette[0]['id_recette'];
            } else {
                $id_recette = $recette['id_recette'];
            }?>
            <article class="conteneur_recette" onclick="window.location.href='recette.php?id= <?php echo $id_recette;?>';">
                <section class="recette"><?php 
                if (isset($id_present)) {
                    //echo '<p>' .$id_recette[0]['id_recette'] .'</p>';
                    echo '<p>'. $recette . '</p>';
                } else {
                    echo '<p>'. $recette['titre'] . '</p>';
                } ?></section>
            </article>
        <?php } ?>
    <?php } ?>
    <?php include_once("footer.php"); ?>
</body>
</html>