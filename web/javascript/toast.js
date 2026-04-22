document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".toast__container");
  if (!container) {
    return;
  }

  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function dismissToast(toast) {
    if (!toast || toast.dataset.closing === "true") {
      return;
    }

    toast.dataset.closing = "true";

    if (reduceMotion) {
      toast.remove();
      return;
    }

    toast.classList.add("toast--closing");

    const removeToast = () => {
      toast.removeEventListener("transitionend", removeToast);
      toast.remove();
    };

    toast.addEventListener("transitionend", removeToast);
    window.setTimeout(removeToast, 220);
  }

  container.addEventListener("click", (e) => {
    if (!e.target.closest(".toast__close")) {
      return;
    }

    dismissToast(e.target.closest(".toast"));
  });

  container.querySelectorAll(".toast").forEach((toast) => {
    window.setTimeout(() => dismissToast(toast), 5000);
  });
});
