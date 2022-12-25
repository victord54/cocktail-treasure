<?php 
    session_start();

    if (isset($_GET["id"])){
        $id = $_GET["id"];
        //var_dump($id);
    } else {
        header("Location: index.php");
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

    $sqlQuery = "SELECT * FROM recette WHERE id_recette=:id;";
    $statement = $pdo->prepare($sqlQuery);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->execute();
    $recette = $statement->fetchAll();
    $recette = $recette[0];
    //var_dump($recette);
    $liste_ingredients = explode("|", $recette['ingredients']);
    //var_dump($liste_ingredients)

    if (isset($_SESSION['favoris'])) {
        $fav = in_array($id, $_SESSION['favoris']);
    } else {
        $fav = 0;
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/recette_style.css">
    <title><?php echo $recette['titre']; ?></title>
</head>
<body>

<script type="text/javascript">
jQuery(document).ready(function($){
    $('#addFav').on('click', function(e){
        var id_recette = "<?php echo $id; ?>";        
        e.preventDefault();
        $.ajax({
            url: 'ajouter_favoris.php',
            type: 'POST',
            data: {id_recette: id_recette, url_slug: $(location).attr('href')},
            success: function(data){
                $('#addFav').hide();
                $('#removeFav').show();

            }
        });
    });

    $('#removeFav').on('click', function(e){
        var id_recette = "<?php echo $id; ?>";        
        e.preventDefault();
        $.ajax({
            url: 'remove_favoris.php',
            type: 'POST',
            data: {id_recette: id_recette, url_slug: $(location).attr('href')},
            success: function(data){
                $('#addFav').show();
                $('#removeFav').hide();
            }
        });
    });

});
</script>

    <?php echo "\n"; include_once("header.php");?>
    <div id="recette">
        <h1><?php echo $recette['titre'];?></h1>
        <?php 
        $img_titre = str_replace($search, $replace, $recette['titre']);
        $path = 'resources/photos/' . $img_titre . '.jpg';
        if (file_exists($path)){
            echo '<img class="img_recette" src="resources/photos/' . $img_titre .'.jpg" alt="Sample">';
        } else {
            echo '<img class="img_recette" src="resources/photos/sample.png" alt="Sample">';
        }?>
        <fieldset>
            <legend>Ingrédients</legend>
            <p>Pour cette recette, vous aurez besoin de :
                <ul id="liste_ingredients"><?php 
                foreach ($liste_ingredients as $ingredient){
                    echo '<li> ♣ ' . $ingredient . '</li>';
                }
                ?></ul>
            </p>
        </fieldset>
        <fieldset>
            <legend>Etapes</legend>
            <p><?php echo $recette['preparation'];?></p>
        </fieldset>
        <div>
            <button id="addFav" <?php if ($fav) { ?> style="display:none" <?php } ?>>Ajouter aux favoris <span id="coeur_vide"> ♡ </span></button>
            <button id="removeFav" <?php if (!$fav) { ?> style="display:none" <?php } ?>>Enlever des favoris <span id="coeur_plein"> ❤ </span></button>
        </div>
    </div>
    <?php echo "\n"; include_once("footer.php"); echo "\n";?>
</body>
</html>
