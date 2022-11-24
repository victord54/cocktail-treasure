<?php 
    session_start();

    if (isset($_GET["id"])){
        $id = $_GET["id"];
        //var_dump($id);
    } else {
        header("Location: index.php");
    }

    $login = file_get_contents("data/login");
    $password = file_get_contents("data/password");
    $dbname = 'cocktail_treasure';
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    $sqlQuery = "SELECT * FROM recette WHERE id_recette=:id;";
    $statement = $pdo->prepare($sqlQuery);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->execute();
    $recette = $statement->fetchAll();
    $recette = $recette[0];
    //var_dump($recette);
    $liste_ingredients = explode("|", $recette['ingredients']);
    //var_dump($liste_ingredients);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/recette_style.css">
    <title><?php echo $recette['titre']; ?></title>
</head>
<body>
    <?php echo "\n"; include_once("header.php");?>
    <div>
    <h1><?php echo $recette['titre'];?></h1>
    <p>Pour cette recette, vous aurez besoin de :
        <ul class="liste_ingredients"><?php 
        foreach ($liste_ingredients as $ingredient){
            echo '<li>' . $ingredient . '</li>';
        }
        ?></ul>
    </p>
    <p><?php echo $recette['preparation'];?></p>


</div>
    <?php echo "\n"; include_once("footer.php"); echo "\n";?>
</body>
</html>
