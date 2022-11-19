<?php
    ini_set('display_errors', TRUE);
    error_reporting(E_ALL);
    $direction_hierarchie = array();

    //On récupère le tableau de la hiérarchie actuelle si il existe
    if (isset($_SESSION['data'])){
        $direction_hierarchie = $_SESSION['data'];
        $id_p = $_GET["id"]; //id de la page actuelle
        $nb= 0;
        foreach ($direction_hierarchie as $cat){ //On check le positionnement de la page actuelle dans le tab de la hiérarchie
            if ($cat[0]['id_categorie'] == $id_p){ //si retour en arrière dans la hiérarchie -> supprime tous les éléments après celui
                $nb_case_a_enlever = count($direction_hierarchie); // actuelle.
                while ($nb_case_a_enlever > $nb){
                    array_pop($direction_hierarchie);
                    $nb_case_a_enlever --;
                    //var_dump($direction_hierarchie);
                }
            }
            $nb++;
        }
    }
    

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
    if (!isset($_GET["id"])){
        //Pas d'id donc tout en haut de la hiérarchie
        // Renvoie le nom des catégories qui n'ont pas de super-categories --> Aliment
        $sqlQuery = "SELECT * FROM categorie WHERE id_categorie NOT IN (SELECT id_categorie FROM possede_spc);";
        $statement = $pdo->prepare($sqlQuery);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute();
        $haut_hierarchie = $statement->fetchAll();
        array_push($direction_hierarchie,$haut_hierarchie); 
    } else {
        //Récupère id
        $id_p = $_GET["id"];
        $sqlQuery = "SELECT * FROM categorie WHERE id_categorie=:id;";//On mets :id pcq l'id vient de l'ext -> pas confiance
        $statement = $pdo->prepare($sqlQuery);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindValue(":id",$id_p,PDO::PARAM_INT); //On injecte la valeur en vérifiant que c'est bien un entier
        $statement->execute();
        $haut_hierarchie = $statement->fetchAll();
        array_push($direction_hierarchie,$haut_hierarchie); //push la nouvelle catégorie dans la hiérarchie actuelle
    }
    
    $_SESSION['data'] = $direction_hierarchie; //set la direction de hierarchie de la session
    
    $nb_cat = count($direction_hierarchie);
    $nb_cat_compte=0;
    echo "\n\t<div id='fil_ariane'>";
    foreach($direction_hierarchie as $row) {
        echo "\n\t\t";
        echo '<a href="index.php?id=' . ($row[0]['id_categorie']) . '">' . ($row[0]['nom']) . '</a>';
        echo "\n";
        $nb_cat_compte++;
        if (!($nb_cat_compte == $nb_cat)){
            echo ' > ';
        }
    }
    echo "\t</div>\n";
    echo '</br></br>';
?>



<nav class="nav-filtre">
    <?php
        foreach($haut_hierarchie as $row) {
            //print($row['id_categorie']);
            echo "<span class=\"super_categorie\">";
            echo ($row['nom']);
            echo "</span>\n";
            echo "<ul>\n";

            //Récupère les sous catégories
            $id = $row['id_categorie'];
            $sqlQuery = "SELECT * FROM sous_categorie JOIN possede_ssc USING (id_sous_categorie,id_sous_categorie) WHERE id_categorie=$id";
            $statement = $pdo->prepare($sqlQuery);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $sous_cat = $statement->fetchAll();
            //var_dump($sous_cat);
        
            foreach($sous_cat as $cat){
                echo "\t<li>";
                //Récupère id de la catégorie correspondant à la sous-catégorie pour la redirection
                $nom = $cat['nom'];
                $sqlQuery = "SELECT id_categorie FROM categorie  WHERE nom=\"$nom\";";
                $statement = $pdo->prepare($sqlQuery);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->execute();
                $id = $statement->fetch();
                //var_dump($id);
                echo "\n\t\t";
                echo '<a href="index.php?id=' . ($id['id_categorie']) . '">' . ($cat['nom']) . '</a>';
                echo "\n\t</li>\n";
            }
            echo "</ul>\n";
        }
    ?>
</nav>