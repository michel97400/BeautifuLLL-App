const messageInput = document.getElementById('message');
const chatHistory = document.getElementById('chat-history');
const chatError = document.getElementById('chat-error');
const sessionList = document.getElementById('session-list');

let currentHistory = [];
let currentSessionId = null;

// Charger l'historique des sessions au démarrage
loadSessionHistory();

// Gérer l'envoi avec Enter (Shift+Enter pour nouvelle ligne)
messageInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

/**
 * Charge l'historique des sessions
 */
function loadSessionHistory() {
    fetch('php-crud/controllers/chatController.php?action=history')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displaySessionList(data.sessions);
            } else {
                console.error('Erreur API:', data.error);
                sessionList.innerHTML = '<div class="empty-state"><p>' + (data.error || 'Aucune conversation') + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Erreur fetch:', error);
            sessionList.innerHTML = '<div class="empty-state"><p>Erreur de chargement</p></div>';
        });
}

/**
 * Affiche la liste des sessions
 */
function displaySessionList(sessions) {
    if (!sessions || sessions.length === 0) {
        sessionList.innerHTML = '<div class="empty-state"><p>Aucune conversation</p></div>';
        return;
    }
    
    sessionList.innerHTML = '';
    sessions.forEach(session => {
        const div = document.createElement('div');
        div.className = 'session-item';
        if (session.id_session == currentSessionId) {
            div.classList.add('active');
        }
        
        const date = new Date(session.date_heure_debut);
        const dateStr = date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        div.innerHTML = `
            <div class="session-date">${dateStr}</div>
            <div>${session.titre ? `<span>${session.titre}</span>` : ''}</div>
            <div class="session-info">
                <span>${session.nom_agent || 'Agent IA'}</span>
                <span class="session-messages">${session.nb_messages || 0} msgs</span>
                
            </div>
            <button class="session-delete" onclick="deleteSession(${session.id_session}, event)">Supprimer</button>
        `;
        
        div.onclick = (e) => {
            if (!e.target.classList.contains('session-delete')) {
                loadSession(session.id_session);
            }
        };
        
        sessionList.appendChild(div);
    });
}

/**
 * Charge une session existante
 */
function loadSession(sessionId) {
    fetch('php-crud/controllers/chatController.php?action=load', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_session=' + sessionId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentSessionId = sessionId;
            currentHistory = data.messages || [];
            updateChatDisplay();
            loadSessionHistory(); // Rafraîchir la liste
        } else {
            chatError.textContent = data.error || 'Erreur lors du chargement';
        }
    })
    .catch(() => {
        chatError.textContent = 'Erreur de connexion au serveur.';
    });
}

/**
 * Démarre une nouvelle conversation
 */
function startNewChat() {
    fetch('php-crud/controllers/chatController.php?action=reset', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentSessionId = null;
            currentHistory = [];
            updateChatDisplay();
            loadSessionHistory();
        }
    });
}

/**
 * Supprime une session
 */
function deleteSession(sessionId, event) {
    event.stopPropagation();
    
    if (!confirm('Voulez-vous vraiment supprimer cette conversation ?')) {
        return;
    }
    
    fetch('php-crud/controllers/chatController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_session=' + sessionId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (sessionId == currentSessionId) {
                startNewChat();
            } else {
                loadSessionHistory();
            }
        } else {
            alert(data.error || 'Erreur lors de la suppression');
        }
    });
}

/**
 * Envoie un message
 */
function sendMessage() {
    chatError.textContent = '';
    const message = messageInput.value.trim();
    
    if (!message) {
        chatError.textContent = 'Veuillez entrer un message.';
        return;
    }
    
    // Afficher le message utilisateur immédiatement
    currentHistory.push({
        role: 'user',
        contenu: message,
        date_envoi: new Date().toISOString()
    });
    updateChatDisplay();
    messageInput.value = '';
    
    // Afficher un indicateur de chargement
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loading-indicator';
    loadingDiv.style.cssText = 'margin-bottom: 12px; padding: 12px; border-radius: 8px; background: #fff; border: 1px solid #e0e0e0;';
    loadingDiv.innerHTML = '<div style="color: #666;"><em>Agent IA est en train d\'écrire...</em></div>';
    chatHistory.appendChild(loadingDiv);
    chatHistory.scrollTop = chatHistory.scrollHeight;
    
    // Envoyer la requête
    fetch('php-crud/controllers/chatController.php?action=send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(message)
    })
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('Response text:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Parse error:', e);
            throw new Error('Réponse invalide du serveur: ' + text.substring(0, 200));
        }
    })
    .then(data => {
        // Retirer l'indicateur de chargement
        const loader = document.getElementById('loading-indicator');
        if (loader) loader.remove();
        
        if (data.success) {
            currentSessionId = data.session_id;
            currentHistory.push({
                role: 'assistant',
                contenu: data.response,
                date_envoi: new Date().toISOString()
            });
            updateChatDisplay();
            loadSessionHistory(); // Rafraîchir la liste
        } else {
            chatError.textContent = data.error || 'Erreur lors de la réponse.';
            // Retirer le dernier message utilisateur si erreur
            currentHistory.pop();
            updateChatDisplay();
        }
    })
    .catch((error) => {
        console.error('Fetch error:', error);
        const loader = document.getElementById('loading-indicator');
        if (loader) loader.remove();
        chatError.textContent = 'Erreur de connexion: ' + error.message;
        // Retirer le dernier message utilisateur si erreur
        currentHistory.pop();
        updateChatDisplay();
    });
}

/**
 * Met à jour l'affichage du chat
 */
function updateChatDisplay() {
    chatHistory.innerHTML = '';
    
    if (currentHistory.length === 0) {
        chatHistory.innerHTML = '<div class="empty-state"><h3>Démarrez une conversation</h3><p>Posez votre première question à l\'agent IA</p></div>';
        return;
    }
    
    currentHistory.forEach(msg => {
        const div = document.createElement('div');
        div.style.marginBottom = '12px';
        div.style.padding = '12px';
        div.style.borderRadius = '8px';
        
        // Supporter les deux formats de données
        const role = msg.role || msg.emetteur || 'user';
        const content = msg.contenu || msg.contenu_message || '';
        
        div.style.backgroundColor = role === 'user' ? '#e7f3ff' : '#fff';
        div.style.border = '1px solid ' + (role === 'user' ? '#0078d7' : '#e0e0e0');
        
        const userLabel = document.createElement('strong');
        userLabel.textContent = (role === 'user' ? 'Vous' : 'Agent IA') + ' : ';
        userLabel.style.color = role === 'user' ? '#0078d7' : '#333';
        userLabel.style.display = 'block';
        userLabel.style.marginBottom = '8px';
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'markdown-content';
        
        // Parser le markdown pour les réponses de l'agent
        if (role === 'assistant' && typeof marked !== 'undefined') {
            contentDiv.innerHTML = marked.parse(content);
        } else {
            contentDiv.textContent = content;
        }
        
        div.appendChild(userLabel);
        div.appendChild(contentDiv);
        chatHistory.appendChild(div);
    });
    
    // Scroll vers le bas
    chatHistory.scrollTop = chatHistory.scrollHeight;
}