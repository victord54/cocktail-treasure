function overrideDataBase() {
    if (confirm("Voulez-vous vraiment recréer la base de donnée ?")) {
        document.location.href = "init_bdd.php";
    }
}