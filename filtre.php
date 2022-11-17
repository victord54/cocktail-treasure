<nav class="nav-filtre">
    <?php 
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

    // Renvoie le nom des catégories qui n'ont pas de super-categories --> Aliment
    $sqlQuery = "SELECT nom FROM categorie WHERE id_categorie NOT IN (SELECT id_categorie FROM possede_spc);";
    $statement = $pdo->prepare($sqlQuery);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->execute();

    $haut_hierarchie = $statement->fetchAll();
    foreach($haut_hierarchie as $row) {
        //var_dump($row);
        echo "<span>\n";
        echo ($row['nom']);
        echo "</span>\n";
    }
    ?>
</nav>