document.addEventListener("DOMContentLoaded", function() {
  const activeUserAccount = document.getElementById("activeAct");
  const disableUserAccount = document.getElementById("disableAct");


  activeUserAccount.addEventListener("show.bs.modal", function(event) {
    let actvButton = event.relatedTarget;
    let actId = actvButton.getAttribute("data-id");
    let modalBodyFileId = document.getElementById("aaccountId");
    modalBodyFileId.value = actId;

    let activActForm = document.getElementById("activeAccountForm");
    activActForm.action = activActForm.action.replace(/\/\d+$/, "/" + actId);
  });

  disableUserAccount.addEventListener("show.bs.modal", function(event) {
    let dsblButton = event.relatedTarget;
    let actId = dsblButton.getAttribute("data-id");
    let modalBodyFileId = document.getElementById("daccountId");
    modalBodyFileId.value = actId;

    let disablActForm = document.getElementById("disableAccountForm");
    disablActForm.action = disablActForm.action.replace(/\/\d+$/, "/" + actId);
  });
});
