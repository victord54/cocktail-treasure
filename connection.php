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
        <form id="connexion_form"action="#" method="post">
            <label for="login">Login</label><input type="text" name="login" id="login">
            <br>
            <label for="password">Mot de passe</label><input type="password" name="password" id="password">zz
            <input type="submit" name="submit" value="Connexion">
        </form>
    </div>
    <?php
    if (isset($_POST['submit'])) {
        if (isset($_POST['login']) && isset($_POST['password'])) {
            $sqlQuery = "SELECT prenom, mdp FROM utilisateur WHERE `login` = :id;";
            $statement = $pdo->prepare($sqlQuery);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->bindValue(":id", $_POST['login']);
            $statement->execute();
            $identifiants = $statement->fetch();
            if ($identifiants) {
                if (password_verify($_POST['password'], $identifiants['mdp'])) {
                    echo 'Bienvenue ' . $identifiants['prenom'];
                } else {
                    echo "Mauvais mot de passe " . $identifiants['prenom'];
                }
            } else {
                echo "Je ne vous connais pas";
            }
        }
    }
    ?>
    <?php include_once('footer.php'); ?>
</body>
</html>