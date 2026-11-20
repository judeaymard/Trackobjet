
<?php
// Préparer les notifications à partir des déclarations Excel
$notifications = [];

$perdus = isset($objetsPerdus) && is_array($objetsPerdus)
    ? $objetsPerdus : [];

$retrouves = isset($objetsRetrouves) && is_array($objetsRetrouves)
    ? $objetsRetrouves : [];

// Ajouter les objets perdus
foreach ($perdus as $objet) {
    $notifications[] = [
        'type' => 'perdu',
        'titre' => 'Nouvel objet perdu',
        'nom' => $objet['D'] ?? 'Objet sans nom',
        'date' => $objet['B'] ?? '',
        'lien' => 'detail.php?type=perdu&id=' . urlencode($objet['A'] ?? '')
    ];
}

// Ajouter les objets retrouvés
foreach ($retrouves as $objet) {
    $notifications[] = [
        'type' => 'retrouve',
        'titre' => 'Nouvel objet retrouvé',
        'nom' => $objet['D'] ?? 'Objet sans nom',
        'date' => $objet['B'] ?? '',
        'lien' => 'detail.php?type=retrouve&id=' . urlencode($objet['A'] ?? '')
    ];
}

// Trier les annonces par date, de la plus récente à la plus ancienne
usort($notifications, function ($a, $b) {
    $dateA = strtotime($a['date']) ?: 0;
    $dateB = strtotime($b['date']) ?: 0;

    return $dateB <=> $dateA;
});

// Conserver les 5 annonces les plus récentes
$notificationsRecentes = array_slice($notifications, 0, 5);
$nombreNotifications = count($notificationsRecentes);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackObjet</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style2.css">
</head>

<body>

<header class="tete">

    <div class="menu">
        <a href="index.php" class="lien-accueil">
            <div class="logo">
                <img src="logo.png" alt="logo du site">
            </div>
            <h1>TrackObjet</h1>
        </a>
    </div>

    <div class="droite">

        <form class="search" action="index.php" method="get">
            <input
                type="search"
                name="q"
                placeholder="Rechercher..."
                value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit" aria-label="Rechercher">
                <svg width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m16 16 4 4"></path>
                </svg>
            </button>
        </form>

        <a href="#footer">Contact</a>

        <!-- BOUTON ET MENU NOTIFICATIONS -->
        <div class="notification-wrapper" id="notificationWrapper">

            <button
                type="button"
                class="notif"
                id="boutonNotifications"
                aria-label="Notifications"
                aria-expanded="false"
                aria-controls="menuNotifications"
            >
                <svg width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
                </svg>

                <?php if ($nombreNotifications > 0): ?>
                    <span class="badge">
                        <?= $nombreNotifications > 9 ? '9+' : $nombreNotifications ?>
                    </span>
                <?php endif; ?>
            </button>

            <!-- CONTENU DU MENU -->
            <div class="menu-notifications" id="menuNotifications">

                <div class="notification-entete">
                    <h3>Notifications</h3>
                    <span><?= $nombreNotifications ?> annonce(s)</span>
                </div>

                <div class="notification-liste">

                    <?php if (!empty($notificationsRecentes)): ?>

                        <?php foreach ($notificationsRecentes as $notif): ?>

                            <a href="<?= htmlspecialchars($notif['lien'], ENT_QUOTES, 'UTF-8') ?>"
                               class="notification-item">

                                <div class="notification-icone <?= $notif['type'] ?>">
                                    <?php if ($notif['type'] === 'perdu'): ?>
                                        <span>⌕</span>
                                    <?php else: ?>
                                        <span>✓</span>
                                    <?php endif; ?>
                                </div>

                                <div class="notification-details">
                                    <strong>
                                        <?= htmlspecialchars($notif['titre'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>

                                    <p>
                                        <?= htmlspecialchars($notif['nom'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <small>
                                        <?= htmlspecialchars($notif['date'], ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </div>

                            </a>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="notifications-vides">
                            <span>🔔</span>
                            <p>Aucune annonce pour le moment.</p>
                        </div>

                    <?php endif; ?>

                </div>

                <a href="index.php#annonces-perdues" class="notification-bas">
                    Consulter les annonces
                </a>

            </div>

        </div>

        <button type="button" class="btn-declaration" id="ouvrirDeclaration">
            Faire une déclaration
        </button>

    </div>

</header>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const wrapper = document.getElementById('notificationWrapper');
    const bouton = document.getElementById('boutonNotifications');

    if (!wrapper || !bouton) return;

    bouton.addEventListener('click', function (event) {
        event.stopPropagation();

        const ouvert = wrapper.classList.toggle('notification-ouvert');

        bouton.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
    });

    // Fermer en cliquant à l'extérieur
    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) {
            wrapper.classList.remove('notification-ouvert');
            bouton.setAttribute('aria-expanded', 'false');
        }
    });

    // Fermer avec la touche Échap
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            wrapper.classList.remove('notification-ouvert');
            bouton.setAttribute('aria-expanded', 'false');
        }
    });

});
</script>
</body>
</html>
