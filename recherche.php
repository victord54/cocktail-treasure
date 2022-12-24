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
    <link rel="stylesheet" href="styles/index_style.css">
    <link rel="stylesheet" href="styles/recettes_grid_style.css">
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
            <label for="ingredients">Ingédients recherchés :</label><input type="text" oninput="formState()" name="ingredients" id='ingredients' placeholder="champagne, poire, ...">
            <br>
            <label for="no_ingredients">Ingédients non désirés :</label><input type="text" oninput="formState()" name="no_ingredients" id="no_ingredients" placeholder="noix, oeufs, ...">
            <br>
            <label for="recette">Recette recherchée :</label><input type="text" list="liste_recette" oninput="formState()" oninput="completion()" name="recette" id="recette" placeholder="Alerte à Malibu">
            <datalist id="liste_recette">
                <?php
                    foreach ($list_recette as $recette) {
                        $tmp = $recette['titre'];
                        echo "<option value='$tmp'>";
                    }
                ?>
            </datalist>
            <br>
            <input type="submit" value="Rechercher">
        </form>
    <?php } else { ?>
        <?php
            function getAllSubCat($pdo, $cat) {
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
                $categories = $statement->fetchAll();
                $tmp = array();
                foreach ($categories as $categorie) {
                    // Pour chaque sous_cat de celle de base, on regarde si elle a elle meme une sous_cat
                    $sqlQuery = "SELECT * FROM categorie NATURAL JOIN possede_ssc WHERE nom LIKE :nom";
                    $statement = $pdo->prepare($sqlQuery);
                    $statement->bindValue(':nom', $categorie['nom']);
                    $statement->setFetchMode(PDO::FETCH_ASSOC);
                    $statement->execute();
                    // Si elle en a pas on l'ajoute au tab à renvoyer
                    if ($statement->columnCount() < 1)
                        array_push($tmp, $categories);
                    // Sinon on recommence avec cette sous_cat 
                    else {
                        $again = true;
                        // Normalement la boucle devrait s'arrêter si la requete renvoie un nombre 0 de lignes ce qui veut dire qu'on arrive
                        // à une feuille du graphe des catégories donc on peut l'ajouter au tableau à renvoyer
                        while ($again) {
                            $sqlQuery = "SELECT categorie.id_categorie, categorie.nom
                            FROM categorie WHERE nom IN
                                (SELECT sous_categorie.nom
                                FROM categorie JOIN possede_ssc USING (id_categorie) JOIN sous_categorie USING (id_sous_categorie)
                                WHERE categorie.nom LIKE :nom);";
                            $statement = $pdo->prepare($sqlQuery);
                            $statement->bindValue(':nom', $categorie['nom']);
                            $statement->setFetchMode(PDO::FETCH_ASSOC);
                            $statement->execute();
                            if ($statement->columnCount() < 1)
                                $again = false;
                            else {
                                $tt = $statement->fetchAll();
                                array_push($tmp, $tt);
                            }
                        }
                    }
                }
                var_dump($tmp);
                return $tmp;
            }
            $ingrs = explode(", ", $_GET['ingredients']);
            getAllSubCat($pdo, $ingrs[0]);

            // $recettes_id = array();
            // $no_recettes_id = array();
            // if (!empty($_GET['ingredients'])) {
            //     $ingrs = explode(", ", $_GET['ingredients']);
            //     foreach ($ingrs as $ingr) {
            //         if ($ingr != ''){
            //             $ingr = mb_strtoupper($ingr, 'UTF-8');
            //             $sqlQuery = "SELECT id_recette FROM contient_ingredient JOIN categorie USING (id_categorie, id_categorie) WHERE upper(nom) LIKE :nom;";
            //             $statement = $pdo->prepare($sqlQuery);
            //             $statement->bindValue(':nom', '%' . $ingr . '%');
            //             $statement->setFetchMode(PDO::FETCH_ASSOC);
            //             $statement->execute();
            //             array_push($recettes_id, $statement->fetchAll());
            //         }
            //     }
            // }

            // if (!empty($_GET['no_ingredients'])) {
            //     $no_ingrs = explode(", ", $_GET['no_ingredients']);
            //     foreach ($no_ingrs as $no_ingr) {
            //         if ($no_ingr != '') {
            //             $no_ingr = mb_strtoupper($no_ingr, 'UTF-8');
            //             var_dump($no_ingr);
            //             $sqlQuery = "SELECT id_recette FROM contient_ingredient JOIN categorie USING (id_categorie, id_categorie) WHERE upper(nom) LIKE :nom;";
            //             $statement = $pdo->prepare($sqlQuery);
            //             $statement->bindValue(':nom', '%' . $no_ingr . '%');
            //             $statement->setFetchMode(PDO::FETCH_ASSOC);
            //             $statement->execute();
            //             array_push($no_recettes_id, $statement->fetchAll());
            //         }
            //     }
            // }

            if (!empty($_GET['recette'])) {
                $sqlQuery = "SELECT titre, id_recette FROM recette WHERE UPPER(titre) LIKE :titre;";
                $statement = $pdo->prepare($sqlQuery);
                $statement->bindValue(':titre', '%'. strtoupper($_GET['recette']).'%');
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $recette_search =  $statement->fetchAll();
            }

            
            $sqlQuery = "SELECT titre, id_recette FROM recette WHERE ";
            // Ajoute les recettes avec les ingrédients
            foreach ($recettes_id as $recette_id) {
                foreach ($recette_id as $id) {
                    $sqlQuery = $sqlQuery . 'id_recette = '. $id['id_recette'] . ' OR ';
                }
            }
            
            if (!empty($recettes_id)) {
                $sqlQuery = $sqlQuery . '0 ';
                if (!empty($no_recettes_id))
                    $sqlQuery = $sqlQuery . 'AND ';
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