<?php
    //Débugg
    /*ini_set('display_errors', TRUE);
    error_reporting(E_ALL);*/
   include_once("data/Donnees.inc.php");

   //Connexion bdd
   $login = file_get_contents("data/login");
   $password = file_get_contents("data/password");
   try {
      $pdo = new PDO(
         'mysql:host=localhost;charset=utf8', $login, $password);
   } catch (Exception $e) {
      die('Erreur : ' . $e->getMessage());
   }

   //Création de la bdd et de ses tables
   $dbname = 'cocktail_treasure';
   $sql = "
   DROP DATABASE IF EXISTS $dbname;
   CREATE DATABASE $dbname;
   USE $dbname;
   
   CREATE TABLE Utilisateur(
      id_utilisateur INT AUTO_INCREMENT,
      nom VARCHAR(50),
      prenom VARCHAR(50),
      login VARCHAR(50) NOT NULL,
      mdp VARCHAR(50) NOT NULL,
      email VARCHAR(50),
      sexe VARCHAR(50),
      date_naissance DATE,
      num_tel VARCHAR(50),
      adresse_postale VARCHAR(50),
      PRIMARY KEY(id_utilisateur),
      UNIQUE(login)
   );
 
   CREATE TABLE Recette(
      id_recette INT AUTO_INCREMENT,
      titre VARCHAR(50) NOT NULL,
      ingredients VARCHAR(50) NOT NULL,
      preparation VARCHAR(50) NOT NULL,
      PRIMARY KEY(id_recette),
      UNIQUE(titre)
   );
 
   CREATE TABLE Categorie(
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
      FOREIGN KEY(id_categorie) REFERENCES Categorie(id_categorie),
      FOREIGN KEY(id_sous_categorie) REFERENCES sous_categorie(id_sous_categorie)
   );
   
   CREATE TABLE possede_spc(
      id_categorie INT,
      id_super_categorie INT,
      PRIMARY KEY(id_categorie, id_super_categorie),
      FOREIGN KEY(id_categorie) REFERENCES Categorie(id_categorie),
      FOREIGN KEY(id_super_categorie) REFERENCES super_categorie(id_super_categorie)
   );
   
   CREATE TABLE contient_ingredient(
      id_recette INT,
      id_categorie INT,
      PRIMARY KEY(id_recette, id_categorie),
      FOREIGN KEY(id_recette) REFERENCES Recette(id_recette),
      FOREIGN KEY(id_categorie) REFERENCES Categorie(id_categorie)
   );
   
   CREATE TABLE favoris(
      id_utilisateur INT,
      id_recette INT,
      PRIMARY KEY(id_utilisateur, id_recette),
      FOREIGN KEY(id_utilisateur) REFERENCES Utilisateur(id_utilisateur),
      FOREIGN KEY(id_recette) REFERENCES Recette(id_recette)
   );
   ";

   try {
      $pdo->exec($sql);
   } catch (Exception $e) {
      die('Erreur création cocktail_treasure: ' . $e->getMessage());
   }

   foreach ($Hierarchie as $cat=>$ss_sp_cat) {
      //Insère la catégorie dans la table Categorie
      $sql = "INSERT INTO Categorie(nom) VALUES (:nom)";
      $query = $pdo->prepare($sql);
      $query->bindValue(":nom", $cat, PDO::PARAM_STR);
      $query->execute();

      //Récupère l'id de la catégorie actuelle
      $sql_id_cat = "SELECT id_categorie FROM Categorie WHERE nom = \"$cat\"";
      $requete = $pdo->query($sql_id_cat);
      $id_cat = $requete->fetch();

      if (isset($ss_sp_cat['sous-categorie'])) {
         foreach($ss_sp_cat['sous-categorie'] as $ss_cat) {
            //Récupère l'id de la sous-catégorie actuelle si il existe
            $sql_id_ss_cat = "SELECT id_sous_categorie FROM sous_categorie WHERE nom = \"$ss_cat\"";
            $requete_ss = $pdo->query($sql_id_ss_cat);
            $id_ss_cat = $requete_ss->fetch();

            if (!($id_ss_cat)) {
               //Insère la sous-catégorie dans la table Sous_categorie
               $sql = "INSERT INTO sous_categorie(nom) VALUES (:nom)";
               $query = $pdo->prepare($sql);
               $query->bindValue(":nom", $ss_cat, PDO::PARAM_STR);
               $query->execute();

               //Récupère l'id de la sous-catégorie actuelle
               $sql_id_ss_cat = "SELECT id_sous_categorie FROM sous_categorie WHERE nom = \"$ss_cat\"";
               $requete_ss = $pdo->query($sql_id_ss_cat);
               $id_ss_cat = $requete_ss->fetch();
            }
            
            //Link la catégorie à sa sous-catégorie dans la table possede_ssc
            $sql = "INSERT INTO possede_ssc(id_categorie, id_sous_categorie) VALUES (:id_categorie, :id_sous_categorie)";
            $query = $pdo->prepare($sql);
            $query->bindValue(":id_categorie", $id_cat['id_categorie'], PDO::PARAM_INT);
            $query->bindValue(":id_sous_categorie", $id_ss_cat['id_sous_categorie'], PDO::PARAM_INT);
            $query->execute();

         }
      }
      if (isset($ss_sp_cat['super-categorie'])) {
         foreach($ss_sp_cat['super-categorie'] as $sp_cat) {
            echo 'cat : ' . $cat;
            echo "</br>";
            //Récupère id de la super-catégorie actuelle si elle existe
            $sql_id_sp_cat = "SELECT id_super_categorie FROM super_categorie WHERE nom = \"$sp_cat\"";
            $requete_ss = $pdo->query($sql_id_sp_cat);
            $id_sp_cat = $requete_ss->fetch();
            
            if (!($id_sp_cat)) {
               //Insère la super-catégorie dans la table super_categorie
               $sql = "INSERT INTO super_categorie(nom) VALUES (:nom)";
               $query = $pdo->prepare($sql);
               $query->bindValue(":nom", $sp_cat, PDO::PARAM_STR);
               $query->execute();
               echo 'SP cat : ' . $sp_cat;
               echo "</br>";
               //Récupère l'id de la super-catégorie actuelle
               $sql_id_sp_cat = "SELECT id_super_categorie FROM super_categorie WHERE nom = \"$sp_cat\"";
               $requete_ss = $pdo->query($sql_id_sp_cat);
               $id_sp_cat = $requete_ss->fetch();
            }

            //Link la catégorie à sa super-catégorie dans la table possede_spc
            $sql = "INSERT INTO possede_spc(id_categorie, id_super_categorie) VALUES (:id_categorie, :id_super_categorie)";
            $query = $pdo->prepare($sql);
            $query->bindValue(":id_categorie", $id_cat['id_categorie'], PDO::PARAM_INT);
            $query->bindValue(":id_super_categorie", $id_sp_cat['id_super_categorie'], PDO::PARAM_INT);
            $query->execute();
           
         } 
      }
   }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <title>Création bdd</title>
</head>
<body>
   <p>Base de donnée créée</p>
</body>
</html>