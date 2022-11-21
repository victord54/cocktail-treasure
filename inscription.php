<?php
//Débug
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';

$inscrit = false;
try {
    $pdo = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
if (isset($_POST['submit'])) {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $sqlQuery = "SELECT prenom, mdp FROM utilisateur WHERE `login` = :id;";
        $statement = $pdo->prepare($sqlQuery);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindValue(":id", $_POST['login']);
        $statement->execute();
        $identifiants = $statement->fetch();
        if ($identifiants) {
            echo "Vous êtes déjà inscrit";
        } else {
            $sqlInsert = "INSERT INTO utilisateur(nom, prenom, `login`, mdp, email, sexe, date_naissance, num_tel, adresse_postale)
            VALUES (:nom, :prenom, :lo, :mdp, :email, :sexe, :date_naissance, :num_tel, :adresse_postale);";

            $statement = $pdo->prepare($sqlInsert);
            $statement->bindValue(':nom', $_POST['nom']);
            $statement->bindValue(':prenom', $_POST['prenom']);
            $statement->bindValue(':email', $_POST['email']);
            $statement->bindValue(':lo', $_POST['login']);
            $statement->bindValue(':mdp', password_hash($_POST['password'], PASSWORD_DEFAULT));
            $statement->bindValue(':email', $_POST['email']);
            $statement->bindValue(':sexe', $_POST['sexe']);
            $statement->bindValue(':date_naissance', $_POST['dob']);
            $statement->bindValue(':num_tel', $_POST['tel']);
            $statement->bindValue(':adresse_postale', $_POST['adresse']);

            $statement->execute();
            $inscrit = true;
        }
    }
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
    <title>Page d'inscription</title>
</head>
<body>
    <?php include_once('header.php'); ?>
    <?php if (!$inscrit) {  ?>
        <div id="inscription">
            <form id="inscription_form" action="#" method="post">
                <label for="nom">Nom</label><input type="text" name="nom" id="nom">
                <br>
                <label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom">
                <br>
                <label for="email">E-mail</label><input type="text" name="email" id="email">
                <br>
                <label for="login">Login</label><input type="text" name="login" id="login" required="required">
                <br>
                <label for="password">Mot de passe</label><input type="password" name="password" id="password" required="required">
                <br>
                <label>Sexe</label><br>
                <label for="h">Homme</label><input type="radio" name="sexe" id="h" value="h">
                <label for="f">Femme</label><input type="radio" name="sexe" id="f" value="f">
                <br>
                <label for="dob">Date de naissance</label><input type="date" name="dob" id="dob">
                <br>
                <label for="tel">N° de téléphone</label><input type="text" name="tel" id="tel">
                <br>
                <label for="adresse">Adresse postale</label><input type="text" name="adresse" id="adresse">
                <br>
                <input type="submit" name="submit" value="Inscription">
            </form>
        </div>
    <?php } else { echo "<p>Inscription réussie !</p>"; } ?>
    <?php include_once('footer.php'); ?>
</body>
</html>