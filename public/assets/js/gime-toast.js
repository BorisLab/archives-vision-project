const toastLive = document.getElementById("liveToast");
const toastContainer = document.getElementsByClassName("toast-container");
var cpt = 0;

do {
  const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLive);
  toastBootstrap.show();

  setTimeout(() => rmvtoast(toastLive), 5000);

  const rmvtoast = (toast) => {
    toast.classList.add("hide");
    setTimeout(() => toast.remove(), 300);
  };

  cpt++;
  toastBootstrap.remove();
} while (cpt == 0);
