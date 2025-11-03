const form = document.getElementById('chat-form');
const messageInput = document.getElementById('message');
const chatHistory = document.getElementById('chat-history');
const chatError = document.getElementById('chat-error');

let history = [];

form.addEventListener('submit', function(e) {
    e.preventDefault();
    chatError.textContent = '';
    const message = messageInput.value.trim();
    if (!message) {
        chatError.textContent = 'Veuillez entrer un message.';
        return;
    }
    // Affiche le message utilisateur
    history.push({ user: 'Vous', message });
    updateHistory();
    messageInput.value = '';
    // Envoie AJAX
    fetch('php-crud/controllers/chatController.php?action=send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            history.push({ user: 'Agent IA', message: data.response });
            updateHistory();
        } else {
            chatError.textContent = data.error || 'Erreur lors de la réponse.';
        }
    })
    .catch(() => {
        chatError.textContent = 'Erreur de connexion au serveur.';
    });
});

function updateHistory() {
    chatHistory.innerHTML = '';
    history.forEach(msg => {
        const div = document.createElement('div');
        div.style.marginBottom = '8px';
        div.innerHTML = `<strong>${msg.user}:</strong> ${msg.message}`;
        chatHistory.appendChild(div);
    });
}
