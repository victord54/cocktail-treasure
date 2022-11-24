<?php
//Débug
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
$login = file_get_contents("data/login");
$password = file_get_contents("data/password");
$dbname = 'cocktail_treasure';

$inscrit = false;
$print_mess_erreur = false;
try {
    $pdo = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
if (isset($_POST['submit'])) {
        $verifs = array(
            "nom" => false,
            "prenom" => false,
            "email" => false,
            "login" => false,
            "password" => false,
            "sexe" => false,
            "naissance" => false,
            "tel" => false,
            "adresse" => false
        );

        if (isset($_POST["sexe"]) && ($_POST["sexe"] == "f" || $_POST["sexe"] == "h")) {
            $verifs["sexe"] = true;
        } else if (!isset($_POST["sexe"])) {
            $verifs["sexe"] = true;
        }

        $patern = '/[^a-zA-Z éèêëíìîï-]/';
        if (strlen(trim($_POST["nom"])) > 0 && !preg_match($patern, $_POST["nom"])) {
            $verifs["nom"] = true;
        } else if (strlen(trim($_POST["nom"])) == 0) {
            $verifs["nom"] = true;
        }

        $patern = '/[^a-zA-Z éèêëíìîï-]/';
        if (strlen(trim($_POST["prenom"])) > 0 && !preg_match($patern, $_POST["prenom"])) {
            $verifs["prenom"] = true;
        } else if (strlen(trim($_POST["prenom"])) == 0) {
            $verifs["prenom"] = true;
        }

        if (strlen($_POST["dob"]) > 0) {
            $date = date_parse($_POST["dob"]);
            $verifs["naissance"] = checkdate($date["month"], $date["day"], $date["year"]);
        } else if (strlen($_POST["dob"]) == 0) {
            $verifs["naissance"] = true;
        }

        if (strlen($_POST["email"]) > 0) {
            $tmp = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
            if ($tmp == false) {
                $verifs["email"] = false;
            } else {
                $verifs["email"] = true;
            }
        } else {
            $verifs["email"] = true;
        }

        if (strlen($_POST["login"]) > 0) {
            $verifs["login"] = true;
        }

        if (strlen($_POST["password"]) > 0) {
            $verifs["password"] = true;
        }

        $patern = "/[^0-9+]/";
        if (strlen($_POST["tel"]) > 0 && !preg_match($patern, $_POST["tel"])) {
            $verifs["tel"] = true;
        } else if (strlen($_POST["email"]) == 0) {
            $verifs["tel"] = true;
        }

        if (strlen($_POST["adresse"]) > 0) {
            $verifs["adresse"] = true;
        } else if (strlen($_POST["adresse"]) == 0) {
            $verifs["adresse"] = true;
        }

        $print_mess_erreur = false;
        foreach ($verifs as $key => $value) {
            if ($value == false) {
                $print_mess_erreur = true;
            }
        }

        if (!$print_mess_erreur) {
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
                if (strlen($_POST["nom"]) > 0)
                    $statement->bindValue(':nom', $_POST['nom']);
                else
                    $statement->bindValue(':nom', NULL);

                if (strlen($_POST["prenom"]) > 0)
                    $statement->bindValue(':prenom', $_POST['prenom']);
                else
                    $statement->bindValue(':prenom', NULL);

                if (strlen($_POST["email"]) > 0)
                    $statement->bindValue(':email', $_POST['email']);
                else
                    $statement->bindValue(':email', NULL);

                $statement->bindValue(':lo', $_POST['login']);
                $statement->bindValue(':mdp', password_hash($_POST['password'], PASSWORD_DEFAULT));

                if (isset($_POST["sexe"]))
                    $statement->bindValue(':sexe', $_POST['sexe']);
                else
                    $statement->bindValue(':sexe', NULL);

                if (strlen($_POST["dob"]) > 0)
                    $statement->bindValue(':date_naissance', $_POST['dob']);
                else
                    $statement->bindValue(':date_naissance', NULL);

                if (strlen($_POST["tel"]) > 0)
                    $statement->bindValue(':num_tel', $_POST['tel']);
                else
                    $statement->bindValue(':num_tel', NULL);

                if (strlen($_POST["adresse"]) > 0)
                    $statement->bindValue(':adresse_postale', $_POST['adresse']);
                else
                    $statement->bindValue(':adresse_postale', NULL);

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
    <link rel="stylesheet" href="styles/inscription_style.css">
    <title>Page d'inscription</title>
</head>
<body>
    <?php
        include_once('header.php');
        // var_dump($verifs);
        // echo "<br>";
        // var_dump($print_mess_erreur);
        // echo "<pre>";
        // var_dump($_POST);
        // echo "</pre>";
    ?>
    <?php if ($print_mess_erreur) { ?>
        <div id="erreur_formulaire">
            <h1>Erreurs dans le formulaire</h1>
            <?php
                echo("<ul>\n");
                foreach ($verifs as $key => $value) {
                    if ($value == false) {
                        echo("\t\t<li>$key</li>\n");
                    }
                }
                echo("\t</ul>\n<br><br>\n");
            ?>
        </div>
    <?php } ?>
    <?php if (!$inscrit) {  ?>
        <div id="inscription">
            <form id="inscription_form" action="#" method="post">
                <label>Sexe</label><br>
                <label for="h">Homme</label><input type="radio" name="sexe" id="h" value="h" <?php if (isset($_POST["sexe"]) && $_POST["sexe"] == "h") echo("checked") ?> >
                <label for="f">Femme</label><input type="radio" name="sexe" id="f" value="f" <?php if (isset($_POST["sexe"]) && $_POST["sexe"] == "f") echo("checked") ?> >
                <br>
                <label for="nom">Nom</label><input type="text" name="nom" id="nom" <?php if (isset($_POST["nom"])) echo("value=\"" . $_POST["nom"] . "\"") ?>>
                <br>
                <label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom" <?php if (isset($_POST["prenom"])) echo("value=\"" . $_POST["prenom"] . "\"") ?>>
                <br>
                <label for="email">E-mail</label><input type="email" name="email" id="email" <?php if (isset($_POST["email"])) echo("value=\"" . $_POST["email"] . "\"") ?>>
                <br>
                <label for="login">Login</label><input type="text" name="login" id="login" required="required" <?php if (isset($_POST["login"])) echo("value=\"" . $_POST["login"] . "\"") ?>>
                <br>
                <label for="password">Mot de passe</label><input type="password" name="password" id="password" required="required">
                <br>
                <label for="dob">Date de naissance</label><input type="date" name="dob" id="dob" <?php if (isset($_POST["dob"])) echo("value=\"" . $_POST["dob"] . "\"") ?>>
                <br>
                <label for="tel">N° de téléphone</label><input type="text" name="tel" id="tel" <?php if (isset($_POST["tel"])) echo("value=\"" . $_POST["tel"] . "\"") ?>>
                <br>
                <label for="adresse">Adresse postale</label><input type="text" name="adresse" id="adresse" <?php if (isset($_POST["adresse"])) echo("value=\"" . $_POST["adresse"] . "\"") ?>>
                <br>
                <input type="submit" name="submit" value="Inscription">
            </form>
        </div>
    <?php } else { echo "<p>Inscription réussie !</p>"; } ?>
    <?php include_once('footer.php'); ?>
    <script src="scripts/inscription_script.js"></script>
</body>
</html>