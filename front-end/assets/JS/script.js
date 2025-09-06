// Fonction pour ouvrir une fenêtre modale de paiement adaptée au vote et paiement
function vote(artiste) {
    // Création du fond semi-transparent
    let overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);

    // Création de la modale
    let modal = document.createElement('div');
    modal.className = 'modal-paiement';
    // Ajout de l'input numéro (id="phone")
    modal.innerHTML = `
        <h3>Paiement Mobile Money pour voter : ${artiste}</h3>
        <label>Numéro Mobile Money :</label><br>
        <input type="tel" id="phone" placeholder="Ex: 06-000-00-00" required><br>
        <label>Nombre de voix :</label><br>
        <div style="display:flex;align-items:center;justify-content:center;gap:10px;">
            <button type="button" id="vote-5">5 voix</button>
            <button type="button" id="vote-10">10 voix</button>
            <button type="button" id="vote-20">20 voix</button>
        </div>
        <label>Montant (FCFA) :</label><br>
        <input type="number" id="mm-amount" value="200" readonly required><br>
        <input type="hidden" id="vote-count" value="5">
        <button id="mm-pay">Payer et voter</button>
        <button id="mm-cancel">Annuler</button>
    `;
    document.body.appendChild(modal);

    // Centrage de la modale
    modal.style.position = 'fixed';
    modal.style.top = '50%';
    modal.style.left = '50%';
    modal.style.transform = 'translate(-50%, -50%)';
    modal.style.zIndex = '1001';

    // Gestion des boutons voix
    const voteCountInput = modal.querySelector('#vote-count');
    const amountInput = modal.querySelector('#mm-amount');
    modal.querySelector('#vote-5').onclick = function () {
        voteCountInput.value = 5;
        amountInput.value = 200;
    };
    modal.querySelector('#vote-10').onclick = function () {
        voteCountInput.value = 10;
        amountInput.value = 500;
    };
    modal.querySelector('#vote-20').onclick = function () {
        voteCountInput.value = 20;
        amountInput.value = 1000;
    };

    // Annuler la modale
    modal.querySelector('#mm-cancel').onclick = function () {
        document.body.removeChild(modal);
        document.body.removeChild(overlay);
    };

    // Bouton paiement et vote
    modal.querySelector('#mm-pay').onclick = function () {
        // Récupération des valeurs du formulaire
        let phoneInput = modal.querySelector('#phone');
        let phoneNumber = phoneInput.value.trim();
        let voteCount = voteCountInput.value;
        let amount = amountInput.value;
        let participantId = artiste.match(/\d+/) ? artiste.match(/\d+/)[0] : 1;

        // Vérification avancée du numéro
        // Liste des préfixes valides (exemple Congo : 05, 06, 07, 08, 09)
        const validPrefixes = ['05', '06', '07', '08', '09'];
        const phoneRegex = /^0[5-9][0-9]{7}$/;
        const prefix = phoneNumber.substring(0, 2);
        if (!phoneRegex.test(phoneNumber) || !validPrefixes.includes(prefix)) {
            alert("Numéro non valide ou inexistant. Veuillez entrer un numéro Mobile Money correct.");
            return;
        }

        // Envoi des données au backend pour paiement et vote
        fetch('traitement_vote.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `phone=${encodeURIComponent(phoneNumber)}&participant_id=${encodeURIComponent(participantId)}&vote_count=${encodeURIComponent(voteCount)}&amount=${encodeURIComponent(amount)}`
        })
            .then(response => response.text())
            .then(data => {
                // Vérification côté backend si le numéro existe (réponse personnalisée)
                if (data.includes('numero_invalide')) {
                    alert("Ce numéro n'existe pas ou n'est pas éligible au paiement Mobile Money.");
                } else {
                    alert("Paiement et vote enregistrés !");
                    document.body.removeChild(modal);
                    document.body.removeChild(overlay);
                }
            })
            .catch(error => {
                alert('Erreur lors du paiement ou du vote.');
                document.body.removeChild(modal);
                document.body.removeChild(overlay);
            });
    };
}

// requête de vote
function voter(id) {
    window.location.href = `backend/paiement.php?id=${id}`;
}

// fonction de reception d'argent
function sendPhoneNumberForPayment() {
    const phoneInput = document.getElementById('phone');
    const phoneNumber = phoneInput.value.trim();

    // Vérification simple (numéro commençant par 08, 09, 05, etc. et 9 chiffres)
    const phoneRegex = /^(0[5-9])[0-9]{7}$/;

    if (!phoneRegex.test(phoneNumber)) {
        alert("06-688-23-63");
        return;
    }

    // Envoi vers le backend avec fetch
    fetch('traitement_vote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `phone=${encodeURIComponent(phoneNumber)}`
    })
        .then(response => response.text())
        .then(data => {
            alert("Numéro soumis avec succès !");
            console.log(data);
        })
        .catch(error => {
            console.error('Erreur :', error);
        });
}
