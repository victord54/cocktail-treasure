<nav class="nav-filtre">
    <?php 
    //Connexion bdd
    $login = file_get_contents("data/login");
    $password = file_get_contents("data/password");
    try {
        $pdo = new PDO(
            'mysql:host=localhost;charset=utf8', $login, $password);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    $sql = "SELECT `nom` FROM `categorie` WHERE `id_categorie` NOT IN (SELECT `id_categorie` FROM `possede_spc`)"; //La requete marche sur phpmyadmin ça sort bien Aliment 
    $requete = $pdo->query($sql);                                                                                   //Mais ensuite marche po
    //$haut_hierarchie = $requete->fetchAll();
    
    

    //var_dump($haut_hierarchie);
    
    ?>
    <span>Aliments</span>
    <ul>
        <li>Alcool</li>
    </ul>

</nav>