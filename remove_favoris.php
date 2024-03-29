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
  
    $sqlFav = "DELETE FROM favoris WHERE id_recette= :id AND id_recette= :recette";
    $query = $pdo->prepare($sqlFav);
    $query->bindValue(":id", $id['id_utilisateur'], PDO::PARAM_INT);
    $query->bindValue(":recette", $id_recette, PDO::PARAM_INT);
    $query->execute(); 
  }

  unset($_SESSION['favoris'][array_search($id_recette, $_SESSION['favoris'])]);



?>