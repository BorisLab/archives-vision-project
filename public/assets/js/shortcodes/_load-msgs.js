document.addEventListener("DOMContentLoaded", function() {
  const infoMsg = document.querySelector("#no-title");
  let listOfMsgs = document.querySelector("#msgList");
  let msgInput = document.querySelector("#message-input");
  let abortController = new AbortController();
  let recipFN = document.querySelector("#recipFullN");
  let recipStatut = document.querySelector("#recipStatut");
  let recipPP = document.querySelector(".rcp-av img");
  let contenuContainer = document.querySelector("#messageInput");
  let correspondantSelect = HTMLElement;
  let correspondantSelectId = "";


  infoMsg.style.display = "block";
  listOfMsgs.style.display = "none";
  msgInput.style.display = "none";
  let correspondants = document.querySelectorAll(".correspondant");
  correspondants.forEach((correspondant) => {
    correspondant.addEventListener("click", function() {
      let correspondantId = this.getAttribute("data-id");
      let correspondantPP = correspondant.querySelector(".user-av img");
      recipPP.src = correspondantPP.src;
      correspondantSelect = correspondant;
      correspondantSelectId = correspondantId;
      document
        .querySelectorAll(".correspondant")
        .forEach((el) => (el.parentElement.style.backgroundColor = "transparent"));
      this.parentElement.style.backgroundColor = "#EBF5FB";
      recipFN.innerHTML = correspondant.querySelector("#userFullN").textContent;
      correspondant.querySelector("#userStatutValue").textContent === 'Connecté'
        ? (recipStatut.innerText = "en ligne") ?? correspondant.querySelector("#userStatutValue").textContent === 'Déconnecté'
        : (recipStatut.innerText = "hors-ligne");
      chargerConversation(correspondantId);
      contenuContainer.value = "";
    });
  });

  function chargerConversation(correspondantId) {
    infoMsg.style.display = "none";
    listOfMsgs.style.display = "block";
    msgInput.style.display = "block";
    abortController.abort();
    abortController = new AbortController();
    fetch(`/chat/messages/${correspondantId}`, {
      method: "GET",
      cache: "no-store",
      signal: abortController.signal,
    })
      .then((response) => response.json())
      .then((data) => {
        let messagesList = document.getElementById("messages-list");
        messagesList.innerHTML = ""; // Vider l'ancien contenu

        let userNId = data.utilisateur_id;

        data.messages.forEach((msg) => {
          let msgBox =
            msg.envoyeur_id === userNId
              ? `<div id="msg">
                    <div id="msg-sent" style="display:flex">
                        <div id="details" style="max-width:calc(100% - 210px);margin-left:auto">
                            <p class="text-bg-dark" style="padding:8px 16px;word-wrap:break-word;border-radius:18px 18px 0 18px;">${msg.contenu}</p>
                        </div>
                    </div>
                 </div>`
              : `<div id="msg">
                    <div id="msg-coming" style="display:flex;align-items:flex-end">
                        <img class="avatar-img rounded-circle" src="/assets/imgs/default-image-user.jpg" alt="" style="height:40px;width:40px"/>
                        <div id="details" style="max-width:calc(100% - 210px);margin-left:10px;margin-right:auto">
                            <p class="text-bg-light" style="padding:8px 16px;word-wrap:break-word;border-radius:18px 18px 18px 0;">${msg.contenu}</p>
                        </div>
                    </div>
                </div>`;

          messagesList.innerHTML += `${msgBox}`;

          messagesList.scrollTop = messagesList.scrollHeight;
        });

                    // Met à jour les messages comme "lus"
                    fetch(`/chat/mark-as-read/${correspondantSelectId}`, { method: 'POST' })
                    .then(() => {
                      if (correspondantSelect.querySelector("#unRBadge")){
                        correspondantSelect.querySelector("#unRBadge").remove();
                      }
                    })
                    .catch(error => console.error('Erreur lors de la mise à jour des messages lus:', error));

      })
      .catch((error) => {
        if (error.name === "AbortError") {
          console.log("Ancienne requête annulée :", correspondantId); // Debug
        } else {
          console.error("Erreur lors du chargement des messages:", error);
        }
      });
  }
});
