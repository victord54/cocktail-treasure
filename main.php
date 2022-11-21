<?php

ini_set('display_errors', TRUE);
error_reporting(E_ALL);

if (isset($_GET["id"])){
    $id = $_GET["id"];
} else {
    //Pas d'id donc page index de base donc on est en haut de la hiérarchie
    $id = null;
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
$recettes= array();

if ($id == null){
    $sqlQuery = "SELECT titre FROM recette;";
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
    <?php foreach ($recettes as $recette) { ?>
        <article class="conteneur_recette">
            <section class="recette"><?php 
            if ($id_present) {
                echo '<p>' . $recette . '</p>';
             } else {
                echo '<p>'. $recette['titre'] . '</p>';
             } ?></section>
        </article>
    <?php } ?>
</div>
