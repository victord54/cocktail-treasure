<?php 
    session_start();

    $login = file_get_contents("data/login");
    $password = file_get_contents("data/password");
    $dbname = 'cocktail_treasure';
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    if (isset($_SESSION['favoris'])){
        sort($_SESSION['favoris']);
    }

    if (isset($_SESSION['favoris'])) {
        $fav = true;
        $recettes = array();

        //On récupère les recettes favorites
        foreach ($_SESSION['favoris'] as $fav){
            $sql = "SELECT * FROM recette WHERE id_recette=:recette";
            $query = $pdo->prepare($sql);
            $query->bindValue(":recette", $fav, PDO::PARAM_INT);
            $query->execute();
            $recette = $query->fetch();
            array_push($recettes, $recette);
        }
    } else {
        $fav = false;
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
    <link rel="stylesheet" href="styles/favoris_style.css">

</head>
<body>

    <?php echo "\n"; include_once("header.php");?>
        <?php if ($fav) { ?>
            <h1>Vos recettes favorites </h1>
            <div class="wrapper_main">
                <?php foreach ($recettes as $recette) { ?>
                    <article class="conteneur_recette" onclick="window.location.href='recette.php?id= <?php echo $recette['id_recette'];?>';">
                        <section class="recette"><?php 
                            echo '<p>'. $recette['titre'] . '</p>';
                        ?></section>
                    </article>
                    <?php } ?>
            </div>
        <?php }else {?>
            <h1>Vous n'avez pas de recettes favorites :( </h1>
        <?php } ?>

        
    <?php echo "\n"; include_once("footer.php"); echo "\n";?>
</body>
</html>
