<?php
    session_start();

    $login = file_get_contents("data/login");
    $password = file_get_contents("data/password");
    $dbname = 'cocktail_treasure';
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=$dbname;charset=utf8", $login, $password);
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    $sqlQuery = "SELECT * FROM utilisateur WHERE `login` = :id;";
    $statement = $pdo->prepare($sqlQuery);
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $statement->bindValue(":id", $_SESSION['user_login']);
    $statement->execute();
    $infos = $statement->fetch();
    $print_mess_erreur = false;

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
            
            $sqlInsert = "UPDATE utilisateur SET nom = :nom, prenom=:prenom, `login`=:lo, mdp=:mdp, email=:email, sexe=:sexe, date_naissance=:date_naissance, num_tel=:num_tel, adresse_postale=:adresse_postale
            WHERE id_utilisateur=:id_user";

            $statement = $pdo->prepare($sqlInsert);
            $statement->bindValue(':id_user', $infos['id_utilisateur']);

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
                if (isset($_POST['password'])){
                    $statement->bindValue(':mdp', password_hash($_POST['password'], PASSWORD_DEFAULT));
                }

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

                $_SESSION['user_login'] = $_POST['login'];
                $_SESSION['user_name'] =  $_POST['prenom'];
                header("location: profil.php");
            }
        }
    
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/header_style.css">
    <link rel="stylesheet" href="styles/footer_style.css">
    <link rel="stylesheet" href="styles/profil_style.css">

    <title>Mon profil</title>
</head>
<body>
</script>

    <?php echo "\n"; include_once("header.php");?>
    <h1>Mon profil : </h1><br/><br/>
    
            <form action="#" method="post">
            <fieldset id="field1">
            <legend>Informations personnelles</legend>

                <label>Sexe : </label>
                <label for="h">Homme</label><input type="radio" name="sexe" id="h" value="h" <?php if (isset($infos["sexe"]) && $infos["sexe"] == "h") echo("checked") ?> >
                <label for="f">Femme</label><input type="radio" name="sexe" id="f" value="f" <?php if (isset($infos["sexe"]) &&$infos["sexe"] == "f") echo("checked") ?> >
                <br>
                <label for="nom">Nom : </label><input type="text" name="nom" id="nom" 
                <?php 
                if (isset($_POST["nom"])){ //Le formulaire a été envoyé
                    echo ("value=\"" . $_POST["nom"] . "\"");
                    if (!$verifs['nom']){
                        echo "style=\"background-color:red;\"";
                    }
                } else {
                    if (isset($infos["nom"])) { //On affiche les valeurs stocké dans la bdd si elle existe qd pas encore eu de formulaire envoyé
                        echo ("value=\"" . $infos["nom"] . "\"");
                    }
                }
                ?>>
                <?php  if (isset($_POST["nom"])){
                        if (!$verifs['nom']) {
                         echo "<span id=\"message_erreur\">Le nom n'est pas valide.</span>";}
                }?>
                <br>
                <label for="prenom">Prénom : </label><input type="text" name="prenom" id="prenom" 
                <?php 
                if (isset($_POST["prenom"])){
                    echo("value=\"" . $_POST["prenom"] . "\"");
                    if (!$verifs['prenom']){
                        echo "style=\"background-color:red;\"";
                    }
                } else {
                    if (isset($infos["prenom"])) {
                        echo ("value=\"" . $infos["prenom"] . "\"");
                    }
                }
                ?>>
                <?php  if (isset($_POST["prenom"])){
                        if (!$verifs['prenom']) {
                         echo "<span id=\"message_erreur\">Le prénom n'est pas valide.</span>";}
                }?>
                
                <br>
                <label for="dob">Date de naissance : </label><input type="date" name="dob" id="dob" 
                <?php 
                if (isset($_POST["dob"])){
                    echo("value=\"" . $_POST["dob"] . "\"");
                    if (!$verifs['naissance']){
                        echo "style=\"background-color:red;\"";
                    }
                } else {
                    if (isset($infos["date_de_naissance"])) {
                        echo ("value=\"" . $infos["date_de_naissance"] . "\"");
                    }
                }
                ?>>
                <?php  if (isset($_POST["dob"])){
                        if (!$verifs['naissance']) {
                        echo "<span id=\"message_erreur\">La date de naissance n'est pas valide.</span>";}
                }?>
                <br>
            
                <label for="tel">N° de téléphone : </label><input type="text" name="tel" id="tel" 
                <?php 
                if (isset($_POST["tel"])){
                    echo("value=\"" . $_POST["tel"] . "\"");
                    if (!$verifs['tel']){
                        echo "style=\"background-color:red;\"";
                    }
                } else {
                    if (isset($infos["num_tel"]))
                        echo ("value=\"" . $infos["num_tel"] . "\"");
                }
                ?>>
                <?php  if (isset($_POST["tel"])){
                        if (!$verifs['tel']) {
                        echo "<span id=\"message_erreur\">Le numéro de téléphone n'est pas bon.</span>";}
                }?>
                <br>
                <label for="adresse">Adresse postale : </label><input type="text" name="adresse"  size="50" id="adresse" <?php if (isset($infos["adresse_postale"])) echo("value=\"" . $infos["adresse_postale"] . "\"") ?>>
                <br>
            </fieldset>
            <fieldset id="field2">
            <legend>Informations de connexion</legend>
                <label for="email">E-mail : </label><input type="email" name="email" id="email" <?php echo("value=\"" . $infos["email"] . "\"") ?>>
                <br/>
                <label for="login">Login : </label><input type="text" name="login" id="login" required="required" 
                <?php 
                if (isset($_POST["login"])){
                    echo("value=\"" . $_POST["login"] . "\"");
                    if (!$verifs['login']){
                        echo "style=\"background-color:red;\"";
                    }
                } else {
                    echo ("value=\"" . $infos["login"] . "\"");
                }
                ?>>
                <?php  if (isset($_POST["login"])){
                        if (!$verifs['login']) {
                        echo "<span id=\"message_erreur\">Ce login est déjà utilisé.</span>";}
                }?> 
                <br>
                <label for="password">Mot de passe : </label><input type="password" name="password" id="password">
                <br>
            </fieldset>
            <div class="separ"></div>
            <br><br>
            <input type="submit" name="submit" id="valider_bouton" value="Valider les modifications">
    
            </form>
    <?php echo "\n"; include_once("footer.php"); echo "\n";?>
</body>
</html>