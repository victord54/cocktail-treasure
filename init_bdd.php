<?php
    ini_set('display_errors', TRUE);
    error_reporting(E_ALL);
   try {
      $pdo = new PDO(
         'mysql:host=localhost;charset=utf8', 'victor', 'firebird');
   } catch (Exception $e) {
      die('Erreur : ' . $e->getMessage());
   }
   $dbname = 'cocktail_treasure';
   $sql = "DROP DATABASE IF EXISTS $dbname;
   CREATE DATABASE $dbname;
   USE $dbname;";

   try {
      $pdo->exec($sql);
   } catch (Exception $e) {
      die('Erreur création cocktail_treasure: ' . $e->getMessage());
   }

   // Si tout va bien, on peut continuer
?>