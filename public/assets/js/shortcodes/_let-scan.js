document.addEventListener("DOMContentLoaded", function () {
    const scanBtn = document.querySelector("#scan-btn");
    const select = document.querySelector("#scanner-device");
    const previewImg = document.querySelector("#scanner-preview");

    scanBtn.addEventListener("click", function () {
        const selectedDevice = select.value;
        let selectedDeviceClean = JSON.parse(selectedDevice);

        if (!selectedDevice) {
            alert("Veuillez sélectionner un périphérique.");
            return;
        }

        scanBtn.disabled = true;
        scanBtn.textContent = "Scan en cours...";

        fetch("/scanner/scan-request", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ device: selectedDeviceClean })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                previewImg.innerHTML = `<img src="${data.imageUrl}"/>`;
            } else {
                alert("Erreur: " + data.message);
            }
        })
        .catch(err => {
            console.error("Erreur :", err);
            alert("Erreur de communication.");
        })
        .finally(() => {
            scanBtn.disabled = false;
            scanBtn.textContent = "Scanner";
        });
    });
});