document.addEventListener("DOMContentLoaded", (event) => {
  // Handle close clicks
  document.querySelector(".toast__container").addEventListener('click', (e) => {
    e.target.closest('.toast').remove();
  });
  // Handle timeouts
  setTimeout(() => {
    document.querySelectorAll(".toast").forEach((toast) => {
      let opacity = 1;
      const timer = setInterval(function() {
          if (opacity <= 0.1) {
              clearInterval(timer);
              toast.remove();
          }
          toast.style.opacity = opacity;
          opacity -= 0.1;
      }, 50);
    });
  }, 5000);
});
