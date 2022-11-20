<?php
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

$sqlQuery = "SELECT titre FROM recette;";
$statement = $pdo->prepare($sqlQuery);
$statement->setFetchMode(PDO::FETCH_ASSOC);
$statement->execute();
$recettes = $statement->fetchAll();
?>

<div class="wrapper_main">
    <?php foreach ($recettes as $recette) { ?>
        <article class="conteneur_recette">
            <section class="recette"><?php echo $recette['titre']; ?></section>
        </article>
    <?php } ?>
</div>
