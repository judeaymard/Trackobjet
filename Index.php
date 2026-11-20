<?php
require_once __DIR__ . '/xlsx_helper.php';
session_start();

$ouvrirModal = !empty($_SESSION['erreurs']);

$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);
$formulaireActif = $_SESSION['formulaire_actif'] ?? 'perdu';
// Chemin du fichier Excel
$fichier = __DIR__ . '/declarations.xlsx';

// Initialiser les tableaux
$objetsPerdus = [];
$objetsRetrouves = [];


// Vérifier si le fichier existe
if (file_exists($fichier)) {

    $classeur = xlsxLireClasseurComplet($fichier);

    // Récupérer les objets perdus
    $objetsPerdus = $classeur['Objets perdus'] ?? [];

    if (!empty($objetsPerdus)) {
        array_shift($objetsPerdus); // Retirer les en-têtes
        $objetsPerdus = array_reverse($objetsPerdus);
    }

    // Récupérer les objets retrouvés
    $objetsRetrouves = $classeur['Objets retrouvés'] ?? [];

    if (!empty($objetsRetrouves)) {
        array_shift($objetsRetrouves); // Retirer les en-têtes
        $objetsRetrouves = array_reverse($objetsRetrouves);
    }
}

// Récupérer le terme recherché
// Nombre d'annonces affichées par catégorie
$limitePerdus = max(3, (int)($_GET['voir_perdus'] ?? 3));
$limiteRetrouves = max(3, (int)($_GET['voir_retrouves'] ?? 3));

// Ne pas dépasser le nombre réel d'annonces
$limitePerdus = min($limitePerdus, count($objetsPerdus));
$limiteRetrouves = min($limiteRetrouves, count($objetsRetrouves));
$recherche = trim($_GET['q'] ?? '');

// Normaliser le texte pour ignorer les accents et la casse
function normaliserTexte($texte) {
    $texte = (string) $texte;

    $texteSansAccents = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);

    if ($texteSansAccents !== false) {
        $texte = $texteSansAccents;
    }

    return strtolower($texte);
}

// Préparer les résultats
$resultatsPerdus = [];
$resultatsRetrouves = [];

if ($recherche !== '') {

    $terme = normaliserTexte($recherche);

    // Rechercher dans les objets perdus
    foreach ($objetsPerdus as $objet) {

        $contenu = normaliserTexte(
            ($objet['D'] ?? '') . ' ' .
            ($objet['E'] ?? '') . ' ' .
            ($objet['F'] ?? '')
        );

        if (strpos($contenu, $terme) !== false) {
            $resultatsPerdus[] = $objet;
        }
    }

    // Rechercher dans les objets retrouvés
    foreach ($objetsRetrouves as $objet) {

        $contenu = normaliserTexte(
            ($objet['D'] ?? '') . ' ' .
            ($objet['E'] ?? '') . ' ' .
            ($objet['H'] ?? '')
        );

        if (strpos($contenu, $terme) !== false) {
            $resultatsRetrouves[] = $objet;
        }
    }
}
include 'header.php';
?>

<main>
<style>


/* =========================================
   SECTIONS DES ANNONCES
========================================= */

.annonces-section {
    padding: 65px 7%;
    background: #f5f8f7;
}

.annonces-section h2 {
    color: #075e55;
    font-size: 29px;
    font-weight: 750;
    margin-bottom: 9px;
}

.annonces-intro {
    color: #718096;
    font-size: 16px;
    margin-bottom: 32px;
}

/* =========================================
   GRILLE
========================================= */

.annonces-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 25px;
    align-items: stretch;
    width: 100%;
}

/* =========================================
   CARTE
========================================= */

.annonce-card {
    display: flex;
    flex-direction: column;
    background: white;
    border: 1px solid #e8eeeb;
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: #1e293b;
    box-shadow: 0 5px 18px rgba(0, 50, 40, 0.045);
    transition: all 0.25s ease;
}

.annonce-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 28px rgba(0, 50, 40, 0.10);
    border-color: #c5e3dc;
}

/* =========================================
   IMAGE
========================================= */

.annonce-card img {
    width: 100%;
    height: 205px;
    object-fit: cover;
    display: block;
    background: #edf5f2;
    transition: transform 0.35s ease;
}

.annonce-card:hover img {
    transform: scale(1.035);
}

/* Visuel de remplacement pour les objets perdus */
.annonce-card:not(:has(img))::before {
    content: "⌕";
    height: 170px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #08796d;
    font-size: 75px;
    background: linear-gradient(135deg, #dff2ed, #f3faf7);
}

/* =========================================
   CONTENU
========================================= */

.annonce-contenu {
    padding: 21px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.annonce-contenu h3 {
    color: #172b29;
    font-size: 19px;
    font-weight: 750;
    margin: 15px 0 7px;
}

.annonce-type {
    color: #84918f;
    font-size: 14px;
    margin-bottom: 15px;
}

.annonce-contenu p:not(.annonce-type):not(.annonce-date) {
    color: #52615e;
    font-size: 14px;
    line-height: 1.6;
}

/* =========================================
   BADGES
========================================= */

.badge-perdu,
.badge-retrouve {
    display: inline-block;
    width: fit-content;
    padding: 7px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
}

.badge-perdu {
    background: #fff1dc;
    color: #b76b0b;
}

.badge-retrouve {
    background: #e3f6eb;
    color: #16804a;
}

/* =========================================
   DATE
========================================= */

.annonce-date {
    color: #94a3a0;
    font-size: 12px;
    margin-top: auto;
    padding-top: 17px;
    border-top: 1px solid #edf1ef;
}

/* =========================================
   BOUTON VOIR PLUS
========================================= */

.annonces-actions {
    display: flex;
    justify-content: center;
    margin-top: 35px;
}

.btn-voir-plus {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 9px;
    padding: 13px 28px;
    background: white;
    color: #087267;
    border: 1px solid #b9dcd4;
    border-radius: 9px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.btn-voir-plus:hover {
    background: #087267;
    color: white;
    border-color: #087267;
}

/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 900px) {
    .annonces-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }
}

@media (max-width: 580px) {
    .annonces-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
}


.hero-trackobjet {
    min-height: 390px;
    padding: 75px 8%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(120deg, #064e46, #08796d);
    color: white;
    text-align: center;
}

.hero-content {
    width: 100%;
    max-width: 850px;
    margin: auto;
}

.hero-content h1 {
    font-size: clamp(34px, 5vw, 55px);
    line-height: 1.2;
    margin: 0 0 22px;
    font-weight: 800;
    letter-spacing: -1.5px;
}

.hero-content h1 span {
    color: #9be5cc;
}

.hero-content p {
    max-width: 650px;
    margin: 0 auto 30px;
    color: #d5eee8;
    line-height: 1.8;
    font-size: 16px;
}

.hero-actions {
    display: flex;
    justify-content: center;
    margin-top: 25px;
}

.hero-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 15px 27px;
    border-radius: 9px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.25s ease;
}

.hero-btn-primary {
    background: white;
    color: #08675c;
}

.hero-btn-primary:hover {
    background: #9be5cc;
    transform: translateY(-3px);
}

@media (max-width: 600px) {
    .hero-trackobjet {
        min-height: 350px;
        padding: 55px 20px;
    }

    .hero-content h1 {
        font-size: 35px;
    }

    .hero-content p {
        font-size: 14px;
    }
}

/* =====================================
   LOADER DE DECLARATION
===================================== */

.loader-declaration {
    position: fixed;
    inset: 0;
    z-index: 100000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(10, 30, 27, 0.65);
    backdrop-filter: blur(5px);
}

.loader-declaration.actif {
    display: flex;
}

.loader-boite {
    width: 100%;
    max-width: 380px;
    padding: 35px 28px;
    background: white;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 15px 50px rgba(0,0,0,0.18);
    animation: apparitionLoader 0.25s ease;
}

.loader-cercle {
    width: 52px;
    height: 52px;
    margin: 0 auto 22px;
    border: 5px solid #e0f0ec;
    border-top-color: #08796d;
    border-radius: 50%;
    animation: rotationLoader 0.8s linear infinite;
}

.loader-boite h3 {
    color: #075e55;
    font-size: 20px;
    margin-bottom: 10px;
}

.loader-boite p {
    color: #718096;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

@keyframes rotationLoader {
    to {
        transform: rotate(360deg);
    }
}

@keyframes apparitionLoader {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* =====================================
   NOTIFICATION
===================================== */

.notification-declaration {
    position: fixed;
    top: 25px;
    right: 25px;
    z-index: 100001;
    display: flex;
    align-items: center;
    gap: 13px;
    width: calc(100% - 40px);
    max-width: 420px;
    padding: 17px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.13);
    animation: apparitionNotification 0.4s ease;
}

.notification-declaration.success {
    border-left: 5px solid #16a34a;
}

.notification-declaration.error {
    border-left: 5px solid #dc2626;
}

.notification-icone {
    width: 35px;
    height: 35px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 19px;
    font-weight: bold;
}

.notification-declaration.success .notification-icone {
    color: #15803d;
    background: #dcfce7;
}

.notification-declaration.error .notification-icone {
    color: #b91c1c;
    background: #fee2e2;
}

.notification-texte {
    flex: 1;
}

.notification-texte strong {
    display: block;
    color: #1e293b;
    font-size: 15px;
    margin-bottom: 5px;
}

.notification-texte p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
    margin: 0;
}

.fermer-notification {
    border: none;
    background: transparent;
    color: #94a3b8;
    font-size: 24px;
    cursor: pointer;
}

@keyframes apparitionNotification {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@media (max-width: 480px) {
    .notification-declaration {
        top: 12px;
        right: 12px;
        width: calc(100% - 24px);
    }
}

</style>


<section class="hero-trackobjet">

    <div class="hero-content">

        <h1>
            Un objet perdu ?<br>
            <span>Retrouvons-le ensemble.</span>
        </h1>

        <p>
            Vous avez perdu un objet ou retrouvé quelque chose ?
            TrackObjet vous aide à publier et consulter des annonces
            pour faciliter les retrouvailles.
        </p>

        <div class="hero-actions">
            <a href="#annonces-perdues" class="hero-btn hero-btn-primary">
                Consulter les annonces
                <span>→</span>
            </a>
        </div>

    </div>

</section>


<?php if ($recherche !== ''): ?>

<!-- AFFICHAGE DES RÉSULTATS DE RECHERCHE -->

<section class="annonces-section">

    <h2>Résultats de recherche</h2>

    <p class="annonces-intro">
        Résultats pour :
        <strong><?= htmlspecialchars($recherche) ?></strong>
        (<?= count($resultatsPerdus) + count($resultatsRetrouves) ?> résultat(s))
    </p>

    <div class="annonces-grid">

        <?php foreach ($resultatsPerdus as $objet): ?>

            <a class="annonce-card"
               href="detail.php?type=perdu&id=<?= urlencode($objet['A']) ?>">

                <div class="annonce-contenu">
                    <span class="badge-perdu">OBJET PERDU</span>
                    <h3><?= htmlspecialchars($objet['D'] ?? '') ?></h3>

                    <p class="annonce-type">
                        <?= htmlspecialchars($objet['E'] ?? '') ?>
                    </p>

                    <p>
                        <?= htmlspecialchars(
                            mb_strimwidth($objet['F'] ?? '', 0, 100, '...')
                        ) ?>
                    </p>

                    <p class="annonce-date">
                        Déclaré le : <?= htmlspecialchars($objet['B'] ?? '') ?>
                    </p>
                </div>

            </a>

        <?php endforeach; ?>


        <?php foreach ($resultatsRetrouves as $objet): ?>

            <?php
            $photos = explode(';', $objet['I'] ?? '');
            $premierePhoto = trim($photos[0]);
            ?>

            <a class="annonce-card"
               href="detail.php?type=retrouve&id=<?= urlencode($objet['A']) ?>">

                <?php if (!empty($premierePhoto)): ?>
                    <img src="<?= htmlspecialchars($premierePhoto) ?>"
                         alt="Photo de l'objet retrouvé">
                <?php endif; ?>

                <div class="annonce-contenu">
                    <span class="badge-retrouve">OBJET RETROUVÉ</span>
                    <h3><?= htmlspecialchars($objet['D'] ?? '') ?></h3>

                    <p class="annonce-type">
                        <?= htmlspecialchars($objet['E'] ?? '') ?>
                    </p>

                    <p>
                        <strong>Lieu :</strong>
                        <?= htmlspecialchars($objet['H'] ?? '') ?>
                    </p>

                    <p class="annonce-date">
                        Déclaré le : <?= htmlspecialchars($objet['B'] ?? '') ?>
                    </p>
                </div>

            </a>

        <?php endforeach; ?>


        <?php if (empty($resultatsPerdus) && empty($resultatsRetrouves)): ?>
            <p class="annonce-vide">
                Aucun objet ne correspond à votre recherche.
                Essayez un autre mot-clé.
            </p>
        <?php endif; ?>

    </div>

</section>

<?php else: ?>

<!-- AFFICHAGE NORMAL : LES 3 DERNIERS OBJETS PERDUS -->

<section class="annonces-section">

    <h2>Objets récemment perdus</h2>
    <p class="annonces-intro">
        Découvrez les objets recherchés par leurs propriétaires.
    </p>

    <div class="annonces-grid">

        <?php if (!empty($objetsPerdus)): ?>

            <?php foreach (array_slice($objetsPerdus, 0, $limitePerdus) as $objet): ?>

                <a class="annonce-card"
                   href="detail.php?type=perdu&id=<?= urlencode($objet['A']) ?>">

                    <div class="annonce-contenu">
                        <span class="badge-perdu">OBJET PERDU</span>
                        <h3><?= htmlspecialchars($objet['D']) ?></h3>

                        <p class="annonce-type">
                            <?= htmlspecialchars($objet['E']) ?>
                        </p>

                        <p>
                            <?= htmlspecialchars(mb_strimwidth($objet['F'], 0, 100, '...')) ?>
                        </p>

                        <p class="annonce-date">
                            Déclaré le : <?= htmlspecialchars($objet['B']) ?>
                        </p>
                    </div>

                </a>

            <?php endforeach; ?>

        <?php else: ?>
            <p class="annonce-vide">Aucune annonce d'objet perdu pour le moment.</p>
        <?php endif; ?>

    </div>
    <?php if (count($objetsPerdus) > $limitePerdus): ?>

<div class="annonces-actions">
    <a class="btn-voir-plus"
       href="index.php?voir_perdus=<?= $limitePerdus + 3 ?>&voir_retrouves=<?= $limiteRetrouves ?>">
        Voir plus d'objets perdus
        <span>↓</span>
    </a>
</div>

<?php endif; ?>
</section>


<!-- AFFICHAGE NORMAL : LES 3 DERNIERS OBJETS RETROUVÉS -->

<section class="annonces-section">

    <h2>Objets récemment retrouvés</h2>
    <p class="annonces-intro">
        Découvrez les objets retrouvés et signalés sur notre plateforme.
    </p>

    <div class="annonces-grid">

        <?php if (!empty($objetsRetrouves)): ?>

            <?php foreach (array_slice($objetsRetrouves, 0, $limiteRetrouves) as $objet): ?>

                <?php
                $photos = explode(';', $objet['I'] ?? '');
                $premierePhoto = trim($photos[0]);
                ?>

                <a class="annonce-card"
                   href="detail.php?type=retrouve&id=<?= urlencode($objet['A']) ?>">

                    <?php if (!empty($premierePhoto)): ?>
                        <img src="<?= htmlspecialchars($premierePhoto) ?>"
                             alt="Photo de l'objet retrouvé">
                    <?php endif; ?>

                    <div class="annonce-contenu">
                        <span class="badge-retrouve">OBJET RETROUVÉ</span>
                        <h3><?= htmlspecialchars($objet['D']) ?></h3>

                        <p class="annonce-type">
                            <?= htmlspecialchars($objet['E']) ?>
                        </p>

                        <p>
                            <strong>Lieu :</strong>
                            <?= htmlspecialchars($objet['H']) ?>
                        </p>

                        <p class="annonce-date">
                            Déclaré le : <?= htmlspecialchars($objet['B']) ?>
                        </p>
                    </div>

                </a>

            <?php endforeach; ?>

        <?php else: ?>
            <p class="annonce-vide">Aucun objet retrouvé pour le moment.</p>
        <?php endif; ?>

    </div>
    <?php if (count($objetsRetrouves) > $limiteRetrouves): ?>

<div class="annonces-actions">
    <a class="btn-voir-plus"
       href="index.php?voir_perdus=<?= $limitePerdus ?>&voir_retrouves=<?= $limiteRetrouves + 3 ?>">
        Voir plus d'objets retrouvés
        <span>↓</span>
    </a>
</div>

<?php endif; ?>
</section>

<?php endif; ?>


</main>

<?php include 'declaration_modal.php'; ?>

<!-- LOADER DE SOUMISSION -->
<div id="loaderDeclaration" class="loader-declaration" aria-hidden="true">
    <div class="loader-boite">
        <div class="loader-cercle"></div>
        <h3>Enregistrement en cours</h3>
        <p>Veuillez patienter pendant l'enregistrement de votre déclaration...</p>
    </div>
</div>

<!-- NOTIFICATION -->
<?php if (!empty($notification)): ?>

    <div
        id="notificationDeclaration"
        class="notification-declaration <?= htmlspecialchars($notification['type'] ?? 'success') ?>"
        role="status"
        aria-live="polite"
    >
        <span class="notification-icone">
            <?= ($notification['type'] ?? '') === 'success' ? '✓' : '!' ?>
        </span>

        <div class="notification-texte">
            <strong>
                <?= ($notification['type'] ?? '') === 'success'
                    ? 'Déclaration ajoutée !'
                    : 'Enregistrement impossible' ?>
            </strong>

            <p><?= htmlspecialchars($notification['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <button type="button" class="fermer-notification"
                onclick="this.parentElement.remove()"
                aria-label="Fermer">
            &times;
        </button>
    </div>

<?php endif; ?>

<?php
unset(
    $_SESSION['erreurs'],
    $_SESSION['ancien_nom'],
    $_SESSION['ancien_type'],
    $_SESSION['ancien_autre_type'],
    $_SESSION['ancienne_description'],
    $_SESSION['ancien_whatsapp'],
    $_SESSION['ancien_telephone'],
    $_SESSION['ancienne_adresse'],
    $_SESSION['formulaire_actif']
);
?>
<script>
    const ouvrirModalApresErreur = <?= json_encode($ouvrirModal) ?>;
    const formulaireActif = <?= json_encode($formulaireActif) ?>;
</script>
<script src="script.js"></script>

<?php include 'footer.html'; ?>


<?php if (isset($_SESSION['notification'])): ?>

<?php $notification = $_SESSION['notification']; ?>

<div class="notification-card">

    <?php if ($notification['type'] === 'success'): ?>

        <div class="icone">✓</div>
        <h3>Déclaration réussie !</h3>

    <?php else: ?>

        <div class="icone"
             style="background:#fde8e8;color:#dc2626;">✕</div>

        <h3 style="color:#dc2626;">Échec de l'enregistrement</h3>

    <?php endif; ?>

    <p>
        <?= htmlspecialchars($notification['message']) ?>
    </p>

</div>

<?php unset($_SESSION['notification']); ?>

<?php endif; ?>