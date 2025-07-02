document.addEventListener('DOMContentLoaded', async () => {
    const select = document.getElementById('scanner-device');
    select.innerHTML = '<option disabled selected>Chargement...</option>';

    try {
        const res = await fetch('/scanner/devices');
        const data = await res.json();

        if (data.devices && data.devices.length > 0) {
            select.innerHTML = '';
            data.devices.forEach(device => {
                const option = document.createElement('option');
                option.value = JSON.stringify({ name: device.deviceName, driver: device.driver });
                option.textContent = `${device.deviceName} (${device.driver.toUpperCase()})`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option disabled selected>Aucun périphérique trouvé</option>';
        }
    } catch (err) {
        console.error(err);
        select.innerHTML = '<option disabled selected>Erreur de chargement</option>';
    }
});
