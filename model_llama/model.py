from gpt4all import GPT4All
import os

def main():
    """Interfaces de chats simple et directe"""
    print("🤖 LLAMA 3.2 1B INSTRUCT - CHAT DIRECT")
    print("="*50)
    
    # Chemin vers le modèle
    model_path = os.path.join(os.getcwd(), "Llama-3.2-1B-Instruct-Q8_0.gguf")
    
    # Vérification du fichier modèle
    if not os.path.exists(model_path):
        print("❌ Modèle GGUF introuvable !")
        print("Téléchargez-le manuellement depuis :")
        print("  https://huggingface.co/lmstudio-community/Llama-3.2-1B-Instruct-GGUF/tree/main")
        print("et placez le fichier 'Llama-3.2-1B-Instruct-Q8_0.gguf' dans ce dossier.")
        return
    
    # Chargement du modèle
    print("🚀 Chargement du modèle...")
    try:
        model = GPT4All(model_path, allow_download=False, device='cpu')
        print("✅ Modèle chargé avec succès !")
    except Exception as e:
        print(f"❌ Erreur lors du chargement : {e}")
        return
    
    # Instructions
    print("\n" + "="*50)
    print("💬 CHAT DIRECT AVEC LLAMA")
    print("="*50)
    print("• Tapez votre message et appuyez sur Entrée")
    print("• Tapez 'quit' ou 'exit' pour quitter")
    print("• Tapez 'clear' pour vider l'historique")
    print("-"*50)
    
    conversation_history = []
    
    while True:
        try:
            # Demande de saisie
            user_input = input("\n🧑 Vous: ").strip()
            
            # Commandes spéciales
            if user_input.lower() in ['quit', 'exit']:
                print("👋 Au revoir !")
                break
            
            if user_input.lower() == 'clear':
                conversation_history = []
                print("🧹 Historique de conversation vidé !")
                continue
            
            if not user_input:
                continue
            
            # Construction du prompt simplifié et plus robuste
            if conversation_history:
                # Format simple mais efficace avec historique
                context_parts = []
                for entry in conversation_history[-2:]:  # Garde les 2 derniers échanges
                    context_parts.append(f"Utilisateur: {entry['user']}")
                    context_parts.append(f"Assistant: {entry['assistant']}")
                
                context_parts.append(f"Utilisateur: {user_input}")
                context_parts.append("Assistant:")
                prompt = "\n".join(context_parts)
            else:
                # Premier message - format simple
                prompt = f"Utilisateur: {user_input}\nAssistant:"
            
            print("\n🤖 Assistant: ", end="", flush=True)
            
            # Génération de la réponse avec paramètres optimisés
            response = model.generate(
                prompt,
                max_tokens=400,
                temp=0.7,  # Plus conservateur
                top_k=30,  
                top_p=0.85,
                repeat_penalty=1.15
            )
            
            # Nettoie la réponse de façon simple et efficace
            # Trouve le texte après "Assistant:" 
            if "Assistant:" in response:
                response = response.split("Assistant:")[-1].strip()
            
            # Supprime le texte après "Utilisateur:" s'il apparaît
            if "Utilisateur:" in response:
                response = response.split("Utilisateur:")[0].strip()
            
            # Supprime toutes les balises HTML/XML restantes
            import re
            response = re.sub(r'<[^>]+>', '', response)
            
            # Nettoie les espaces et formatage
            response = re.sub(r'\s+', ' ', response).strip()
            
            # Si la réponse est vide ou trop courte, utilise une réponse par défaut
            if len(response.strip()) < 3:
                response = "Je ne comprends pas bien votre question. Pouvez-vous la reformuler ?"
            
            print(response)
            
            # Sauvegarde dans l'historique
            conversation_history.append({
                'user': user_input,
                'assistant': response
            })
            
        except KeyboardInterrupt:
            print("\n\n👋 Au revoir !")
            break
        except Exception as e:
            print(f"\n❌ Erreur : {e}")
            print("Essayez de reformuler votre question.")

if __name__ == "__main__":
    main()