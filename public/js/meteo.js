// Attendre que le DOM soit entièrement chargé avant d'exécuter le code
document.addEventListener('DOMContentLoaded', function() {
    // ========== GESTION DES MESSAGES FLASH ==========
    // Sélectionne tous les éléments avec la classe 'auto-dismiss' (messages flash)
    const alerts = document.querySelectorAll('.auto-dismiss');
    
    // Pour chaque message flash trouvé
    alerts.forEach(alert => {
        // Définir un délai avant la suppression du message
        setTimeout(() => {
            alert.remove(); // Supprime l'élément du DOM après le délai
        }, 5000); // Délai de 5000ms (5 secondes)
    });

    // ========== GESTION DE LA COULEUR DES TEMPÉRATURES ==========
    // Sélectionne tous les éléments avec la classe 'colorTemp' (affichage des températures)
    const colorTemp = document.querySelectorAll('.colorTemp');
    
    // Pour chaque élément de température trouvé
    colorTemp.forEach(temp => {
        // Convertit le texte de l'élément en nombre décimal
        const tempValue = parseFloat(temp.textContent);
        
        // Applique une classe CSS en fonction de la valeur de la température
        if (tempValue < 15) {
            // Si température inférieure à 15°C -> couleur bleue (froid)
            temp.classList.add('temp-cold');
        } else if (tempValue < 25) {
            // Si température entre 15°C et 24.99°C -> couleur verte (doux)
            temp.classList.add('temp-warm');
        } else {
            // Si température 25°C ou plus -> couleur rouge (chaud)
            temp.classList.add('temp-hot');
        }
    });
});
