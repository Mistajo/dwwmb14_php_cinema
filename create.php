<?php

    // var_dump($_SERVER);
    // die();


    // Si les données arrivent au serveur via la méthode "POST",
        if ($_SERVER['REQUEST_METHOD'] === "POST") 
        {
            
            // Un peu de cyber sécurité
            // Protection contre les failles de type XSS (Cross-site scripting) pour éviter les injections de scripts malveillants
            $post_clean = [];

            foreach ($_POST as $key => $value) 
            {
                $post_clean[$key] = htmlspecialchars(trim(addslashes($value)));
            }

            
            // Protection contre les failles de type CSRF (Cross-Site Request Forgery)
           
            
            // Protection contre les spams
            
            
            // Validation des données

            
            
            // S'il y a des erreurs,
            
            // Sauvegarder les anciennes données envoyées en session,
            // Sauvegarder les messages d'erreurs en session,
            // Redirection vers la page de laquelle proviennent les informations,
            // Arrêt de l'exécution du script.
            
            // Dans le cas contraire,
            // Etablir une connexion avec la base de données
            
            // Effectuer une requête d'insertion des données dans la table "film"
            
            // Effectuer une redirection vers la page d'accueil
            
            // Arrêter l'exécution du script
        }

        $_SESSION["csrf_token"] = bin2hex(random_bytes(30))
?>

<!-- Chargement de l'entête ainsi que la balise ouvrante du body -->
<?php require __DIR__ . "/components/head.php"; ?>

    <!-- Chargement de la barre de navigation -->
    <?php require __DIR__ . "/components/nav.php"; ?>
    
    <!-- Chargement du contenu spécific à la page -->
    <main class="container">
        <h1 class="text-center my-3 display-5">Nouveau film</h1>

        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto shadow bg-white p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label for="name">Le nom du film <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="actors">Le nom du/des acteur(s) <span class="text-danger">*</span></label>
                            <input type="text" name="actors" id="actors" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="review">La note / 5</label>
                            <input type="text" name="review" id="review" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="comment">Un commentaire ?</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4"></textarea>
                        </div>

                        <div>
                            <div class="mb-3">
                                <input type="hidden" name="csrf_token" value="<?=$_SESSION["csrf_token"]; ?>">

                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="submit" class="btn btn-primary shadow">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Chargement du pied de page -->
    <?php require __DIR__ . "/components/footer.php"; ?>

<!-- Chargement de la fermeture du document -->
<?php require __DIR__ . "/components/foot.php"; ?>

    