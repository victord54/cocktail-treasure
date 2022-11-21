<?php
use FFI\Exception;
   //Débug
   ini_set('display_errors', TRUE);
   error_reporting(E_ALL);
   include_once("data/Donnees.inc.php");

   // Va chercher le login dans un fichier externe pour plus de sécurité et éviter de devoir le changer à chaque commit (push).
   $login = file_get_contents("data/login");
   // Va chercher le mdp dans un fichier externe.
   $password = file_get_contents("data/password");
   try {
      // Connexion à la bdd.
      $pdo = new PDO('mysql:host=localhost;charset=utf8', $login, $password);
   } catch (Exception $e) {
      die('Erreur connexion à MySQL : ' . $e->getMessage());
   }

   // Création de la bdd.
   $dbname = 'cocktail_treasure';
   // Création des tables.
   $sql = "
   DROP DATABASE IF EXISTS $dbname;
   CREATE DATABASE $dbname;
   USE $dbname;
   
   CREATE TABLE utilisateur(
      id_utilisateur INT AUTO_INCREMENT,
      nom VARCHAR(50),
      prenom VARCHAR(50),
      login VARCHAR(50) NOT NULL,
      mdp VARCHAR(200) NOT NULL,
      email VARCHAR(50),
      sexe VARCHAR(50),
      date_naissance DATE,
      num_tel VARCHAR(50),
      adresse_postale VARCHAR(50),
      PRIMARY KEY(id_utilisateur),
      UNIQUE(login)
   );
 
   CREATE TABLE recette(
      id_recette INT AUTO_INCREMENT,
      titre VARCHAR(250) NOT NULL,
      ingredients VARCHAR(300) NOT NULL,
      preparation TEXT NOT NULL,
      PRIMARY KEY(id_recette),
      UNIQUE(titre)
   );
 
   CREATE TABLE categorie(
      id_categorie INT AUTO_INCREMENT,
      nom VARCHAR(50) NOT NULL,
      PRIMARY KEY(id_categorie),
      UNIQUE(nom)
   );
 
   CREATE TABLE super_categorie(
      id_super_categorie INT AUTO_INCREMENT,
      nom VARCHAR(50) NOT NULL,
      PRIMARY KEY(id_super_categorie),
      UNIQUE(nom)
   );
 
   CREATE TABLE sous_categorie(
      id_sous_categorie INT AUTO_INCREMENT,
      nom VARCHAR(50) NOT NULL,
      PRIMARY KEY(id_sous_categorie),
      UNIQUE(nom)
   );
   
   CREATE TABLE possede_ssc(
      id_categorie INT,
      id_sous_categorie INT,
      PRIMARY KEY(id_categorie, id_sous_categorie),
      FOREIGN KEY(id_categorie) REFERENCES categorie(id_categorie),
      FOREIGN KEY(id_sous_categorie) REFERENCES sous_categorie(id_sous_categorie)
   );
   
   CREATE TABLE possede_spc(
      id_categorie INT,
      id_super_categorie INT,
      PRIMARY KEY(id_categorie, id_super_categorie),
      FOREIGN KEY(id_categorie) REFERENCES categorie(id_categorie),
      FOREIGN KEY(id_super_categorie) REFERENCES super_categorie(id_super_categorie)
   );
   
   CREATE TABLE contient_ingredient(
      id_recette INT,
      id_categorie INT,
      PRIMARY KEY(id_recette, id_categorie),
      FOREIGN KEY(id_recette) REFERENCES recette(id_recette),
      FOREIGN KEY(id_categorie) REFERENCES categorie(id_categorie)
   );
   
   CREATE TABLE favoris(
      id_utilisateur INT,
      id_recette INT,
      PRIMARY KEY(id_utilisateur, id_recette),
      FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id_utilisateur),
      FOREIGN KEY(id_recette) REFERENCES recette(id_recette)
   );
   ";

   try {
      $pdo->exec($sql);
   } catch (Exception $e) {
      die('Erreur création cocktail_treasure: ' . $e->getMessage());
   }

   foreach ($Hierarchie as $cat=>$ss_sp_cat) {
      try {
         // Insère la catégorie dans la table categorie.
         $sql = "INSERT INTO categorie(nom) VALUES (:nom)";
         $query = $pdo->prepare($sql);
         $query->bindValue(":nom", $cat, PDO::PARAM_STR);
         $query->execute();
      } catch (Exception $e) {
         die('Erreur insertion des données dans la table catégorie: ' . $e->getMessage());
      }


      // Récupère l'id de la catégorie actuelle.
      $sql_id_cat = "SELECT id_categorie FROM categorie WHERE nom = \"$cat\"";
      $requete = $pdo->query($sql_id_cat);
      $id_cat = $requete->fetch();

      if (isset($ss_sp_cat['sous-categorie'])) {
         foreach($ss_sp_cat['sous-categorie'] as $ss_cat) {
            // Insère la sous-catégorie dans la table sous_categorie.
            $sql = "INSERT IGNORE INTO sous_categorie(nom) VALUES (:nom)";
            $query = $pdo->prepare($sql);
            $query->bindValue(":nom", $ss_cat, PDO::PARAM_STR);
            $query->execute();

            // Récupère l'id de la sous-catégorie actuelle que l'on vient de créer.
            $sql_id_ss_cat = "SELECT id_sous_categorie FROM sous_categorie WHERE nom = \"$ss_cat\"";
            $requete_ss = $pdo->query($sql_id_ss_cat);
            $id_ss_cat = $requete_ss->fetch();
            
            // Link la catégorie à sa sous-catégorie dans la table possede_ssc.
            $sql = "INSERT INTO possede_ssc(id_categorie, id_sous_categorie) VALUES (:id_categorie, :id_sous_categorie)";
            $query = $pdo->prepare($sql);
            $query->bindValue(":id_categorie", $id_cat['id_categorie'], PDO::PARAM_INT);
            $query->bindValue(":id_sous_categorie", $id_ss_cat['id_sous_categorie'], PDO::PARAM_INT);
            $query->execute();
         }
      }
      if (isset($ss_sp_cat['super-categorie'])) {
         foreach($ss_sp_cat['super-categorie'] as $sp_cat) {
            // Insère la super-catégorie dans la table super_categorie.
            $sql = "INSERT IGNORE INTO super_categorie(nom) VALUES (:nom)";
            $query = $pdo->prepare($sql);
            $query->bindValue(":nom", $sp_cat, PDO::PARAM_STR);
            $query->execute();

            // Récupère id de la super-catégorie actuelle que l'on vient de créer.
            $sql_id_sp_cat = "SELECT id_super_categorie FROM super_categorie WHERE nom = \"$sp_cat\"";
            $requete_ss = $pdo->query($sql_id_sp_cat);
            $id_sp_cat = $requete_ss->fetch();

            // Link la catégorie à sa super-catégorie dans la table possede_spc.
            $sql = "INSERT INTO possede_spc(id_categorie, id_super_categorie) VALUES (:id_categorie, :id_super_categorie)";
            $query = $pdo->prepare($sql);
            $query->bindValue(":id_categorie", $id_cat['id_categorie'], PDO::PARAM_INT);
            $query->bindValue(":id_super_categorie", $id_sp_cat['id_super_categorie'], PDO::PARAM_INT);
            $query->execute();
         }
      }
   }
   foreach ($Recettes as $recette) {
      try {
      $sqlInsertRecettes = "INSERT INTO recette(titre, ingredients, preparation) VALUE (:titre, :ingredients, :preparation)";
      $query = $pdo->prepare($sqlInsertRecettes);
      $query->bindValue(':titre', $recette['titre'], PDO::PARAM_STR);
      $query->bindValue(':ingredients', $recette['ingredients'], PDO::PARAM_STR);
      $query->bindValue(':preparation', $recette['preparation'], PDO::PARAM_STR);
      $query->execute();

      foreach ($recette['index'] as $ingredient) {
         $sqlQuery = "SELECT id_categorie FROM categorie WHERE nom = \"$ingredient\";";
         $statement = $pdo->prepare($sqlQuery);
         $statement->setFetchMode(PDO::FETCH_ASSOC);
         $statement->execute();
         $id_ingredient = $statement->fetch();
         // echo "<pre>\n"; // Debug
         // echo $recette['titre'];
         // echo " = ";
         // print_r($id_ingredient);
         // echo "</pre>\n";
         $sqlQuery = "SELECT id_recette FROM recette WHERE titre = :titre;";
         $statement = $pdo->prepare($sqlQuery);
         $statement->setFetchMode(PDO::FETCH_ASSOC);
         $statement->bindValue(':titre', $recette['titre']);
         $statement->execute();
         $id_recette = $statement->fetch();
         // echo "<pre>\n"; // Debug
         // echo $recette['titre'];
         // echo " = ";
         // print_r($id_recette);
         // echo "</pre>\n";
         $sqlInsert = "INSERT IGNORE INTO contient_ingredient(id_recette, id_categorie) VALUES (:id_recette, :id_ingredient);";
         $statement = $pdo->prepare($sqlInsert);
         $statement->bindValue(':id_recette', $id_recette['id_recette']);
         $statement->bindValue(':id_ingredient', $id_ingredient['id_categorie']);
         $statement->execute();
      }
      }  catch (Exception $e) {
         die('Erreur insertion des données dans la table recette: ' . $e->getMessage());
      }
   }

   try {
      $sqlQuery = "SELECT nom FROM super_categorie;";
      $statement = $pdo->prepare($sqlQuery);
      $statement->setFetchMode(PDO::FETCH_ASSOC);
      $statement->execute();
      $super_cat = $statement->fetchAll();
   } catch (Exception $e) {
      die('Erreur selection des noms depuis les super-categories: ' . $e->getMessage());
   }

   try {
      $sqlQuery = "SELECT nom FROM categorie;";
      $statement = $pdo->prepare($sqlQuery);
      $statement->setFetchMode(PDO::FETCH_ASSOC);
      $statement->execute();
      $categorie = $statement->fetchAll();
   } catch (Exception $e) {
      die('Erreur selection des noms depuis les super-categories: ' . $e->getMessage());
   }

   try {
      $sqlQuery = "SELECT nom FROM sous_categorie;";
      $statement = $pdo->prepare($sqlQuery);
      $statement->setFetchMode(PDO::FETCH_ASSOC);
      $statement->execute();
      $sous_cat = $statement->fetchAll();
   } catch (Exception $e) {
      die('Erreur selection des noms depuis les super-categories: ' . $e->getMessage());
   }

   try {
      $sqlQuery = "SELECT * FROM recette;";
      $statement = $pdo->prepare($sqlQuery);
      $statement->setFetchMode(PDO::FETCH_ASSOC);
      $statement->execute();
      $recettes = $statement->fetchAll();
   } catch (Exception $e) {
      die('Erreur selection des noms depuis les super-categories: ' . $e->getMessage());
   }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <title>Création bdd</title>
   <link rel="stylesheet" href="styles/bdd_init_style.css">
   <link rel="stylesheet" href="styles/header_style.css">
   <link rel="stylesheet" href="styles/footer_style.css">
</head>
<body>
   <?php include_once("header.php");?>
   <h1>Base de donnée créée</h1>
   <table class="data">
      <tr>
         <th>Super catégorie</th>
      </tr>
      <?php
         foreach ($super_cat as $value) {
            echo "<tr>\n<td>" . $value['nom'] . "</td>\n</tr>";
         }
      ?>
   </table>

   <table class="data">
      <tr>
         <th>Catégorie</th>
      </tr>
      <?php
         foreach ($categorie as $value) {
            echo "<tr>\n<td>" . $value['nom'] . "</td>\n</tr>";
         }
      ?>
   </table>

   <table class="data">
      <tr>
         <th>Sous catégorie</th>
      </tr>
      <?php
         foreach ($sous_cat as $value) {
            echo "<tr>\n<td>" . $value['nom'] . "</td>\n</tr>";
         }
      ?>
   </table>

   <table class="data">
      <tr>
         <th colspan="3">Recettes</th>
      </tr>
      <tr>
         <th>Titre</th>
         <th>Ingrédients</th>
         <th>Préparation</th>
      </tr>
      <?php
         foreach ($recettes as $recette) {
            echo "<tr>\n";
            echo "<td>" . $recette['titre'] . "</td>\n";
            echo "<td>" . $recette['ingredients'] . "</td>\n";
            echo "<td>" . $recette['preparation'] . "</td>\n";
            echo"</tr>";
         }
      ?>
   </table>
   <?php include_once("footer.php"); ?>
</body>
</html>