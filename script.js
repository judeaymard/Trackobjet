
// =====================================
// ELEMENTS PRINCIPAUX
// =====================================

const container = document.getElementById("container");

const btnPerdu = document.getElementById("btnPerdu");
const btnRetrouve = document.getElementById("btnRetrouve");

const ouvrirDeclaration = document.getElementById("ouvrirDeclaration");
const fermerDeclaration = document.getElementById("fermerDeclaration");
const modalDeclaration = document.getElementById("modalDeclaration");


// =====================================
// OUVRIR ET FERMER LA FENETRE
// =====================================

// =====================================
// OUVERTURE DE LA DECLARATION AVEC LOADER
// =====================================

if (ouvrirDeclaration && modalDeclaration) {

    ouvrirDeclaration.addEventListener("click", function () {

        const loader = document.getElementById("loading-overlay");
        const message = document.getElementById("loader-message");

        if (loader) {
            message.textContent = "Préparation de votre déclaration...";
            loader.style.display = "flex";
        }

        setTimeout(function () {

            modalDeclaration.classList.add("active");
            document.body.classList.add("modal-ouvert");

            if (loader) {
                loader.style.display = "none";
            }

        }, 3000);

    });

}

function fermerModal() {
    if (modalDeclaration) {
        modalDeclaration.classList.remove("active");
        document.body.classList.remove("modal-ouvert");
    }
}

if (fermerDeclaration) {
    fermerDeclaration.addEventListener("click", fermerModal);
}

if (modalDeclaration) {
    modalDeclaration.addEventListener("click", (event) => {
        if (event.target === modalDeclaration) {
            fermerModal();
        }
    });
}

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modalDeclaration) {
        fermerModal();
    }
});


// =====================================
// CHOIX OBJET PERDU / RETROUVE
// =====================================

if (btnPerdu && container) {
    btnPerdu.addEventListener("click", () => {
        container.classList.remove("right-panel-active");
    });
}

if (btnRetrouve && container) {
    btnRetrouve.addEventListener("click", () => {
        container.classList.add("right-panel-active");
    });
}


// =====================================
// REOUVERTURE APRES UNE ERREUR PHP
// =====================================

if (
    typeof ouvrirModalApresErreur !== "undefined" &&
    ouvrirModalApresErreur &&
    modalDeclaration &&
    container
) {
    modalDeclaration.classList.add("active");
    document.body.classList.add("modal-ouvert");

    if (formulaireActif === "retrouve") {
        container.classList.add("right-panel-active");
    } else {
        container.classList.remove("right-panel-active");
    }
}


// =====================================
// GESTION DU TYPE D'OBJET AUTRES
// =====================================

function gererTypeAutre(selectId, inputId) {
    const select = document.getElementById(selectId);
    const input = document.getElementById(inputId);

    if (!select || !input) return;

    function actualiserChamp() {
        if (select.value === "autres") {
            input.style.display = "block";
        } else {
            input.style.display = "none";
            input.value = "";
        }
    }

    select.addEventListener("change", actualiserChamp);
    actualiserChamp();
}

gererTypeAutre("type-perdu", "autre-type-perdu");
gererTypeAutre("type-retrouve", "autre-type-retrouve");




// =====================================
// LOADER AVANT ENVOI DES DECLARATIONS
// =====================================

document.addEventListener("DOMContentLoaded", function () {

    const loader = document.getElementById("loading-overlay");
    const message = document.getElementById("loader-message");

    const formulaires = document.querySelectorAll(
        "#modalDeclaration form"
    );

    formulaires.forEach(function (formulaire) {

        formulaire.addEventListener("submit", function (event) {

            if (formulaire.dataset.envoiEnCours === "oui") {
                event.preventDefault();
                return;
            }

            if (!formulaire.checkValidity()) {
                return;
            }

            event.preventDefault();

            if (!loader) {
                console.error("Loader introuvable !");
                return;
            }

            // Empêcher une nouvelle soumission
            formulaire.dataset.envoiEnCours = "oui";

            // Message du loader
            if (message) {
                message.textContent =
                    "Enregistrement de votre déclaration...";
            }

            // Fermer la fenêtre modale
            modalDeclaration.classList.remove("active");
            document.body.classList.remove("modal-ouvert");

            // Afficher le loader
            loader.style.display = "flex";

            // Attendre 3 secondes avant l'envoi à PHP
            setTimeout(function () {

                HTMLFormElement.prototype.submit.call(formulaire);

            }, 3000);

        });

    });

});
// =====================================
// DISPARITION AUTOMATIQUE NOTIFICATION
// =====================================

document.addEventListener("DOMContentLoaded", function () {

    const notification = document.getElementById(
        "notificationDeclaration"
    );

    if (notification) {

        setTimeout(function () {

            notification.style.transition =
                "opacity 0.4s ease, transform 0.4s ease";

            notification.style.opacity = "0";
            notification.style.transform = "translateX(30px)";

            setTimeout(function () {
                notification.remove();
            }, 400);

        }, 5000);

    }

});
// =====================================
// GESTION DES NUMEROS DE TELEPHONE
// =====================================

document.querySelectorAll(".numero-telephone").forEach(function(input) {

    input.addEventListener("keydown", function(e) {

        const touchesAutorisees = [
            "Backspace",
            "Delete",
            "ArrowLeft",
            "ArrowRight",
            "Tab",
            "Home",
            "End"
        ];

        if (
            e.ctrlKey ||
            e.metaKey ||
            touchesAutorisees.includes(e.key)
        ) {
            return;
        }

        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }

    });

    input.addEventListener("input", function() {

        this.value = this.value
            .replace(/[^0-9]/g, "")
            .slice(0, 8);

        this.setCustomValidity("");

    });

});