document.addEventListener('DOMContentLoaded', function() {
    // Gestion des messages flash
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.remove();
        }, 5000);
    });

    // Gestion de la couleur de la température
    
    const colorTemp = document.querySelectorAll('.colorTemp');
    
        colorTemp.forEach(temp => {
            const tempValue = parseFloat(temp.textContent);
            if (tempValue < 15) {
                temp.classList.add('temp-cold');
            } else if (tempValue < 25) {
                temp.classList.add('temp-warm');
            } else {
                temp.classList.add('temp-hot');
            }
        });
});
