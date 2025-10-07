document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);

  // Fonction pour rediriger avec coords ou fallback
  function redirect(lat, lon) {
    if (lat && lon) {
      window.location.href = `/meteo/?lat=${lat}&lon=${lon}`;
    } else if (!params.has("ville") && !params.has("lat") && !params.has("lon")) {
      // fallback Nancy
      window.location.href = "/?ville=Nancy";
    }
  }

  // 1️⃣ Géolocalisation automatique au chargement
  if ("geolocation" in navigator && !params.has("ville") && (!params.has("lat") || !params.has("lon"))) {
    navigator.geolocation.getCurrentPosition(
      pos => redirect(pos.coords.latitude, pos.coords.longitude),
      err => {
        console.warn("Géolocalisation refusée ou erreur :", err.message, err.code);
        redirect(); // fallback Nancy
      }
    );
  }

  // 2️⃣ Géolocalisation au clic sur l’icône
  const geoBtn = document.querySelector(".geo-icon");
  if (geoBtn) {
    geoBtn.addEventListener("click", () => {
      if (!("geolocation" in navigator)) {
        alert("Géolocalisation non supportée par votre navigateur.");
        return;
      }

      navigator.geolocation.getCurrentPosition(
        pos => redirect(pos.coords.latitude, pos.coords.longitude),
        err => {
          console.warn("Géolocalisation refusée ou erreur :", err.message, err.code);
          redirect(); // fallback Nancy
        }
      );
    });
  }
});
