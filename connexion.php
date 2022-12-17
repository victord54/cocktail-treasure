<?php
session_start();
//Débug
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
if (isset($_POST['submit'])) {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $sqlQuery = "SELECT id_utilisateur, prenom, `login`, mdp FROM utilisateur WHERE `login` = :id;";
        $statement = $pdo->prepare($sqlQuery);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindValue(":id", $_POST['login']);
        $statement->execute();
        $identifiants = $statement->fetch();
        if ($identifiants) {
            if (password_verify($_POST['password'], $identifiants['mdp'])) {
                $_SESSION['user_login'] = $identifiants['login'];
                $_SESSION['user_name'] = $identifiants['prenom'];
                if (!isset($_SESSION['favoris'])){
                    $_SESSION['favoris'] = array();
                }

                //On mets dans la bdd toutes les recettes favorites qui n'y étaient pas au préalable
                foreach ($_SESSION['favoris'] as $fav){
                    $sqlFav = "INSERT IGNORE INTO favoris (id_utilisateur, id_recette) VALUES (:id, :recette)";
                    $query = $pdo->prepare($sqlFav);
                    $query->bindValue(":id", $identifiants['id_utilisateur'], PDO::PARAM_INT);
                    $query->bindValue(":recette", $fav, PDO::PARAM_INT);
                    $query->execute();
                }

                //On remets à 0 le tableau des favoris
                $_SESSION['favoris'] = array();
                //On récupère toutes les recettes favorites
                $sql = "SELECT id_recette FROM favoris WHERE id_utilisateur=:id_user;";
                $statement = $pdo->prepare($sql);
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $statement->bindValue(":id_user", $identifiants['id_utilisateur']);
                $statement->execute();
                $favoris = $statement->fetchAll();
                
                foreach ($favoris as $fav){
                    array_push($_SESSION['favoris'], $fav['id_recette']);
                }
                 
                header("location: index.php");
            } else {
                echo "Mauvais mot de passe " . $identifiants['prenom'];
            }
        } else {
            echo "Je ne vous connais pas";
        }
    }
} else if (isset($_SESSION['user_login'])) {
    header("location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/connection_style.css">
    <title>Page de connexion</title>
</head>
<body>
    <?php include_once('header.php'); ?>
    <div id="connexion">
        <form id="connexion_form" action="#" method="post">
            <label for="login">Login</label><input type="text" name="login" id="login">
            <br>
            <label for="password">Mot de passe</label><input type="password" name="password" id="password">
            <input type="submit" name="submit" value="Connexion">
        </form>
    </div>
    <?php include_once('footer.php'); ?>
</body>
</html>