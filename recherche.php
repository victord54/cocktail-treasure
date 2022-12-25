<?php
session_start();
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
setlocale( LC_ALL, 'french' );

$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

$sqlQuery = "SELECT titre FROM recette";
$statement = $pdo->prepare($sqlQuery);
$statement->setFetchMode(PDO::FETCH_ASSOC);
$statement->execute();
$list_recette = $statement->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/recherche_style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

    <title>Recherche</title>
</head>
<body>
    <?php include_once("header.php"); ?>
    <script>
        $(function() {
            $( "#ingredients" ).autocomplete({
                source: function(requete, reponse) {
                    //Extrait le dernier ingrédient entré
                    var ingredientText = extractLast(requete.term);
                    $.ajax({
                        url: "recherche_requete.php",
                        type: 'post',
                        dataType: "json",
                        data: {
                            ingredient: ingredientText
                        },
                        success: function(data) {
                            reponse(data);
                        }
                    });
            },
                select: function(event,ui ) {
                    //Récupère et split les ingrédients au niveau des ","
                    var terms = split($('#ingredients').val());
                    //Enlève le dernier élément entré par l'utilisateur qui est le début de l'ingrédient
                    terms.pop();
                    //Ajoute l'élément sélectionné
                    terms.push(ui.item.label);
                    //Ajoute au tableau "" pour avoir un dernier élément
                    terms.push( "" );
                    //On sépare les éléments du tableau avec des ","
                    $('#ingredients').val(terms.join( ", " ));

                    return false;
                }
                
            });

            $( "#no_ingredients" ).autocomplete({
                source: function( requete, reponse ) {
                    var ingredientText = extractLast(requete.term);
                    $.ajax({
                        url: "recherche_requete.php",
                        type: 'post',
                        dataType: "json",
                        data: {
                            ingredient: ingredientText
                        },
                        success: function( data ) {
                            console.log(data);
                            reponse( data );
                        }
                    });
            },
            select: function( event, ui ) {
                var terms = split( $('#no_ingredients').val());
                terms.pop();
                terms.push(ui.item.label);    
                terms.push("");
                $('#no_ingredients').val(terms.join( ", " ));

                return false;
            }
                
            });

        });
        function split(val) {
            return val.split( /,\s*/ );
        }
        function extractLast(term) {
            return split(term).pop();
        }
    </script>

    <?php if (empty($_GET['ingredients']) && empty($_GET['no_ingredients']) && empty($_GET['recette'])) { ?>
        <form id="ingr_search_form" action="#" method="get">
            <fieldset>
                <legend>Recherche à partir d'ingrédients</legend>
            <label for="ingredients">Ingédients désirés :</label><input type="text" size="40" oninput="formState()" name="ingredients" id='ingredients' placeholder="champagne, poire, ...">
            <br>
            <label for="no_ingredients">Ingédients non désirés :</label><input type="text" size = "36" oninput="formState()" name="no_ingredients" id="no_ingredients" placeholder="noix, oeufs, ...">
            <br>
            </fieldset>
            <fieldset>
                <legend>Recherche à partir du nom d'une recette</legend>
            <label for="recette">Recette recherchée :</label><input type="text" list="liste_recette" size="40" oninput="formState()" oninput="completion()" name="recette" id="recette" placeholder="Alerte à Malibu">
            </fieldset>
            <datalist id="liste_recette">
                <?php
                    foreach ($list_recette as $recette) {
                        $tmp = $recette['titre'];
                        echo "<option value='$tmp'>";
                    }
                ?>
            </datalist>
            <br>
            <input type="submit" id="rechercher_bouton" value="Rechercher">
        </form>
    <?php } else { ?>
        <?php
            function getAllSubCat($pdo, $cat, $tmp) {
                // On récup toutes les sous cat de la catégorie qu'on a inscrit sur le formulaire
                $sqlQuery = "SELECT categorie.id_categorie, categorie.nom
                FROM categorie WHERE nom IN
                    (SELECT sous_categorie.nom
                    FROM categorie JOIN possede_ssc USING (id_categorie) JOIN sous_categorie USING (id_sous_categorie)
                    WHERE categorie.nom LIKE :nom);";
                $statement = $pdo->prepare($sqlQuery);
                $statement->bindValue(':nom', $cat);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                
                 // Check si sous-catégorie si non -> ajout tableau
                if ($statement->rowCount() < 1){
                    array_push($tmp, $cat);
                } else {
                    $categories = $statement->fetchAll();
                    foreach ($categories as $categorie) {
                        $tmp = getAllSubCat($pdo, $categorie['nom'], $tmp);
                    }
                }
                return $tmp;
            }

            $recettes_id = array();
            $no_recettes_id = array();

            if (!empty($_GET['ingredients'])) {
                $ingrs = explode(", ", $_GET['ingredients']);
                $ingredients = array();
                for ($i = 0; $i < count($ingrs); $i++) {
                    if ($ingrs[$i] != '') {
                        $ingr = array();
                        $ingr = getAllSubCat($pdo, $ingrs[$i], $ingr);
                        array_push($ingredients, $ingr);
                    }
                }
                $i = 0;
                $j = 0;
                $sqlQuery = "SELECT id_recette FROM contient_ingredient NATURAL JOIN categorie WHERE ";
                foreach ($ingredients as $ing) {
                    if ($j == 0){
                        //$sqlQuery = $sqlQuery . '(';
                    } else {
                        $sqlQuery = $sqlQuery . ' AND id_recette IN (SELECT id_recette FROM contient_ingredient NATURAL JOIN categorie WHERE ';
                    }
                    foreach ($ing as $in) {
                        if ($i == 0){
                            $sqlQuery = $sqlQuery . 'nom = "' . $in . '"'; 
                        } else{
                            $sqlQuery = $sqlQuery . ' OR nom = "' . $in . '"'; 
                        }
                        $i+=1;
                    }
                    $sqlQuery = $sqlQuery . str_repeat(')',$j);
                    $j+=1;
                    $i = 0;
                }
                //var_dump($sqlQuery);

                $statement = $pdo->prepare($sqlQuery);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                array_push($recettes_id, $statement->fetchAll());
            }

            if (!empty($_GET['no_ingredients'])) {
                $ingrs = explode(", ", $_GET['no_ingredients']);
                $ingredients = array();
                for ($i = 0; $i < count($ingrs); $i++) {
                    if ($ingrs[$i] != '') {
                        $ingr = array();
                        $ingr = getAllSubCat($pdo, $ingrs[$i], $ingr);
                        array_push($ingredients, $ingr);
                    }
                }
                $i = 0;
                $sqlQuery = "SELECT id_recette FROM contient_ingredient NATURAL JOIN categorie WHERE ";
                foreach ($ingredients as $ing) {
                    foreach ($ing as $in) {
                        if ($i == 0){
                            $sqlQuery = $sqlQuery . 'nom = "' . $in . '"'; 
                        } else{
                            $sqlQuery = $sqlQuery . ' OR nom = "' . $in . '"'; 
                        }
                        $i+=1;
                    }
                }
                //var_dump($sqlQuery);

                $statement = $pdo->prepare($sqlQuery);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                array_push($no_recettes_id, $statement->fetchAll());
            }

            if (!empty($_GET['recette'])) {
                $sqlQuery = "SELECT titre, id_recette FROM recette WHERE UPPER(titre) LIKE :titre;";
                $statement = $pdo->prepare($sqlQuery);
                $statement->bindValue(':titre', '%'. strtoupper($_GET['recette']).'%');
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $recette_search =  $statement->fetchAll();
            }

            
            $sqlQuery = "SELECT titre, id_recette FROM recette WHERE ";
            if (!empty($recettes_id))
                $sqlQuery = $sqlQuery . '(';
            // Ajoute les recettes avec les ingrédients
            foreach ($recettes_id as $recette_id) {
                foreach ($recette_id as $id) {
                    $sqlQuery = $sqlQuery . 'id_recette = '. $id['id_recette'] . ' OR ';
                }
            }
            
            if (!empty($recettes_id)) {
                $sqlQuery = $sqlQuery . '0)';
                if (!empty($no_recettes_id))
                    $sqlQuery = $sqlQuery . ' AND ';
            } 

            // Ajoute les recettes sans les ingrédients
            foreach ($no_recettes_id as $no_recette_id)
                foreach ($no_recette_id as $id) {
                    $sqlQuery = $sqlQuery . 'id_recette != '. $id['id_recette'] . ' AND ';
                }
            if (!empty($no_recettes_id))
                $sqlQuery = $sqlQuery . '1';
            
            if (!empty($_GET['recette'])) {
                $recettes = $recette_search;
            } else {
                //var_dump($sqlQuery);
                $statement = $pdo->prepare($sqlQuery);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $recettes = $statement->fetchAll();
            }
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

    <script src="scripts/recherche_script.js"></script>
</body>
</html>