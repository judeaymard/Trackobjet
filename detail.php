
<?php
session_start();

require_once __DIR__ . '/xlsx_helper.php';

// Fichier Excel
$fichier = __DIR__ . '/declarations.xlsx';

// Récupérer les paramètres de l'URL
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

// Vérifier les paramètres
if (!in_array($type, ['perdu', 'retrouve'], true) || $id === '') {
    header('Location: index.php');
    exit;
}

// Choisir la bonne feuille Excel
$nomFeuille = ($type === 'perdu')
    ? 'Objets perdus'
    : 'Objets retrouvés';

// Initialiser l'objet
$objet = null;
$erreur = '';

// Lire le classeur
try {

    if (!file_exists($fichier)) {
        throw new RuntimeException('Le fichier des déclarations est introuvable.');
    }

    $classeur = xlsxLireClasseurComplet($fichier);

    $lignes = $classeur[$nomFeuille] ?? [];

    // Parcourir les déclarations sans la ligne d'en-têtes
    if (!empty($lignes)) {
        array_shift($lignes);
    }

    foreach ($lignes as $ligne) {

        if ((string)($ligne['A'] ?? '') === (string)$id) {
            $objet = $ligne;
            break;
        }
    }

    if ($objet === null) {
        $erreur = 'Cette annonce est introuvable ou n’existe plus.';
    }

} catch (Throwable $e) {

    error_log('Erreur detail.php : ' . $e->getMessage());
    $erreur = 'Une erreur est survenue lors du chargement de cette annonce.';
}

// Fonction pour sécuriser l'affichage
function securiser($valeur) {
    return htmlspecialchars((string)($valeur ?? ''), ENT_QUOTES, 'UTF-8');
}

// Préparer les photos pour les objets retrouvés
$photos = [];

if ($objet !== null && $type === 'retrouve') {

    $cheminsPhotos = explode(';', $objet['I'] ?? '');

    foreach ($cheminsPhotos as $chemin) {

        $chemin = trim($chemin);

        // Accepter uniquement les fichiers du dossier uploads
        if (
            $chemin !== '' &&
            str_starts_with($chemin, 'uploads/') &&
            !str_contains($chemin, '..') &&
            is_file(__DIR__ . '/' . $chemin)
        ) {
            $photos[] = $chemin;
        }
    }
}

include 'header.php';
?>

<main class="detail-page">

<style>
.detail-page {
    background: #f4f8f7;
    min-height: 75vh;
    padding: 50px 7%;
}

.detail-container {
    max-width: 950px;
    margin: auto;
    background: white;
    border-radius: 16px;
    padding: 35px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.07);
}

.detail-retour {
    display: inline-block;
    margin-bottom: 25px;
    color: #086d63;
    text-decoration: none;
    font-weight: 600;
}

.detail-retour:hover {
    text-decoration: underline;
}

.detail-titre {
    color: #086d63;
    font-size: 28px;
    margin-bottom: 10px;
}

.detail-badge {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 20px;
}

.detail-badge.perdu {
    background: #fff0dc;
    color: #c47b16;
}

.detail-badge.retrouve {
    background: #dcfce7;
    color: #15803d;
}

.detail-date {
    color: #888;
    font-size: 14px;
    margin-bottom: 25px;
}

.detail-infos {
    border-top: 1px solid #eee;
    margin-top: 20px;
}

.detail-ligne {
    padding: 17px 0;
    border-bottom: 1px solid #eee;
}

.detail-label {
    display: block;
    color: #64748b;
    font-size: 14px;
    margin-bottom: 6px;
}

.detail-valeur {
    color: #222;
    font-size: 16px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.detail-photos {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-top: 20px;
}

.detail-photos img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #eee;
}

.detail-sans-photo {
    color: #777;
    padding: 15px 0;
}

.detail-erreur {
    max-width: 700px;
    margin: 50px auto;
    padding: 30px;
    background: white;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 4px 18px rgba(0,0,0,0.07);
}

.detail-erreur h2 {
    color: #c47b16;
    margin-bottom: 15px;
}

.detail-bouton {
    display: inline-block;
    margin-top: 25px;
    padding: 12px 22px;
    background: #086d63;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.2s;
}

.detail-bouton:hover {
    background: #07564f;
}

@media (max-width: 600px) {
    .detail-page {
        padding: 25px 15px;
    }

    .detail-container {
        padding: 22px 18px;
    }

    .detail-titre {
        font-size: 23px;
    }

    .detail-photos img {
        height: 190px;
    }
}
</style>

<?php if ($erreur !== ''): ?>

    <div class="detail-erreur">

        <h2>Annonce indisponible</h2>

        <p><?= securiser($erreur) ?></p>

        <a href="index.php" class="detail-bouton">
            Retour à l'accueil
        </a>

    </div>

<?php else: ?>

    <div class="detail-container">

        <a href="index.php" class="detail-retour">
            ← Retour aux annonces
        </a>

        <?php if ($type === 'perdu'): ?>

            <span class="detail-badge perdu">
                OBJET PERDU
            </span>

        <?php else: ?>

            <span class="detail-badge retrouve">
                OBJET RETROUVÉ
            </span>

        <?php endif; ?>

        <h1 class="detail-titre">
            <?= securiser($objet['D'] ?? 'Objet sans nom') ?>
        </h1>

        <p class="detail-date">
            Déclaré le : <?= securiser($objet['B'] ?? '') ?>
        </p>

        <div class="detail-infos">

            <div class="detail-ligne">
                <span class="detail-label">Type d'objet</span>

                <div class="detail-valeur">
                    <?= securiser($objet['E'] ?? '') ?>
                </div>
            </div>

            <?php if ($type === 'perdu'): ?>

                <div class="detail-ligne">
                    <span class="detail-label">Description de l'objet</span>

                    <div class="detail-valeur">
                        <?= nl2br(securiser($objet['F'] ?? '')) ?>
                    </div>
                </div>

            <?php else: ?>

                <div class="detail-ligne">
                    <span class="detail-label">Lieu où l'objet a été retrouvé</span>

                    <div class="detail-valeur">
                        <?= nl2br(securiser($objet['H'] ?? '')) ?>
                    </div>
                </div>

                <div class="detail-ligne">

                    <span class="detail-label">Photos de l'objet</span>

                    <?php if (!empty($photos)): ?>

                        <div class="detail-photos">

                            <?php foreach ($photos as $photo): ?>

                                <img
                                    src="<?= securiser($photo) ?>"
                                    alt="Photo de l'objet retrouvé"
                                    loading="lazy"
                                >

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <p class="detail-sans-photo">
                            Aucune photo disponible.
                        </p>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <div class="detail-ligne">
                <span class="detail-label">Nom du déclarant</span>

                <div class="detail-valeur">
                    <?= securiser($objet['D'] ?? '') ?>
                </div>
            </div>

            <div class="detail-ligne">
                <span class="detail-label">Numéro WhatsApp</span>

                <div class="detail-valeur">
                    <?= securiser($objet['G'] ?? ($objet['F'] ?? '')) ?>
                </div>
            </div>

            <div class="detail-ligne">
                <span class="detail-label">Numéro de téléphone</span>

                <div class="detail-valeur">
                    <?= securiser($objet['H'] ?? ($objet['G'] ?? '')) ?>
                </div>
            </div>

        </div>

    </div>

<?php endif; ?>

</main>

<?php include 'footer.html'; ?>
