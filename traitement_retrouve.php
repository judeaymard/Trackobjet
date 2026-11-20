
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

// Vérification des numéros de téléphone
function verifierTelephone($telephone, $champ, $obligatoire = false) {

    if ($telephone === '') {
        if ($obligatoire) {
            return "Le numéro " . $champ . " est obligatoire.";
        }

        return null;
    }

    // Supprimer les espaces ajoutés par JavaScript
    $telephone = preg_replace('/\s+/', '', $telephone);

    // Vérifier les 8 chiffres restants après le préfixe
    if (!preg_match('/^[0-9]{8}$/', $telephone)) {
        return "Le numéro " . $champ . " doit contenir exactement 8 chiffres après le préfixe +229 01.";
    }

    return null;
}

function traiterDeclarationRetrouvee() {

    $erreurs = [];

    $nom = trim($_POST['name'] ?? '');
    $typeSelectionne = trim($_POST['type'] ?? '');
    $autreType = trim($_POST['autre_type'] ?? '');
    $whatsapp = trim($_POST['phone_whatsapp'] ?? '');
    $telephone = trim($_POST['tel_phone'] ?? '');
    $adresse = trim($_POST['address'] ?? '');

    // Supprimer les espaces des numéros
    $whatsapp = preg_replace('/\s+/', '', $whatsapp);
    $telephone = preg_replace('/\s+/', '', $telephone);

    // Vérification du nom
    $erreur = verifierNom($nom);

    if ($erreur !== null) {
        $erreurs['nom'][] = $erreur;
    }

    // Vérification du type
    $erreur = verifierType($typeSelectionne);

    if ($erreur !== null) {
        $erreurs['type'][] = $erreur;

    } elseif ($typeSelectionne === 'autres') {

        if ($autreType === '') {
            $erreurs['type'][] = "Veuillez préciser le type de l'objet.";

        } else {
            $erreur = verifierTexte($autreType, "type d'objet");

            if ($erreur !== null) {
                $erreurs['type'][] = $erreur;
            }
        }
    }

    // Type définitif à enregistrer
    $type = ($typeSelectionne === 'autres')
        ? 'Autres : ' . $autreType
        : $typeSelectionne;

    // Vérification de l'adresse
    if ($adresse === '') {
        $erreurs['adresse'][] = "L'adresse complète est obligatoire.";

    } else {
        $erreur = verifierTexte($adresse, "adresse");

        if ($erreur !== null) {
            $erreurs['adresse'][] = $erreur;
        }
    }

    // Vérification WhatsApp (obligatoire)
    $erreur = verifierTelephone($whatsapp, "WhatsApp", true);

    if ($erreur !== null) {
        $erreurs['whatsapp'][] = $erreur;
    }

    // Vérification téléphone (facultatif)
    $erreur = verifierTelephone($telephone, "de téléphone", false);

    if ($erreur !== null) {
        $erreurs['telephone'][] = $erreur;
    }

    // Vérification des photos
    $photosValides = [];
    $dossierUploads = __DIR__ . "/uploads/";

    if (!isset($_FILES['photos'])) {

        $erreurs['photos'][] = "Veuillez ajouter au moins deux photos de l'objet.";

    } else {

        if (!is_dir($dossierUploads)) {
            mkdir($dossierUploads, 0755, true);
        }

        $typesAutorises = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $nombrePhotos = 0;

        foreach ($_FILES['photos']['name'] as $index => $nomOriginal) {

            if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($_FILES['photos']['error'][$index] !== UPLOAD_ERR_OK) {
                $erreurs['photos'][] = "Une erreur est survenue lors de l'envoi d'une photo.";
                continue;
            }

            if ($_FILES['photos']['size'][$index] > 5 * 1024 * 1024) {
                $erreurs['photos'][] = "Chaque photo ne doit pas dépasser 5 Mo.";
                continue;
            }

            $mime = finfo_file(
                $finfo,
                $_FILES['photos']['tmp_name'][$index]
            );

            if (!isset($typesAutorises[$mime])) {
                $erreurs['photos'][] = "Format de photo non autorisé. Utilisez JPG, PNG, WEBP ou GIF.";
                continue;
            }

            $extension = $typesAutorises[$mime];
            $nouveauNom = uniqid('photo_', true) . '.' . $extension;
            $destination = $dossierUploads . $nouveauNom;

            if (move_uploaded_file(
                $_FILES['photos']['tmp_name'][$index],
                $destination
            )) {

                $photosValides[] = [
                    'nom' => $nomOriginal,
                    'chemin' => 'uploads/' . $nouveauNom
                ];

                $nombrePhotos++;

            } else {
                $erreurs['photos'][] = "Impossible d'enregistrer une photo.";
            }
        }

        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if ($nombrePhotos < 2) {
            $erreurs['photos'][] = "Vous devez ajouter au moins deux photos valides.";
        }
    }

    // Enregistrer les erreurs et les anciennes valeurs
    if (!empty($erreurs)) {

        $_SESSION['erreurs'] = [];

        foreach ($erreurs as $champ => $messages) {
            $_SESSION['erreurs'][$champ] = implode(' ', $messages);
        }

        $_SESSION['ancien_nom'] = $nom;
        $_SESSION['ancien_type'] = $typeSelectionne;
        $_SESSION['ancien_autre_type'] = $autreType;
        $_SESSION['ancien_whatsapp'] = $whatsapp;
        $_SESSION['ancien_telephone'] = $telephone;
        $_SESSION['ancienne_adresse'] = $adresse;

        $_SESSION['formulaire_actif'] = 'retrouve';

        header("Location: index.php");
        exit();
    }

    // Ajouter le préfixe +229 01 aux numéros
    $whatsappComplet = '+229 01 ' . $whatsapp;

    $telephoneComplet = ($telephone !== '')
        ? '+229 01 ' . $telephone
        : '';

    // Préparer les chemins des photos
    $listePhotos = [];

    foreach ($photosValides as $photo) {
        $listePhotos[] = $photo['chemin'];
    }

    // Enregistrer la déclaration
    $numero = enregistrerDeclaration([
        'formulaire' => 'RETROUVE',
        'nom' => $nom,
        'type' => $type,
        'description' => '-',
        'whatsapp' => $whatsappComplet,
        'telephone' => $telephoneComplet,
        'adresse' => $adresse,
        'photos' => implode('; ', $listePhotos)
    ]);

    if ($numero !== false) {

        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Votre déclaration d’objet retrouvé a été ajoutée avec succès.'
        ];

    } else {

        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Une erreur est survenue lors de l’enregistrement. Veuillez réessayer.'
        ];

    }

    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    traiterDeclarationRetrouvee();

} else {
    echo "Aucune donnée envoyée.";
}
