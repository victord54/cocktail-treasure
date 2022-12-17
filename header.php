<header>
    <h1 class="cocktail_treasure">
        <span>Cocktail</span>
        <span>Cocktail</span>
        <span>Treasure</span>
    </h1>
    <nav class="menu">
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php">Accueil</a></li>
            <li class="nav-item"><a href="recherche.php">Chercher une recette</a></li>
            <?php if(!isset($_SESSION["user_login"])){ ?>
                <li class="nav-item"><a href="connexion.php">Connexion</a></li>
                <li class="nav-item"><a href="inscription.php">Inscription</a></li>
                <li class="nav-item"><a href="#">Mes favoris</a></li>
            <?php } else {?>     
                <li class="nav-item" id="profil"><a href="index.php">Profil</a></li>
                <li class="nav-item" id="deconnexion"><a href="logout.php">Déconnexion</a></li>
            <?php }?>
            <li class="nav-item" id="creation_bdd"><a onclick="overrideDataBase()" href="#">Creation bdd</a></li>
        </ul>
    </nav>
    <script src="scripts/header_script.js"></script>
</header>