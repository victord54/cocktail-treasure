<?php 
  session_start();
  ini_set('display_errors', TRUE);
  error_reporting(E_ALL);
  $id_recette = $_POST['id_recette'];

  if (isset($_SESSION['user_login'])){
    $login = file_get_contents("data/login");
    $password = file_get_contents("data/password");
    $dbname = 'cocktail_treasure';
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
  
    $login = $_SESSION['user_login'];
    $sql = "SELECT id_utilisateur FROM utilisateur WHERE login=\"$login\""; 
    $requete = $pdo->query($sql);
    $id = $requete->fetch();
  
    $sqlFav = "INSERT INTO favoris (id_utilisateur, id_recette) VALUES (:id, :recette)";
    $query = $pdo->prepare($sqlFav);
    $query->bindValue(":id", $id['id_utilisateur'], PDO::PARAM_INT);
    $query->bindValue(":recette", $id_recette, PDO::PARAM_INT);
    $query->execute(); 
  }

  if (isset($_SESSION['favoris'])){
    array_push($_SESSION['favoris'], $id_recette);
  } else {
    $_SESSION['favoris'] = array();
    array_push($_SESSION['favoris'], $id_recette);
  }


?>