<?php
/**
 * Script de migration pour la table message
 * Ce script renomme les colonnes de la table message pour correspondre au schéma défini dans README.md
 */

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

try {
    $database = new Database();
    $conn = $database->connect();

    echo "Connexion à la base de données réussie.\n\n";

    // Vérifier la structure actuelle de la table message
    echo "=== Structure actuelle de la table message ===\n";
    $stmt = $conn->query("DESCRIBE message");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $columnNames = array_column($columns, 'Field');

    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }

    echo "\n=== Début de la migration ===\n";

    $migrations = [];

    // Migration 1: role ou role_message → emetteur
    if (in_array('role', $columnNames) && !in_array('emetteur', $columnNames)) {
        // D'abord mettre à jour les valeurs 'assistant' en 'agent'
        $migrations[] = "UPDATE message SET role = 'agent' WHERE role = 'assistant'";
        // Puis renommer la colonne et changer le type
        $migrations[] = "ALTER TABLE message CHANGE COLUMN role emetteur VARCHAR(50) NOT NULL";
        echo "✓ Migration planifiée: role → emetteur (avec conversion assistant → agent)\n";
    } elseif (in_array('role_message', $columnNames) && !in_array('emetteur', $columnNames)) {
        $migrations[] = "ALTER TABLE message CHANGE COLUMN role_message emetteur VARCHAR(50) NOT NULL";
        echo "✓ Migration planifiée: role_message → emetteur\n";
    } elseif (in_array('emetteur', $columnNames)) {
        echo "✓ La colonne 'emetteur' existe déjà\n";
    }

    // Migration 2: contenu → contenu_message
    if (in_array('contenu', $columnNames) && !in_array('contenu_message', $columnNames)) {
        $migrations[] = "ALTER TABLE message CHANGE COLUMN contenu contenu_message TEXT NOT NULL";
        echo "✓ Migration planifiée: contenu → contenu_message (+ augmentation à TEXT)\n";
    } elseif (in_array('contenu_message', $columnNames)) {
        echo "✓ La colonne 'contenu_message' existe déjà\n";
        // Vérifier si on doit augmenter la taille
        foreach ($columns as $column) {
            if ($column['Field'] === 'contenu_message' && strpos($column['Type'], 'varchar') !== false) {
                $migrations[] = "ALTER TABLE message MODIFY COLUMN contenu_message TEXT NOT NULL";
                echo "✓ Migration planifiée: Augmentation de contenu_message à TEXT\n";
            }
        }
    }

    // Migration 3: date_envoi → date_heure_message
    if (in_array('date_envoi', $columnNames) && !in_array('date_heure_message', $columnNames)) {
        $migrations[] = "ALTER TABLE message CHANGE COLUMN date_envoi date_heure_message DATETIME NOT NULL";
        echo "✓ Migration planifiée: date_envoi → date_heure_message\n";
    } elseif (in_array('date_heure_message', $columnNames)) {
        echo "✓ La colonne 'date_heure_message' existe déjà\n";
    }

    if (empty($migrations)) {
        echo "\n✓ Aucune migration nécessaire. La table est déjà à jour!\n";
    } else {
        echo "\n=== Exécution des migrations ===\n";
        foreach ($migrations as $sql) {
            try {
                $conn->exec($sql);
                echo "✓ Exécuté: $sql\n";
            } catch (PDOException $e) {
                echo "✗ Erreur: {$e->getMessage()}\n";
                echo "  SQL: $sql\n";
            }
        }
        echo "\n✓ Migration terminée!\n";
    }

    // Afficher la structure finale
    echo "\n=== Structure finale de la table message ===\n";
    $stmt = $conn->query("DESCRIBE message");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }

} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}
