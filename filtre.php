<?php
    ini_set('display_errors', TRUE);
    error_reporting(E_ALL);
    //Connexion bdd
    $login = file_get_contents("data/login");
    $password = file_get_contents("data/password");
    $dbname = 'cocktail_treasure';
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    //Vérifie id
    if (!isset($_GET["id"]) || !empty($_GEt["id"])){
        //Pas d'id donc tout en haut de la hiérarchie
        // Renvoie le nom des catégories qui n'ont pas de super-categories --> Aliment
        $sqlQuery = "SELECT * FROM categorie WHERE id_categorie NOT IN (SELECT id_categorie FROM possede_spc);";
        $statement = $pdo->prepare($sqlQuery);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute();
        $haut_hierarchie = $statement->fetchAll();
    } else {
        //Récupère id
        $id_p = $_GET["id"];
    }
    
    
    foreach($haut_hierarchie as $row) {
        //print($row['id_categorie']);
        echo '<a href="index.php">' . ($row['nom']) . '</a>';
        echo ' > ';
    }
   echo '</br></br>';
    
?>



<nav class="nav-filtre">
    <?php
    
    foreach($haut_hierarchie as $row) {
        //print($row['id_categorie']);
        echo "<span style=\"color:red\">\n";
        echo ($row['nom']);
        echo "</span>\n";
        echo "<ul>";

        //Récupère les sous catégories
        $id = $row['id_categorie'];
        $sqlQuery = "SELECT * FROM sous_categorie JOIN possede_ssc USING (id_sous_categorie,id_sous_categorie) WHERE id_categorie=$id";
        $statement = $pdo->prepare($sqlQuery);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute();
        $sous_cat = $statement->fetchAll();
        //var_dump($sous_cat);

        foreach($sous_cat as $cat){
            echo "<li>";
            //Récupère id de la catégorie correspondant à la sous-catégorie
            $nom = $cat['nom'];
            $sqlQuery = "SELECT id_categorie FROM categorie  WHERE nom=\"$nom\";";
            $statement = $pdo->prepare($sqlQuery);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $id = $statement->fetch();
            //var_dump($id);
            echo '<a href="index.php&id=' . ($id['id_categorie']) . '">' . ($cat['nom']) . '</a>';
            echo '</li>';
        }
        echo "</ul>";

    }
    ?>
</nav>