
<?php
session_start();
require_once __DIR__ . '/xlsx_helper.php';

function afficherErreurs($erreurs) {

    echo "<h2>Erreurs détectées</h2>";
    echo "<ul>";

    foreach ($erreurs as $erreur) {
        echo "<li>" . htmlspecialchars($erreur) . "</li>";
    }

    echo "</ul>";
}


function verifierNom($nom) {

    if ($nom === '') {
        return "Le nom de l'objet est obligatoire.";
    }

    if (strlen($nom) > 20) {
        return "Le nom ne doit pas dépasser 20 caractères.";
    }

    if (!preg_match('/^[a-zA-Z ]+$/', $nom)) {
        return "Le nom doit contenir uniquement des lettres et des espaces, sans chiffres ni caractères spéciaux.";
    }

    return null;
}


function verifierType($type) {

    if ($type === '') {
        return "Le type de l'objet est obligatoire.";
    }

    return null;
}


function verifierTexte($texte, $champ) {

    if (!preg_match('/^[a-zA-ZÀ-ÿ0-9 ]+$/u', $texte)) {
        return "Le champ " . $champ . " ne doit contenir que des lettres, des chiffres et des espaces.";
    }

    return null;
}


/* VERIFICATION DU TELEPHONE */

function verifierTelephone($telephone, $champ, $obligatoire = false) {

    if ($telephone === '') {

        if ($obligatoire) {
            return "Le numéro " . $champ . " est obligatoire.";
        }

        return null;
    }

    // Supprimer les espaces
    $telephone = preg_replace('/\s+/', '', $telephone);

    // Vérifier les 8 chiffres
    if (!preg_match('/^[0-9]{8}$/', $telephone)) {
        return "Le numéro " . $champ . " doit contenir exactement 8 chiffres après le préfixe +229 01.";
    }

    return null;
}


// TRAITEMENT DE LA DECLARATION D'OBJET PERDU

function traiterDeclarationPerdue() {

    $erreurs = [];

    $nom = trim($_POST['name'] ?? '');
    $typeSelectionne = trim($_POST['type'] ?? '');
    $autreType = trim($_POST['autre_type'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $whatsapp = trim($_POST['phone_whatsapp'] ?? '');
    $telephone = trim($_POST['tel_phone'] ?? '');

    // Supprimer les espaces des numéros
    $whatsapp = preg_replace('/\s+/', '', $whatsapp);
    $telephone = preg_replace('/\s+/', '', $telephone);


    // Vérification du nom
    $erreur = verifierNom($nom);

    if ($erreur !== null) {
        $erreurs['nom'] = $erreur;
    }


    // Vérification du type
    $erreur = verifierType($typeSelectionne);

    if ($erreur !== null) {
        $erreurs['type'] = $erreur;

    } elseif ($typeSelectionne === 'autres') {

        if ($autreType === '') {
            $erreurs['type'] = "Veuillez préciser le type de l'objet.";

        } else {

            $erreur = verifierTexte($autreType, "type d'objet");

            if ($erreur !== null) {
                $erreurs['type'] = $erreur;
            }
        }
    }


    // Définition du type à enregistrer
    $type = ($typeSelectionne === 'autres')
        ? 'Autres : ' . $autreType
        : $typeSelectionne;


    // Vérification de la description
    if ($description === '') {

        $erreurs['description'] = "La description est obligatoire.";

    } else {

        $erreur = verifierTexte($description, "description");

        if ($erreur !== null) {
            $erreurs['description'] = $erreur;
        }
    }


    // Vérification des numéros
    $erreur = verifierTelephone($whatsapp, "WhatsApp", true);

    if ($erreur !== null) {
        $erreurs['whatsapp'] = $erreur;
    }

    $erreur = verifierTelephone($telephone, "de téléphone");

    if ($erreur !== null) {
        $erreurs['telephone'] = $erreur;
    }


    // Retour au formulaire en cas d'erreur
    if (!empty($erreurs)) {

        $_SESSION['erreurs'] = $erreurs;

        $_SESSION['ancien_nom'] = $nom;
        $_SESSION['ancien_type'] = $typeSelectionne;
        $_SESSION['ancien_autre_type'] = $autreType;
        $_SESSION['ancienne_description'] = $description;
        $_SESSION['ancien_whatsapp'] = $whatsapp;
        $_SESSION['ancien_telephone'] = $telephone;

        $_SESSION['formulaire_actif'] = 'perdu';

        header("Location: index.php");
        exit();
    }


    // Ajouter le préfixe béninois avant l'enregistrement
    $whatsappComplet = ($whatsapp !== '')
        ? '+229 01 ' . $whatsapp
        : '';

    $telephoneComplet = ($telephone !== '')
        ? '+229 01 ' . $telephone
        : '';


    // Enregistrement dans Excel
    $numero = enregistrerDeclaration([
        'formulaire' => 'PERDU',
        'nom' => $nom,
        'type' => $type,
        'description' => $description,
        'whatsapp' => $whatsappComplet,
        'telephone' => $telephoneComplet,
        'adresse' => '-',
        'photos' => '-'
    ]);


    if ($numero !== false) {

        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Votre déclaration d’objet perdu a été ajoutée avec succès.'
        ];

    } else {

        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Une erreur est survenue lors de l’enregistrement. Veuillez réessayer.'
        ];

    }

    header("Location: index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    traiterDeclarationPerdue();

} else {

    echo "Aucune donnée envoyée.";

}

?>
