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
 * Remplace les emojis et caractères spéciaux par des équivalents texte
 */
function sanitizeText(text) {
    const replacements = {
        '🤖': '[IA]',
        '📚': '[LIVRE]',
        '💡': '[IDEE]',
        '⚠️': '[ATTENTION]',
        '✅': '[OK]',
        '❌': '[NON]',
        '🔍': '[RECHERCHE]',
        '📝': '[NOTE]',
        '🎯': '[CIBLE]',
        '⭐': '[ETOILE]',
        '🌟': '[ETOILE]',
        '💻': '[ORDINATEUR]',
        '📊': '[GRAPHIQUE]',
        '🔬': '[SCIENCE]',
        '🧪': '[CHIMIE]',
        '🧬': '[ADN]',
        '🌊': '[EAU]',
        '💧': '[GOUTTE]',
        '🔥': '[FEU]',
        '⚡': '[ECLAIR]',
        '🌡️': '[TEMPERATURE]',
        '📐': '[GEOMETRIE]',
        '➡️': '->',
        '⬅️': '<-',
        '⬆️': '^',
        '⬇️': 'v',
        '✓': 'V',
        '×': 'x',
        '÷': '/',
        '±': '+/-',
        '≈': '~',
        '≠': '!=',
        '≤': '<=',
        '≥': '>=',
        '°': ' degres',
        '€': 'EUR',
        '£': 'GBP',
        '¥': 'YEN',
        '₹': 'INR',
        // Nettoyer les caractères Unicode problématiques
        '\u2028': ' ',
        '\u2029': ' ',
        '\u2013': '-',
        '\u2014': '-',
        '\u2018': "'",
        '\u2019': "'",
        '\u201C': '"',
        '\u201D': '"',
        '\u2026': '...',
        '™': '(TM)',
        '®': '(R)',
        '©': '(C)',
        '1️⃣': '1.',
        '2️⃣': '2.',
        '3️⃣': '3.',
        '4️⃣': '4.',
        '5️⃣': '5.',
        '6️⃣': '6.',
        '7️⃣': '7.',
        '8️⃣': '8.',
        '9️⃣': '9.',
        '🔟': '10.'
    };
    
    let result = text;
    for (const [emoji, replacement] of Object.entries(replacements)) {
        result = result.replace(new RegExp(emoji, 'g'), replacement);
    }
    
    // Remplacer les autres emojis restants par un espace
    result = result.replace(/[\u{1F300}-\u{1F9FF}]/gu, ' ');
    
    return result;
}

/**
 * Détecte et parse un tableau markdown
 */
function parseMarkdownTable(lines, startIndex) {
    const tableLines = [];
    let i = startIndex;
    
    // Collecter toutes les lignes du tableau
    while (i < lines.length && lines[i].includes('|')) {
        tableLines.push(lines[i]);
        i++;
    }
    
    if (tableLines.length < 2) return null;
    
    // Parser le tableau
    const headers = tableLines[0].split('|').map(h => h.trim()).filter(h => h);
    const rows = [];
    
    for (let j = 2; j < tableLines.length; j++) {
        const cells = tableLines[j].split('|').map(c => c.trim()).filter(c => c);
        if (cells.length > 0) {
            rows.push(cells);
        }
    }
    
    // Créer la structure pdfmake
    const tableBody = [
        headers.map(h => ({ text: sanitizeText(h), style: 'tableHeader', fillColor: '#0078d7', color: '#ffffff' }))
    ];
    
    rows.forEach(row => {
        tableBody.push(row.map(cell => ({ text: sanitizeText(cell), style: 'tableCell' })));
    });
    
    return {
        table: {
            headerRows: 1,
            widths: Array(headers.length).fill('*'),
            body: tableBody
        },
        layout: {
            fillColor: function (rowIndex) {
                return (rowIndex % 2 === 0) ? '#f8f9fa' : null;
            },
            hLineWidth: function () { return 0.5; },
            vLineWidth: function () { return 0.5; },
            hLineColor: function () { return '#dddddd'; },
            vLineColor: function () { return '#dddddd'; }
        },
        margin: [0, 10, 0, 10],
        endIndex: i
    };
}

/**
 * Convertit le contenu markdown en structure pdfmake
 */
function markdownToPdfContent(markdown) {
    const content = [];
    
    // NE PAS nettoyer les emojis ici, les garder pour le PDF
    const lines = markdown.split('\n');
    let inCodeBlock = false;
    let codeBlockContent = [];
    let inList = false;
    let listItems = [];
    let i = 0;
    
    while (i < lines.length) {
        const line = lines[i];
        
        // Bloc de code
        if (line.startsWith('```')) {
            if (inCodeBlock) {
                // Fin du bloc de code
                content.push({
                    text: codeBlockContent.join('\n'),
                    style: 'code',
                    margin: [10, 5, 10, 10],
                    background: '#f4f4f4'
                });
                codeBlockContent = [];
                inCodeBlock = false;
            } else {
                // Début du bloc de code
                if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
                inCodeBlock = true;
            }
            i++;
            continue;
        }
        
        if (inCodeBlock) {
            codeBlockContent.push(line);
            i++;
            continue;
        }
        
        // Détecter un tableau
        if (line.includes('|') && !line.startsWith('|') && i + 1 < lines.length && lines[i + 1].includes('|')) {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            
            const tableResult = parseMarkdownTable(lines, i);
            if (tableResult) {
                content.push({
                    table: tableResult.table,
                    layout: tableResult.layout,
                    margin: tableResult.margin
                });
                i = tableResult.endIndex;
                continue;
            }
        }
        
        // Séparateur horizontal
        if (line.trim() === '---' || line.trim() === '___') {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            content.push({
                canvas: [
                    {
                        type: 'line',
                        x1: 0, y1: 0,
                        x2: 515, y2: 0,
                        lineWidth: 1,
                        lineColor: '#cccccc'
                    }
                ],
                margin: [0, 10, 0, 10]
            });
            i++;
            continue;
        }
        
        // Citation (blockquote)
        if (line.startsWith('> ')) {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            const text = line.replace(/^>\s*/, '');
            content.push({
                text: text,
                style: 'quote',
                margin: [20, 3, 0, 3]
            });
            i++;
            continue;
        }
        
        // Titres
        if (line.startsWith('# ')) {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            content.push({ text: line.replace(/^# /, ''), style: 'header1', margin: [0, 15, 0, 5] });
            i++;
            continue;
        } else if (line.startsWith('## ')) {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            content.push({ text: line.replace(/^## /, ''), style: 'header2', margin: [0, 12, 0, 5] });
            i++;
            continue;
        } else if (line.startsWith('### ')) {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            content.push({ text: line.replace(/^### /, ''), style: 'header3', margin: [0, 10, 0, 5] });
            i++;
            continue;
        } 
        // Listes
        else if (line.match(/^[\*\-\+]\s+/)) {
            inList = true;
            listItems.push(line.replace(/^[\*\-\+]\s+/, ''));
            i++;
            continue;
        }
        // Ligne vide
        else if (line.trim() === '') {
            if (inList) {
                content.push({ ul: listItems, margin: [0, 5, 0, 10] });
                listItems = [];
                inList = false;
            }
            i++;
            continue;
        }
        // Texte normal
        else if (line.trim()) {
            if (inList) { content.push({ ul: listItems }); listItems = []; inList = false; }
            
            // Gérer le formatage inline
            const textParts = [];
            let currentText = line;
            
            // Parser le texte avec gras/italique
            const boldRegex = /\*\*(.+?)\*\*/g;
            const italicRegex = /\*(.+?)\*/g;
            const codeRegex = /`(.+?)`/g;
            
            // Simplification : juste garder le texte formaté
            currentText = currentText
                .replace(boldRegex, '$1')
                .replace(italicRegex, '$1')
                .replace(codeRegex, '$1');
            
            content.push({ text: currentText, margin: [0, 0, 0, 5] });
        }
        
        i++;
    }
    
    // Ajouter la dernière liste si nécessaire
    if (inList && listItems.length > 0) {
        content.push({ ul: listItems, margin: [0, 5, 0, 10] });
    }
    
    return content;
}

/**
 * Télécharge une réponse en PDF
 */
async function downloadResponseAsPDF(content, index) {
    try {
        // Créer un conteneur temporaire avec le contenu formaté
        const container = document.createElement('div');
        container.style.cssText = `
            position: fixed;
            left: -9999px;
            top: 0;
            width: 800px;
            background: white;
            padding: 40px;
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        `;
        
        // En-tête
        const header = document.createElement('div');
        header.style.cssText = `
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0078d7;
        `;
        header.innerHTML = `
            <h1 style="color: #0078d7; margin: 10px 0; font-size: 28px;">🤖 Réponse de l'Agent IA</h1>
            <p style="color: #666; font-size: 14px; margin: 10px 0;"><em>Généré le ${new Date().toLocaleString('fr-FR')}</em></p>
        `;
        
        // Contenu markdown converti en HTML
        const contentDiv = document.createElement('div');
        contentDiv.className = 'markdown-content';
        contentDiv.innerHTML = marked.parse(content);
        contentDiv.style.cssText = `
            font-size: 14px;
            line-height: 1.8;
        `;
        
        // Appliquer les styles CSS aux éléments
        contentDiv.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => {
            h.style.color = '#0078d7';
            h.style.marginTop = '20px';
            h.style.marginBottom = '10px';
            h.style.fontWeight = '600';
        });
        
        contentDiv.querySelectorAll('code').forEach(code => {
            if (!code.parentElement || code.parentElement.tagName !== 'PRE') {
                code.style.backgroundColor = '#f4f4f4';
                code.style.padding = '3px 6px';
                code.style.borderRadius = '3px';
                code.style.fontFamily = 'Courier New, monospace';
                code.style.fontSize = '13px';
            }
        });
        
        contentDiv.querySelectorAll('pre').forEach(pre => {
            pre.style.backgroundColor = '#f4f4f4';
            pre.style.padding = '15px';
            pre.style.borderRadius = '5px';
            pre.style.margin = '15px 0';
            pre.style.overflowX = 'auto';
            pre.style.whiteSpace = 'pre-wrap';
            pre.style.wordWrap = 'break-word';
            const code = pre.querySelector('code');
            if (code) {
                code.style.backgroundColor = 'transparent';
                code.style.padding = '0';
            }
        });
        
        contentDiv.querySelectorAll('table').forEach(table => {
            table.style.borderCollapse = 'collapse';
            table.style.width = '100%';
            table.style.margin = '20px 0';
            table.style.fontSize = '13px';
            
            table.querySelectorAll('th').forEach(th => {
                th.style.backgroundColor = '#0078d7';
                th.style.color = 'white';
                th.style.padding = '12px 10px';
                th.style.border = '1px solid #ddd';
                th.style.textAlign = 'left';
                th.style.fontWeight = '600';
            });
            
            table.querySelectorAll('td').forEach(td => {
                td.style.padding = '10px';
                td.style.border = '1px solid #ddd';
            });
            
            table.querySelectorAll('tr:nth-child(even)').forEach(tr => {
                if (!tr.querySelector('th')) {
                    tr.style.backgroundColor = '#f8f9fa';
                }
            });
        });
        
        contentDiv.querySelectorAll('blockquote').forEach(bq => {
            bq.style.borderLeft = '4px solid #0078d7';
            bq.style.paddingLeft = '15px';
            bq.style.color = '#555';
            bq.style.margin = '15px 0';
            bq.style.fontStyle = 'italic';
            bq.style.backgroundColor = '#f8f9fa';
            bq.style.padding = '10px 15px';
        });
        
        contentDiv.querySelectorAll('ul, ol').forEach(list => {
            list.style.margin = '10px 0';
            list.style.paddingLeft = '30px';
        });
        
        contentDiv.querySelectorAll('li').forEach(li => {
            li.style.margin = '8px 0';
        });
        
        contentDiv.querySelectorAll('p').forEach(p => {
            p.style.margin = '10px 0';
        });
        
        // Pied de page
        const footer = document.createElement('div');
        footer.style.cssText = `
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        `;
        footer.innerHTML = '<p>Document généré par BeautifuLLL-App</p>';
        
        // Assembler le contenu
        container.appendChild(header);
        container.appendChild(contentDiv);
        container.appendChild(footer);
        document.body.appendChild(container);
        
        // Capturer avec html2canvas
        const canvas = await html2canvas(container, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        });
        
        // Créer le PDF
        const { jsPDF } = window.jspdf;
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 210; // A4 width in mm
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        
        const pdf = new jsPDF({
            orientation: imgHeight > 297 ? 'portrait' : 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        
        let heightLeft = imgHeight;
        let position = 0;
        
        // Ajouter l'image au PDF (avec gestion des pages multiples)
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= 297;
        
        while (heightLeft > 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= 297;
        }
        
        // Télécharger
        pdf.save(`Agent-IA-Reponse-${new Date().getTime()}.pdf`);
        
        // Nettoyer
        document.body.removeChild(container);
        
    } catch (error) {
        console.error('Erreur lors de la génération du PDF:', error);
        alert('Erreur lors de la génération du PDF: ' + error.message);
    }
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