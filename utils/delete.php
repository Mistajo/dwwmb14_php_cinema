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
    // Récupérons les informations du film à supprimer
    $film = $req->fetch();


    // Effectuer une nouvelle requête pour la suppression
    $delete_req = $db->prepare("DELETE FROM film WHERE id=:id");
    $delete_req->bindValue(":id", $film['id']);
    $delete_req->execute();
    $delete_req->closeCursor();

    // Générons un message flash de succès
    $_SESSION['success'] = "<em>" . stripslashes($film['name']) . "</em> a été supprimé.";

    // Effectuer une redirection vers la page index.php
    // Arrêter l'exécution du script
    return header("Location: index.php");