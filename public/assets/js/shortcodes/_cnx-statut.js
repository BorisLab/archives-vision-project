    // Indiquer que l'utilisateur est en ligne toutes les 30 secondes
    function updateOnlineStatus() {
        fetch('/status/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'online' })
        });
    }
    
document.addEventListener("DOMContentLoaded", function () {
    // Premier envoi au chargement de la page
    updateOnlineStatus();

    // Mise à jour toutes les 30 secondes tant que l'utilisateur est actif
    setInterval(updateOnlineStatus, 120000);
});

let offlineTimeout;

window.addEventListener("visibilitychange", function () {
    if (document.visibilityState === "hidden") {
        // Planifier la mise hors ligne après 1 minute si l'utilisateur ne revient pas
        offlineTimeout = setTimeout(() => {
            navigator.sendBeacon('/user/status/update', JSON.stringify({ status: 'offline' }));
        }, 60000);
    } else {
        // Annuler la mise hors ligne si l'utilisateur revient
        clearTimeout(offlineTimeout);
        updateOnlineStatus();
    }
});
