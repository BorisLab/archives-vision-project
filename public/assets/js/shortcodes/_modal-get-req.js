document.addEventListener("DOMContentLoaded", function() {
  const rejectReq = document.getElementById("rejectRequest");
  const agreeReq = document.getElementById("agreeRequest");


  rejectReq.addEventListener("show.bs.modal", function(event) {
    let rejectButton = event.relatedTarget;
    let rejectNotifId = rejectButton.getAttribute("data-notif");
    let rejectDAId = rejectButton.getAttribute("data-da");
    let modalBodyNotifId = document.getElementById("rejectNotifId");
    let modalBodyDAId = document.getElementById("rejectReqId");
    modalBodyNotifId.value = rejectNotifId;
    modalBodyDAId.value = rejectDAId;
  });

  agreeReq.addEventListener("show.bs.modal", function(event) {
    let agreeButton = event.relatedTarget;
    let agreeNotifId = agreeButton.getAttribute("data-notif");
    let agreeDAId = agreeButton.getAttribute("data-da");
    let modalBodyNotifId = document.getElementById("agreeNotifId");
    let modalBodyDAId = document.getElementById("agreeReqId");
    modalBodyNotifId.value = agreeNotifId;
    modalBodyDAId.value = agreeDAId;
  });
});
