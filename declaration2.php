
<?php 
$title = "Faire une déclaration";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<main class="page-declaration">
<div class="container" id="container">

    <!-- FORMULAIRE OBJET PERDU -->
    <div class="form-container sign-in-container" id="form-perdu">
        <form action="traitement_perdu.php" method="POST">
            <h2>Déclaration d'objet perdu</h2>
            <p>Veuillez renseigner les informations concernant l'objet que vous avez perdu.</p>

            <div class="champ">
                <label for="nom-perdu">Nom de l'objet :</label>
                <input type="text" id="nom-perdu" name="name" placeholder="Ex : Iphone" required>
            </div>

            <div class="champ">
                <label for="type-perdu">Type d'objet :</label>
                <select id="type-perdu" name="type" required>
                    <option value="">Sélectionnez un type d'objet</option>
                    <option value="telephone">Téléphone</option>
                    <option value="documents">Documents</option>
                    <option value="sacs">Sacs</option>
                    <option value="cles">Clés</option>
                    <option value="bijoux">Bijoux</option>
                    <option value="vetements">Vêtements</option>
                    <option value="autres">Autres</option>
                </select>
            </div>

            <div class="champ">
            <div class="champ">
    <label for="wa-perdu">Numéro WhatsApp :</label>
    <div class="telephone-container">
        <span class="prefixe">+22901</span>
        <input type="text"
            id="wa-perdu"
            name="phone_whatsapp"
            class="numero-telephone"
            placeholder="XXXXXXXX"
            maxlength="8"
            minlength="8"
            inputmode="numeric"
            pattern="[0-9]{8}"
            autocomplete="tel-national"
            required>
    </div>
</div>

<div class="champ">
    <label for="tel-perdu">Numéro de téléphone :</label>
    <div class="telephone-container">
        <span class="prefixe">+22901</span>
        <input type="text"
            id="tel-perdu"
            name="tel_phone"
            class="numero-telephone"
            placeholder="XXXXXXXX"
            maxlength="8"
            minlength="8"
            inputmode="numeric"
            pattern="[0-9]{8}"
            autocomplete="off">
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
                <label for="nom-retrouve">Nom de l'objet :</label>
                <input type="text" id="nom-retrouve" name="name" placeholder="Ex : Samsung" required>
            </div>

            <div class="champ">
                <label for="type-retrouve">Type d'objet :</label>
                <select id="type-retrouve" name="type" required>
                    <option value="">Sélectionnez un type d'objet</option>
                    <option value="telephone">Téléphone</option>
                    <option value="documents">Documents</option>
                    <option value="sacs">Sacs</option>
                    <option value="cles">Clés</option>
                    <option value="bijoux">Bijoux</option>
                    <option value="vetements">Vêtements</option>
                    <option value="autres">Autres</option>
                </select>
            </div>

            <div class="champ">
                <label for="photos-retrouve">Photos de l'objet :</label>
                <input type="file" id="photos-retrouve" name="photos[]" accept="image/*" multiple required>
            </div>

            <div class="champ">
            <div class="champ">
    <label for="wa-retrouve">Numéro WhatsApp :</label>
    <div class="telephone-container">
        <span class="prefixe">+22901</span>
        <input type="text"
            id="wa-retrouve"
            name="phone_whatsapp"
            class="numero-telephone"
            placeholder="XXXXXXXX"
            maxlength="8"
            minlength="8"
            inputmode="numeric"
            pattern="[0-9]{8}"
            autocomplete="tel-national"
            required>
    </div>
</div>

<div class="champ">
    <label for="tel-retrouve">Numéro de téléphone :</label>
    <div class="telephone-container">
        <span class="prefixe">+22901</span>
        <input type="text"
            id="tel-retrouve"
            name="tel_phone"
            class="numero-telephone"
            placeholder="XXXXXXXX"
            maxlength="8"
            minlength="8"
            inputmode="numeric"
            pattern="[0-9]{8}"
            autocomplete="off">
    </div>
</div>

            <div class="champ haut">
                <label for="adresse-retrouve">Votre adresse :</label>
                <textarea id="adresse-retrouve" name="address" placeholder="Indiquez votre adresse complète..." required></textarea>
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
                <h2>Vous avez perdu un objet ?</h2>
                <p>Déclarez-le ici pour mettre toutes les chances de votre côté.</p>
                <button class="ghost" id="btnPerdu">Déclarer un objet perdu</button>
            </div>

            <div class="overlay-panel overlay-right">
                <h2>Vous avez retrouvé un objet ?</h2>
                <p>Déclarez-le pour aider son propriétaire à le récupérer.</p>
                <button class="ghost" id="btnRetrouve">Déclarer un objet retrouvé</button>
            </div>

        </div>
    </div>

</div>
</main>

<!-- ÉCRAN DE CHARGEMENT -->
<div id="loading-overlay">
    <div class="loading-card">
        <div class="spinner"></div>
        <h3>Chargement en cours...</h3>
        <p>Veuillez patienter pendant l'enregistrement.</p>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
