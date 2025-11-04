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
            gap: 20px;
            max-width: 1400px;
            margin: 40px auto;
            height: calc(100vh - 200px);
        }
        
        .chat-sidebar {
            width: 320px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar-header {
            background: #0078d7;
            color: #fff;
            padding: 20px;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .session-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
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
            margin: 10px;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .new-chat-btn:hover {
            background: #218838;
        }
        
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        
        .chat-header {
            background: #0078d7;
            color: #fff;
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-body {
            flex: 1;
            padding: 24px 32px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        
        .chat-history {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .chat-footer {
            padding: 20px 32px 24px 32px;
            background: #fff;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
    </style>
    
    <div class="chat-container">
        <!-- Sidebar avec historique -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <span>Historique</span>
                <button class="new-chat-btn" onclick="startNewChat()" style="margin: 0; padding: 6px 12px; font-size: 0.9rem;">
                    + Nouveau
                </button>
            </div>
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
                    <h2 style="margin:0; font-size: 1.7rem; font-weight: 600; letter-spacing: 1px;">Agent IA - Chat</h2>
                    <div style="margin-top:8px; font-size:1rem; color:#e7f3ff;">
                        Matière : <strong><?= htmlspecialchars($matiereChoisie) ?></strong>
                        <?php if ($niveauLibelle): ?>
                            | Niveau : <strong><?= htmlspecialchars($niveauLibelle) ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="post" style="margin-left:auto;">
                    <input type="hidden" name="reset_matiere" value="1">
                    <button type="submit" class="btn btn-secondary" style="margin-left:12px;">Changer de matière</button>
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
    <script src="php-crud/public/chat.js"></script>
    
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