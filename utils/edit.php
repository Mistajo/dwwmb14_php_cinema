<?php
session_start();

    // Si l'identifiant du film à modifier n'existe pas dans $_GET 
    if ( !isset($_GET['film_id']) || empty($_GET['film_id']) ) 
    {
        // On redirige l'utilisateur vers la page d'accueil
        // On arrête l'exécution du script
        return header("Location: index.php");
    }

        
    // Dans le cas contraire,
        
    // Récupérer l'identifiant du film tout en protégeant le système contre les failles de type XSS
    $film_id = (int) htmlspecialchars(trim($_GET['film_id']));


    // Etablir une connexion avec la base de données
    require __DIR__ . "/db/connexion.php";

    // Effectuer une requête pour vérifier que l'identifiant appartient à celui d'un film de la table "film"
    $req = $db->prepare("SELECT * FROM film WHERE id=:id LIMIT 1");
    $req->bindValue(":id", $film_id);
    $req->execute();
    
    // Compter le nombre d'enregistrement récupéré de la table film
    $row = $req->rowCount();

    // Si ce n'est pas égal à 1
    if ($row != 1) 
    {
        // On redirige l'utilisateur vers la page d'accueil
        // On arrête l'exécution du script
        return header("Location: index.php");
    }

    // Dans le cas contraire,
    // Récupérons les informations du film à modifier
    $film = $req->fetch();


    // Si les données arrivent au serveur via la méthode "POST",
    if ($_SERVER['REQUEST_METHOD'] === "POST") 
    {
        
        // Un peu de cyber sécurité
        // Protection contre les failles de type XSS (Cross-site scripting) pour éviter les injections de scripts malveillants
        $post_clean = [];
        $errors     = [];

        foreach ($_POST as $key => $value) 
        {
            $post_clean[$key] = htmlspecialchars(trim(addslashes($value)));
        }

        
        // Protection contre les failles de type CSRF (Cross-Site Request Forgery)

        /*
         * Si la clé csrf_token n'existe pas dans les sessions ou dans le formulaire envoyé
         * Ou que la valeur qui leur est associée est vide
         * Ou que leur valeur n'est pas identique,
        */
        if ( 
            !isset($_SESSION['csrf_token']) || !isset($post_clean['csrf_token']) ||
            empty($_SESSION['csrf_token'])  || empty($post_clean['csrf_token']) ||
            ($_SESSION['csrf_token'] !== $post_clean['csrf_token'])
        ) 
        {
            // Effectuons une redirection vers la page de laquelle proviennent les informations
            // Puis, arrêtons l'exécution du script.
            unset($_SESSION['csrf_token']);
            return header("Location: $_SERVER[HTTP_REFERER]");
        }
        unset($_SESSION['csrf_token']);
        
        
        // Protection contre les spams
        if ( !isset($post_clean['honey_pot']) || ($post_clean['honey_pot'] !== "") ) 
        {
            
            // Effectuons une redirection vers la page de laquelle proviennent les informations
            // Puis, arrêtons l'exécution du script.
            return header("Location: $_SERVER[HTTP_REFERER]");
        }

        // Validation des données

        if ( isset($post_clean['name']) ) 
        {
            if ( empty($post_clean['name']) ) 
            {
                $errors['name'] = "Le nom du film est obligatoire.";
            }
            elseif ( mb_strlen($post_clean['name']) > 255 )
            {
                $errors['name'] = "Le nom du film ne doit pas dépasser 255 caractères.";
            }
        }


        if ( isset($post_clean['actors']) ) 
        {
            if ( empty($post_clean['actors']) ) 
            {
                $errors['actors'] = "Le nom du ou des acteurs est obligatoire.";
            }
            elseif ( mb_strlen($post_clean['actors']) > 255 )
            {
                $errors['actors'] = "Le nom du ou des acteurs ne doit pas dépasser 255 caractères.";
            }
        }

        
        if ( isset($post_clean['review']) ) 
        {
            if ( $post_clean['review'] !== "" )
            {
                if ( ! is_numeric($post_clean['review']) ) 
                {
                    $errors['review'] = "Veuillez entrer un nombre.";
                }
                elseif ( ($post_clean['review'] < '0') || ($post_clean['review'] > '5') )
                {
                    $errors['review'] = "La note doit être comprise entre 0 et 5.";
                }
            }
        }
        

        if ( isset($post_clean['comment']) ) 
        {
            if ( $post_clean['comment'] !== "" )
            {
                if ( mb_strlen($post_clean['comment']) > 1000 ) 
                {
                    $errors['comment'] = "Le commentaire ne doit pas dépasser 1000 caractères.";
                }
            }
        }


        // S'il y a des erreurs,
        if ( count($errors) > 0 ) 
        {

            // Sauvegarder les anciennes données envoyées en session,
            $_SESSION['old'] = $post_clean;
            
            // Sauvegarder les messages d'erreurs en session,
            $_SESSION['form_errors'] = $errors;

            // Redirection vers la page de laquelle proviennent les informations,
            // Arrêt de l'exécution du script.
            return header("Location: $_SERVER[HTTP_REFERER]");
        }
        
        // Dans le cas contraire,

        // Si une note a été envoyée,
        if ( isset($post_clean['review']) && !empty($post_clean['review']) ) 
        {
            // L'arrondir à une chiffre apreès la virgule
            $review_rounded = round($post_clean['review'], 1);
        }

        // Etablir une connexion avec la base de données
        require __DIR__ . "/db/connexion.php";
        
        // Effectuer une requête de modification du film dans la table "film"
        $req = $db->prepare("UPDATE film SET name=:name, actors=:actors, review=:review, comment=:comment, updated_at=now() WHERE id=:id");

        $req->bindValue(":name",    $post_clean['name']);
        $req->bindValue(":actors",  $post_clean['actors']);
        $req->bindValue(":review",  isset($review_rounded) ? $review_rounded : '' );
        $req->bindValue(":comment", $post_clean['comment']);
        $req->bindValue(":id",      $film['id']);

        $req->execute();

        // Non obligatoire
        $req->closeCursor();

        // Générons un message flash de succès
        $_SESSION['success'] = "<em>" . stripslashes($post_clean['name']) . "</em> a été modifié avec succès.";
        
        // Effectuer une redirection vers la page d'accueil
        // Arrêter l'exécution du script
        return header("Location: index.php");
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(30));
?>

<!-- Chargement de l'entête ainsi que la balise ouvrante du body -->
<?php require __DIR__ . "/components/head.php"; ?>

    <!-- Chargement de la barre de navigation -->
    <?php require __DIR__ . "/components/nav.php"; ?>
    
    <!-- Chargement du contenu spécific à la page -->
    <main class="container">
        <h1 class="text-center my-3 display-5">Modifier ce film</h1>

        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto shadow bg-white p-4">

                    <?php if( isset($_SESSION['form_errors']) && !empty(isset($_SESSION['form_errors'])) ) : ?>
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                <?php foreach( $_SESSION['form_errors'] as $error) : ?>
                                    <li><?= $error; ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                    <?php endif ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="name">Le nom du film <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" autofocus maxlength="255" value="<?= (isset($_SESSION['old']['name']) && !empty($_SESSION['old']['name'])) ? stripslashes($_SESSION['old']['name']) : htmlspecialchars(stripslashes($film['name'])); unset($_SESSION['old']['name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="actors">Le nom du/des acteur(s) <span class="text-danger">*</span></label>
                            <input type="text" name="actors" id="actors" class="form-control" maxlength="255" value="<?= (isset($_SESSION['old']['actors']) && !empty($_SESSION['old']['actors'])) ? stripslashes($_SESSION['old']['actors']) : htmlspecialchars(stripslashes($film['actors'])); unset($_SESSION['old']['actors']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="review">La note / 5</label>
                            <input type="number" name="review" id="review" step=".1" min="0" max="5" class="form-control" value="<?= (isset($_SESSION['old']['review']) && !empty($_SESSION['old']['review'])) ? stripslashes($_SESSION['old']['review']) : htmlspecialchars(stripslashes($film['review'])); unset($_SESSION['old']['review']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="comment">Un commentaire ?</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4"><?= (isset($_SESSION['old']['comment']) && !empty($_SESSION['old']['comment'])) ? stripslashes($_SESSION['old']['comment']) : htmlspecialchars(stripslashes($film['comment'])); unset($_SESSION['old']['comment']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="honey_pot" value="">
                        </div>
                        <div class="mb-3">
                            <input formnovalidate type="submit" class="btn btn-primary shadow">
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

    