let destId = "";
let destinataires = document.querySelectorAll(".correspondant");
destinataires.forEach((dest) => {
  dest.addEventListener("click", function() {
    destId = this.getAttribute("data-id");
  });
});
document.querySelector("#sendBtn").addEventListener("click", function() {
  let contenu = document.querySelector("#messageInput").value;
  let destinataireId = destId; // ID du correspondant sélectionné

  if (!contenu.trim()) return;

  fetch("/chat/send", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      destinataire_id: destinataireId,
      contenu: contenu,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        let messagesList = document.getElementById("messages-list");
        let msgBox = `<div id="msg">
                        <div id="msg-sent" style="display:flex">
                            <div id="details" style="max-width:calc(100% - 210px);margin-left:auto">
                                <p class="text-bg-dark" style="padding:8px 16px;word-wrap:break-word;border-radius:18px 18px 0 18px;">${contenu}</p>
                            </div>
                        </div>
                      </div>`;
        console.log(data.message);
        messagesList.innerHTML += `${msgBox}`;
        document.querySelector("#messageInput").value = ""; // Vider le champ après envoi

        messagesList.scrollTop = messagesList.scrollHeight;
      }
    })
    .catch((error) =>
      console.error("Erreur lors de l'envoi du message :", error)
    );
});

document.querySelector("#messageInput").addEventListener("keypress", function(e) {
    if(e.key === "Enter") {
        document.querySelector("#sendBtn").click();
    }
});
