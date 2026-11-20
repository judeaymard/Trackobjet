
<div class="modal-declaration" id="modalDeclaration">

    <div class="fond-modal"></div>

    <div class="contenu-modal">

        <button type="button" class="fermer-modal" id="fermerDeclaration" aria-label="Fermer">
            &times;
        </button>

        <div class="container" id="container">

            <!-- FORMULAIRE OBJET PERDU -->
            <div class="form-container sign-in-container" id="form-perdu">

                <form action="traitement_perdu.php" method="POST">

                    <h2>Déclaration d'objet perdu</h2>
                    <p>Veuillez renseigner les informations concernant l'objet que vous avez perdu.</p>

                    <div class="champ">
                    <label for="nom-perdu">Nom de l'objet : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <input type="text" id="nom-perdu" name="name"
               value="<?= htmlspecialchars($_SESSION['ancien_nom'] ?? '') ?>"
               placeholder="Ex : Iphone" required>

        <?php if (!empty($_SESSION['erreurs']['nom'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['nom']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>
<div class="champ">
<label for="type-perdu">Type d'objet : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <select id="type-perdu" name="type" required>
            <option value="">Sélectionnez un type d'objet</option>
            <?php
            $types = [
                'telephone' => 'Téléphone',
                'documents' => 'Documents',
                'sacs' => 'Sacs',
                'cles' => 'Clés',
                'bijoux' => 'Bijoux',
                'vetements' => 'Vêtements',
                'autres' => 'Autres'
            ];

            foreach ($types as $valeur => $libelle):
            ?>
                <option value="<?= $valeur ?>"
                    <?= ($_SESSION['ancien_type'] ?? '') === $valeur ? 'selected' : '' ?>>
                    <?= $libelle ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
    type="text"
    id="autre-type-perdu"
    name="autre_type"
    placeholder="Précisez le type d'objet"
    value="<?= htmlspecialchars($_SESSION['ancien_autre_type'] ?? '') ?>"
    style="<?= ($_SESSION['ancien_type'] ?? '') === 'autres' ? '' : 'display:none;' ?>"
>

        <?php if (!empty($_SESSION['erreurs']['type'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['type']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>
<div class="champ haut">
<label for="desc-perdu">Description de l'objet perdu : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <textarea id="desc-perdu" name="description"
                  placeholder="Décrivez l'objet perdu..." required><?= htmlspecialchars($_SESSION['ancienne_description'] ?? '') ?></textarea>

        <?php if (!empty($_SESSION['erreurs']['description'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['description']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

<!-- WHATSAPP OBJET PERDU -->
<div class="champ">
    <label for="wa-perdu">Numéro WhatsApp : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <div class="telephone-container">
            <span class="prefixe">+22901</span>

            <input type="text"
                id="wa-perdu"
                name="phone_whatsapp"
                class="numero-telephone"
                value="<?= htmlspecialchars(substr(preg_replace('/\D/', '', $_SESSION['ancien_whatsapp'] ?? ''), -8)) ?>"
                placeholder="XXXXXXXX"
                maxlength="8"
                minlength="8"
                inputmode="numeric"
                pattern="[0-9]{8}"
                required>
        </div>

        <?php if (!empty($_SESSION['erreurs']['whatsapp'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['whatsapp']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

<!-- TELEPHONE OBJET PERDU -->
<div class="champ">
    <label for="tel-perdu">Numéro de téléphone :</label>

    <div class="zone-saisie">
        <div class="telephone-container">
            <span class="prefixe">+22901</span>

            <input type="text"
                id="tel-perdu"
                name="tel_phone"
                class="numero-telephone"
                value="<?= htmlspecialchars(substr(preg_replace('/\D/', '', $_SESSION['ancien_telephone'] ?? ''), -8)) ?>"
                placeholder="XXXXXXXX"
                maxlength="8"
                minlength="8"
                inputmode="numeric"
                pattern="[0-9]{8}">
        </div>

        <?php if (!empty($_SESSION['erreurs']['telephone'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['telephone']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

                    <button type="submit" name="declaration_perdue" value="1">
                        Envoyer ma déclaration
                    </button>

                </form>
            </div>

            <!-- FORMULAIRE OBJET RETROUVÉ -->
            <div class="form-container sign-up-container" id="form-retrouve">

                <form action="traitement_retrouve.php" method="POST" enctype="multipart/form-data">

                    <h2>Déclaration d'objet retrouvé</h2>
                    <p>Aidez le propriétaire à retrouver son objet.</p>

                    <div class="champ">
                    <label for="nom-retrouve">Nom de l'objet : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <input type="text" id="nom-retrouve" name="name"
               value="<?= htmlspecialchars($_SESSION['ancien_nom'] ?? '') ?>"
               placeholder="Ex : Samsung" required>

        <?php if (!empty($_SESSION['erreurs']['nom'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['nom']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

<div class="champ">
<label for="type-retrouve">Type d'objet : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <select id="type-retrouve" name="type" required>
            <option value="">Sélectionnez un type d'objet</option>

            <?php
            $types = [
                'telephone' => 'Téléphone',
                'documents' => 'Documents',
                'sacs' => 'Sacs',
                'cles' => 'Clés',
                'bijoux' => 'Bijoux',
                'vetements' => 'Vêtements',
                'autres' => 'Autres'
            ];

            foreach ($types as $valeur => $libelle):
            ?>
                <option value="<?= $valeur ?>"
                    <?= ($_SESSION['ancien_type'] ?? '') === $valeur ? 'selected' : '' ?>>
                    <?= $libelle ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
    type="text"
    id="autre-type-retrouve"
    name="autre_type"
    placeholder="Précisez le type d'objet"
    value="<?= htmlspecialchars($_SESSION['ancien_autre_type'] ?? '') ?>"
    style="<?= ($_SESSION['ancien_type'] ?? '') === 'autres' ? '' : 'display:none;' ?>"
>

        <?php if (!empty($_SESSION['erreurs']['type'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['type']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>
<div class="champ">
<label for="photos-retrouve">Photos de l'objet<span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <input type="file" id="photos-retrouve"
               name="photos[]" accept="image/*" multiple required>

        <?php if (!empty($_SESSION['erreurs']['photos'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['photos']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

<!-- WHATSAPP OBJET RETROUVE -->
<div class="champ">
    <label for="wa-retrouve">Numéro WhatsApp : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <div class="telephone-container">
            <span class="prefixe">+22901</span>

            <input type="text"
                id="wa-retrouve"
                name="phone_whatsapp"
                class="numero-telephone"
                value="<?= htmlspecialchars(substr(preg_replace('/\D/', '', $_SESSION['ancien_whatsapp'] ?? ''), -8)) ?>"
                placeholder="XXXXXXXX"
                maxlength="8"
                minlength="8"
                inputmode="numeric"
                pattern="[0-9]{8}"
                required>
        </div>

        <?php if (!empty($_SESSION['erreurs']['whatsapp'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['whatsapp']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

<!-- TELEPHONE OBJET RETROUVE -->
<div class="champ">
    <label for="tel-retrouve">Numéro de téléphone :</label>

    <div class="zone-saisie">
        <div class="telephone-container">
            <span class="prefixe">+22901</span>

            <input type="text"
                id="tel-retrouve"
                name="tel_phone"
                class="numero-telephone"
                value="<?= htmlspecialchars(substr(preg_replace('/\D/', '', $_SESSION['ancien_telephone'] ?? ''), -8)) ?>"
                placeholder="XXXXXXXX"
                maxlength="8"
                minlength="8"
                inputmode="numeric"
                pattern="[0-9]{8}">
        </div>

        <?php if (!empty($_SESSION['erreurs']['telephone'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['telephone']) ?>
            </small>
        <?php endif; ?>
    </div>
</div><div class="champ haut">
<label for="adresse-retrouve">Votre adresse : <span class="obligatoire">*</span></label>

    <div class="zone-saisie">
        <textarea id="adresse-retrouve" name="address"
                  placeholder="Indiquez votre adresse complète..."
                  required><?= htmlspecialchars($_SESSION['ancienne_adresse'] ?? '') ?></textarea>

        <?php if (!empty($_SESSION['erreurs']['adresse'])): ?>
            <small class="erreur-champ">
                <?= htmlspecialchars($_SESSION['erreurs']['adresse']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>
                    <button type="submit" name="declaration_retrouvee" value="1">
                        Envoyer ma déclaration
                    </button>

                </form>
            </div>

           
<!-- PANNEAU ANIMÉ -->
<div class="overlay-container">
    <div class="overlay">

        <div class="overlay-panel overlay-left">

            <div class="panel_haut">
                <img src="logo.png" alt="Logo TrackObjet" class="logo_panneau">
                <h2>Vous avez perdu un objet ?</h2>
            </div>

            <div class="panel_bas">
                <p>Déclarez-le ici pour mettre toutes les chances de votre côté.</p>
                <button type="button" class="ghost" id="btnPerdu">
                    Déclarer un objet perdu
                </button>
            </div>

        </div>

        <div class="overlay-panel overlay-right">

            <div class="panel_haut">
                <img src="logo.png" alt="Logo TrackObjet" class="logo_panneau">
                <h2>Vous avez retrouvé un objet ?</h2>
            </div>

            <div class="panel_bas">
                <p>Déclarez-le pour aider son propriétaire à le récupérer.</p>
                <button type="button" class="ghost" id="btnRetrouve">
                    Déclarer un objet retrouvé
                </button>
            </div>

        </div>

    </div>
</div>
</div><!-- Fin container -->
    </div><!-- Fin contenu-modal -->
</div><!-- Fin modal-declaration -->
<!-- LOADER TRACKOBJET -->
<div id="loading-overlay">
    <div class="loader-content">
        <div class="loader-logo">
            <img src="logo.png" alt="Logo TrackObjet">
        </div>

        <div class="loader-spinner"></div>

        <h3>TrackObjet</h3>
        <p id="loader-message">Préparation de votre déclaration...</p>
    </div>
</div>