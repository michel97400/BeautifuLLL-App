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
        div.style.marginBottom = '12px';
        div.style.padding = '12px';
        div.style.borderRadius = '8px';
        div.style.backgroundColor = msg.user === 'Vous' ? '#e7f3ff' : '#fff';
        div.style.border = '1px solid ' + (msg.user === 'Vous' ? '#0078d7' : '#e0e0e0');
        
        const userLabel = document.createElement('strong');
        userLabel.textContent = msg.user + ': ';
        userLabel.style.color = msg.user === 'Vous' ? '#0078d7' : '#333';
        userLabel.style.display = 'block';
        userLabel.style.marginBottom = '8px';
        
        const content = document.createElement('div');
        content.className = 'markdown-content';
        // Parse markdown si c'est une réponse de l'Agent IA
        if (msg.user === 'Agent IA' && typeof marked !== 'undefined') {
            content.innerHTML = marked.parse(msg.message);
        } else {
            content.textContent = msg.message;
        }
        
        div.appendChild(userLabel);
        div.appendChild(content);
        chatHistory.appendChild(div);
    });
    // Scroll vers le bas
    chatHistory.scrollTop = chatHistory.scrollHeight;
}
