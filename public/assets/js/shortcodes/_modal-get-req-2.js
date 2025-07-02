document.addEventListener("DOMContentLoaded", function() {
  const rejectReq = document.getElementById("rejectRequest2");
  const agreeReq = document.getElementById("agreeRequest2");
  const revokeReq = document.getElementById("revokeAccess");


  rejectReq.addEventListener("show", function(event) {
    let rejectButton = event.relatedTarget;
    let rejectDAId = rejectButton.getAttribute("data-da");
    let modalBodyDAId = document.getElementById("rejectReqId");
    modalBodyDAId.value = rejectDAId;
  });

  agreeReq.addEventListener("show", function(event) {
    let agreeButton = event.relatedTarget;
    let agreeDAId = agreeButton.getAttribute("data-da");
    let modalBodyDAId = document.getElementById("agreeReqId");
    modalBodyDAId.value = agreeDAId;
  });

  revokeReq.addEventListener("show", function(event) {
    let revokeButton = event.relatedTarget;
    let revokeDAId = revokeButton.getAttribute("data-da");
    let modalBodyDAId = document.getElementById("revokeAccessReqId");
    modalBodyDAId.value = revokeDAId;
  });
});
