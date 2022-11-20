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

<main>
    <article class="recettes">
    <?php foreach ($recettes as $recette) { ?>
        <section class="recette"><?php echo $recette['titre']; ?></section>
    <?php } ?>

</article>
</main>