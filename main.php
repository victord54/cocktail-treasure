<?php

ini_set('display_errors', TRUE);
error_reporting(E_ALL);

if (isset($_GET["id"])){
    $id = $_GET["id"];
} else {
    //Pas d'id donc page index de base donc on est en haut de la hiérarchie
    $id = null;
}

//Tableau des caractères à remplacer pour les images
$search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'ñ', ' ', '\'');
//Tableau avec les caractères de remplacement pour les images
$replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'n', '_','');

$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
$recettes= array();

if ($id == null){
    $sqlQuery = "SELECT titre,id_recette FROM recette;";
    $statement = $pdo->prepare($sqlQuery);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->execute();
    $recettes = $statement->fetchAll();
    $id_present = false;
} else {
    $id_present = true;
    $recettes = getRecettes($pdo, $id, $recettes);
    sort($recettes);//Tri pour remettre dans l'ordre alphabétique
    //var_dump($recettes);
}



function getRecettes($pdo, $id, $recettes){
    //Pour chaque sous catégorie si pas de sous catégorie -> Fetch titre 
    //Sinon pour chaque sous-catégorie si pas de sous-sous-catégorie -> Fetch titre 
    //Etc ...
    $sqlQuery = "SELECT * FROM sous_categorie JOIN possede_ssc USING (id_sous_categorie,id_sous_categorie) WHERE id_categorie=:id";
    $statement = $pdo->prepare($sqlQuery);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->execute();
    $ss_cat = $statement->fetchAll();
    /*print("_________");
    print('ID :' . $id);
    print('SOUS_CAT :');
    var_dump($ss_cat);*/
    if (!$ss_cat){
        $sqlQuery = "SELECT titre FROM recette NATURAL JOIN contient_ingredient WHERE id_categorie = :id;";
        $statement = $pdo->prepare($sqlQuery);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute();
        $recette = $statement->fetchAll();
        foreach ($recette as $rec){
            if (!in_array($rec['titre'], $recettes)){ //On check que le titre n'est pas déjà dans le tableau
                array_push($recettes, $rec['titre']);
            }
        }
        //print('_________');
        //var_dump($recettes);
    } else {
        foreach ($ss_cat as $cat){
            //var_dump($cat);
            $nom = $cat['nom'];
            $sqlQuery ="SELECT id_categorie FROM categorie WHERE nom=\"$nom\"" ;
            $statement = $pdo->prepare($sqlQuery);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $id_cat = $statement->fetchAll();
            $recettes = getRecettes($pdo, $id_cat[0]['id_categorie'], $recettes);
        }
    }
    return $recettes;
}
?>

<div class="wrapper_main">
    <?php foreach ($recettes as $recette) { 
        if ($id_present) {
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
            $img_titre = null;
            if ($id_present) {
                echo '<p>'. $recette . '</p>';
                $img_titre = str_replace($search, $replace, $recette);
            } else {
                echo '<p>'. $recette['titre'] . '</p>';
                $img_titre = str_replace($search, $replace, $recette['titre']);
            } ?>
            <?php
                $path = 'resources/photos/' . $img_titre . '.jpg';
                if (file_exists($path)){
                    echo '<img class="img_recette" src="resources/photos/' . $img_titre .'.jpg" alt="Sample">';
                } else {
                    echo '<img class="img_recette" src="resources/photos/sample.png" alt="Sample">';
                }
            ?>
            </section>
        </article>
    <?php } ?>
</div>
