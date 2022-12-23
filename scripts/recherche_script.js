function formState() {
    if (document.getElementById('ingredients').value.toString().length != 0)
        document.getElementById('recette').disabled = true;
    else {
        if (document.getElementById('no_ingredients').value.toString().length != 0)
            document.getElementById('recette').disabled = true;
        else
            document.getElementById('recette').disabled = false;
    }
    
    if (document.getElementById('recette').value.toString().length != 0) {
        document.getElementById('ingredients').disabled = true;
        document.getElementById('no_ingredients').disabled = true;
    } else {
        document.getElementById('ingredients').disabled = false;
        document.getElementById('no_ingredients').disabled = false;
    }
}