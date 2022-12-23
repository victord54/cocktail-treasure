<?php
$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

if(isset($_POST['ingredient'])){
    
    $ingredient = $_POST['ingredient'];
    $sqlQuery = "SELECT nom FROM categorie WHERE nom like'%".$ingredient."%'";
    $statement = $pdo->prepare($sqlQuery);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->execute();
    $reponse = $statement->fetchAll();

    $tableau = array();
    foreach ($reponse as $rep){
        array_push($tableau, $rep['nom']);
    }
    echo json_encode($tableau);
}

?>