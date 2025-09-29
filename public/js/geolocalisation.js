if ("geolocation" in navigator) {
    const params = new URLSearchParams(window.location.search);
  
    // Si l’utilisateur n’a PAS fait une recherche par ville
    if (!params.has("ville")) {
      // Et si on n’a pas encore les coordonnées
      if (!params.has("lat") || !params.has("lon")) {
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            const { latitude, longitude } = pos.coords;
            window.location.href = `/?lat=${latitude}&lon=${longitude}`;
          },
          (err) => {
            console.warn("Géolocalisation refusée ou erreur :", err);
            // Redirige par défaut sur Nancy si refus
            if (!params.has("ville")) {
              window.location.href = "/?ville=Nancy";
            }
          }
        );
      }
    }
  }