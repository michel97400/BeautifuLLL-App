<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/etudiant.php';
require_once __DIR__ . '/../../model/niveau.php';
use Models\Etudiants;
use Models\Niveau;


$matiereChoisie = $_SESSION['agent_ia_matiere'] ?? null;
$user = $_SESSION['user'] ?? null;
$niveauLibelle = null;
if ($user && isset($user['email'])) {
    $etudiantModel = new Etudiants();
    $etudiant = $etudiantModel->readByEmail($user['email']);
    $niveauId = $etudiant['id_niveau'] ?? null;
    
    // Récupérer le libellé du niveau
    if ($niveauId) {
        $niveauModel = new Niveau();
        $niveauData = $niveauModel->readSingle($niveauId);
        $niveauLibelle = $niveauData['libelle_niveau'] ?? null;
    }
}

if ($matiereChoisie) {
    ?>
    <style>
        .chat-container {
            display: flex;
            flex-direction: row;
            gap: 0;
            margin: 0;
            padding: 0;
            height: calc(100vh - 160px);
            min-height: 500px;
            background: transparent;
            overflow: hidden;
        }
        
        .chat-sidebar {
            flex: 0 0 280px;
            width: 280px;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 2px solid #e0e0e0;
            position: relative;
            box-shadow: 2px 0 8px rgba(0,0,0,0.08);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        
        .chat-sidebar.collapsed {
            margin-left: -280px;
            width: 0;
        }
        
        .chat-sidebar::after {
            content: '';
            position: absolute;
            right: -1px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: linear-gradient(to bottom, 
                transparent 0%, 
                rgba(0, 120, 215, 0.3) 20%, 
                rgba(0, 120, 215, 0.5) 50%, 
                rgba(0, 120, 215, 0.3) 80%, 
                transparent 100%);
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #0078d7 0%, #005a9e 100%);
            color: #ffffff;
            padding: 20px 16px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 10;
            min-height: 60px;
        }
        
        .session-list {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }
        
        .session-item {
            padding: 15px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .session-item:hover {
            background: #e7f3ff;
            border-left-color: #0078d7;
        }
        
        .session-item.active {
            background: #e7f3ff;
            border-left-color: #0078d7;
            font-weight: 600;
        }
        
        .session-date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .session-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .session-messages {
            color: #0078d7;
            font-size: 0.85rem;
        }
        
        .session-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .session-delete:hover {
            background: #c82333;
        }
        
        .new-chat-btn {
            margin: 12px;
            padding: 12px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
        }
        
        .new-chat-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1ba87d 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            overflow: hidden;
            

            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        .toggle-sidebar-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .toggle-sidebar-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        .toggle-sidebar-btn-floating {
            position: fixed;
            top: 100px;
            left: 0;
            background: #0078d7;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .toggle-sidebar-btn-floating:hover {
            background: #005a9e;
            transform: translateX(5px);
        }
        
        .chat-sidebar.collapsed ~ * .toggle-sidebar-btn-floating {
            display: flex;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #0078d7 0%, #005a9e 100%);
            color: #ffffff;
            padding: 20px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            min-height: 60px;
        }
        
        .chat-body {
            flex: 1;
            padding: 24px 20px;
            overflow-y: auto;
            background: #fbfdfdff;
        }
        
        .chat-history {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .chat-footer {
            padding: 20px 20px 24px 20px;
            border:2px solid #0f0f0fff;
            background: #005a9e;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        /* Styles pour le contenu markdown */
        .markdown-content {
            line-height: 1.6;
        }
        
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            color: #0078d7;
            margin-top: 16px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .markdown-content h1 { font-size: 1.8em; }
        .markdown-content h2 { font-size: 1.5em; }
        .markdown-content h3 { font-size: 1.3em; }
        
        .markdown-content p {
            margin: 10px 0;
        }
        
        .markdown-content code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .markdown-content pre {
            background-color: #f4f4f4;
            padding: 12px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 12px 0;
        }
        
        .markdown-content pre code {
            background: none;
            padding: 0;
        }
        
        .markdown-content ul,
        .markdown-content ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        
        .markdown-content li {
            margin: 5px 0;
        }
        
        .markdown-content blockquote {
            border-left: 4px solid #0078d7;
            padding-left: 15px;
            margin: 15px 0;
            color: #666;
            font-style: italic;
        }
        
        .markdown-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 15px 0;
        }
        
        .markdown-content table th,
        .markdown-content table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .markdown-content table th {
            background-color: #0078d7;
            color: white;
            font-weight: 600;
        }
        
        .markdown-content table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .markdown-content a {
            color: #0078d7;
            text-decoration: none;
        }
        
        .markdown-content a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: auto;
            }
            
            .chat-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid #e0e0e0;
                max-height: 300px;
            }
            
            .chat-sidebar::after {
                display: none;
            }
            
            .chat-main {
                min-height: 500px;
            }
        }
    </style>
    
    <!-- Bouton flottant pour rouvrir la sidebar -->
    <button class="toggle-sidebar-btn-floating" id="floatingToggle" onclick="toggleSidebar()" title="Afficher l'historique">
        ▶
    </button>
    
    <div class="chat-container">
        <!-- Sidebar avec historique -->
        <div class="chat-sidebar" id="chatSidebar">
            <div class="sidebar-header">
                <span>📜 Historique</span>
                <button class="toggle-sidebar-btn" id="sidebarToggle" onclick="toggleSidebar()" title="Masquer l'historique">
                    ◀
                </button>
            </div>
            <button class="new-chat-btn" onclick="startNewChat()">
                ✨ Nouvelle conversation
            </button>
            <div class="session-list" id="session-list">
                <div class="empty-state">
                    <p>Chargement...</p>
                </div>
            </div>
        </div>
        
        <!-- Zone de chat principale -->
        <div class="chat-main">
            <div class="chat-header">
                <div>
                    <div style="margin:0; font-size: 1.1rem; font-weight: 600; color: #ffffff;">🤖 Prof IA - <?= htmlspecialchars($matiereChoisie) ?></div>
                    <div style="margin-top:6px; font-size:0.9rem; color: #e7f3ff;">
                        Matière : <strong><?= htmlspecialchars($matiereChoisie) ?></strong>
                        <?php if ($niveauLibelle): ?>
                            | Niveau : <strong><?= htmlspecialchars($niveauLibelle) ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="post" style="margin-left:auto;">
                    <input type="hidden" name="reset_matiere" value="1">
                    <button type="submit" class="btn btn-secondary" style="margin-left:12px; padding:8px 16px; font-size:0.9rem; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #ffffff; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">Changer de matière</button>
                </form>
            </div>
            
            <div class="chat-body">
                <div id="chat-history" class="chat-history"></div>
                <div id="chat-error" style="color: #dc3545; margin-top: 8px;"></div>
            </div>
            
            <div class="chat-footer">
                <textarea id="message" name="message" rows="2" placeholder="Écrivez votre message..." 
                    style="flex:1; border-radius: 8px; border: 1px solid #e0e0e0; font-size: 1rem; background: #fafbfc; padding: 10px 12px; resize: none;"></textarea>
                <button type="submit" class="btn btn-primary" onclick="sendMessage()" 
                    style="padding: 12px 24px; font-size: 1.1rem; border-radius: 8px;">Envoyer</button>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="php-crud/public/chat.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('chatSidebar');
            const floatingBtn = document.getElementById('floatingToggle');
            
            if (sidebar.classList.contains('collapsed')) {
                // Ouvrir la sidebar
                sidebar.classList.remove('collapsed');
                floatingBtn.style.display = 'none';
            } else {
                // Fermer la sidebar
                sidebar.classList.add('collapsed');
                floatingBtn.style.display = 'flex';
            }
        }
    </script>
    
    <?php
    // Traitement du POST pour reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_matiere'])) {
        unset($_SESSION['agent_ia_matiere']);
        unset($_SESSION['current_session_id']);
        unset($_SESSION['chat_messages']);
        echo '<script>window.location.href = "index.php?action=agent-ia";</script>';
        exit;
    }
}
?>