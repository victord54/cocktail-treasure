<?php
    //Débugg
    /*ini_set('display_errors', TRUE);
    error_reporting(E_ALL);*/

    //Connexion bdd
    try {
      $pdo = new PDO(
         'mysql:host=localhost;charset=utf8', 'victor', 'firebird');
   } catch (Exception $e) {
      die('Erreur : ' . $e->getMessage());
   }

   //Création de la bdd et de ses tables
   $dbname = 'cocktail_treasure';
   $sql = "DROP DATABASE IF EXISTS $dbname;
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

   // Si tout va bien, on peut continuer
?>