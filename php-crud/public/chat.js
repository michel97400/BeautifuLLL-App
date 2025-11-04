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
 * Télécharge une réponse en PDF
 */
function downloadResponseAsPDF(content, index) {
    // Créer le contenu HTML complet avec styles inline
    const htmlContent = `
        <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; padding: 20px; background: white; max-width: 800px;">
            <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #0078d7;">
                <h1 style="color: #0078d7; margin: 10px 0; font-size: 24px;">🤖 Réponse de l'Agent IA</h1>
                <p style="color: #666; font-size: 14px; margin: 5px 0;"><em>Généré le ${new Date().toLocaleString('fr-FR')}</em></p>
            </div>
            <div style="margin: 20px 0; font-size: 14px;">
                ${marked.parse(content)}
            </div>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center;">
                <p style="margin: 5px 0;">Document généré par BeautifuLLL-App</p>
            </div>
        </div>
    `;
    
    // Créer un élément temporaire
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = htmlContent;
    tempDiv.style.cssText = 'position: fixed; top: 0; left: 0; width: 210mm; background: white; z-index: -1; opacity: 0;';
    
    // Ajouter à la page
    document.body.appendChild(tempDiv);
    
    // Appliquer les styles après insertion dans le DOM
    setTimeout(() => {
        const contentDiv = tempDiv.querySelector('div > div:nth-child(2)');
        
        if (contentDiv) {
            // Styliser les titres
            const headings = contentDiv.querySelectorAll('h1, h2, h3, h4, h5, h6');
            headings.forEach(h => {
                h.style.color = '#0078d7';
                h.style.marginTop = '16px';
                h.style.marginBottom = '8px';
                h.style.fontWeight = '600';
            });
            
            // Styliser le code
            const codeBlocks = contentDiv.querySelectorAll('code');
            codeBlocks.forEach(code => {
                if (!code.parentElement || code.parentElement.tagName !== 'PRE') {
                    code.style.backgroundColor = '#f4f4f4';
                    code.style.padding = '2px 6px';
                    code.style.borderRadius = '3px';
                    code.style.fontFamily = 'Courier New, monospace';
                    code.style.fontSize = '13px';
                }
            });
            
            // Styliser les blocs pre
            const preBlocks = contentDiv.querySelectorAll('pre');
            preBlocks.forEach(pre => {
                pre.style.backgroundColor = '#f4f4f4';
                pre.style.padding = '12px';
                pre.style.borderRadius = '5px';
                pre.style.margin = '10px 0';
                pre.style.overflowX = 'auto';
                const code = pre.querySelector('code');
                if (code) {
                    code.style.backgroundColor = 'transparent';
                    code.style.padding = '0';
                }
            });
            
            // Styliser les tableaux
            const tables = contentDiv.querySelectorAll('table');
            tables.forEach(table => {
                table.style.borderCollapse = 'collapse';
                table.style.width = '100%';
                table.style.margin = '15px 0';
                
                const ths = table.querySelectorAll('th');
                ths.forEach(th => {
                    th.style.backgroundColor = '#0078d7';
                    th.style.color = 'white';
                    th.style.padding = '8px';
                    th.style.border = '1px solid #ddd';
                    th.style.textAlign = 'left';
                });
                
                const tds = table.querySelectorAll('td');
                tds.forEach(td => {
                    td.style.padding = '8px';
                    td.style.border = '1px solid #ddd';
                });
            });
            
            // Styliser les citations
            const blockquotes = contentDiv.querySelectorAll('blockquote');
            blockquotes.forEach(bq => {
                bq.style.borderLeft = '4px solid #0078d7';
                bq.style.paddingLeft = '15px';
                bq.style.color = '#666';
                bq.style.margin = '15px 0';
                bq.style.fontStyle = 'italic';
            });
            
            // Styliser les listes
            const lists = contentDiv.querySelectorAll('ul, ol');
            lists.forEach(list => {
                list.style.margin = '10px 0';
                list.style.paddingLeft = '25px';
            });
            
            const listItems = contentDiv.querySelectorAll('li');
            listItems.forEach(li => {
                li.style.margin = '5px 0';
            });
        }
        
        // Options pour html2pdf
        const opt = {
            margin: 10,
            filename: `Agent-IA-Reponse-${new Date().getTime()}.pdf`,
            image: { type: 'jpeg', quality: 0.95 },
            html2canvas: { 
                scale: 2,
                useCORS: true,
                letterRendering: true,
                logging: false
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait',
                compress: true
            }
        };
        
        // Générer et télécharger le PDF
        html2pdf().set(opt).from(tempDiv).save().then(() => {
            // Nettoyer l'élément temporaire
            document.body.removeChild(tempDiv);
        }).catch((error) => {
            console.error('Erreur lors de la génération du PDF:', error);
            if (document.body.contains(tempDiv)) {
                document.body.removeChild(tempDiv);
            }
            alert('Erreur lors de la génération du PDF. Veuillez réessayer.');
        });
    }, 100); // Petit délai pour laisser le DOM se mettre à jour
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
    
    currentHistory.forEach((msg, index) => {
        const div = document.createElement('div');
        div.style.marginBottom = '12px';
        div.style.padding = '12px';
        div.style.borderRadius = '8px';
        div.style.position = 'relative';
        
        // Supporter les deux formats de données
        const role = msg.role || msg.emetteur || 'user';
        const content = msg.contenu || msg.contenu_message || '';
        
        div.style.backgroundColor = role === 'user' ? '#e7f3ff' : '#fff';
        div.style.border = '1px solid ' + (role === 'user' ? '#0078d7' : '#e0e0e0');
        
        const headerDiv = document.createElement('div');
        headerDiv.style.display = 'flex';
        headerDiv.style.justifyContent = 'space-between';
        headerDiv.style.alignItems = 'center';
        headerDiv.style.marginBottom = '8px';
        
        const userLabel = document.createElement('strong');
        userLabel.textContent = (role === 'user' ? 'Vous' : 'Agent IA') + ' : ';
        userLabel.style.color = role === 'user' ? '#0078d7' : '#333';
        
        headerDiv.appendChild(userLabel);
        
        // Ajouter le bouton de téléchargement uniquement pour les réponses de l'agent
        if (role === 'assistant') {
            const downloadBtn = document.createElement('button');
            downloadBtn.innerHTML = '📥 Télécharger PDF';
            downloadBtn.style.cssText = `
                background: linear-gradient(135deg, #0078d7 0%, #005a9e 100%);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 0.85rem;
                transition: all 0.3s;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            `;
            downloadBtn.onmouseover = function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
            };
            downloadBtn.onmouseout = function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
            };
            downloadBtn.onclick = function() {
                downloadResponseAsPDF(content, index);
            };
            headerDiv.appendChild(downloadBtn);
        }
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'markdown-content';
        
        // Parser le markdown pour les réponses de l'agent
        if (role === 'assistant' && typeof marked !== 'undefined') {
            contentDiv.innerHTML = marked.parse(content);
        } else {
            contentDiv.textContent = content;
        }
        
        div.appendChild(headerDiv);
        div.appendChild(contentDiv);
        chatHistory.appendChild(div);
    });
    
    // Scroll vers le bas
    chatHistory.scrollTop = chatHistory.scrollHeight;
}