document.querySelectorAll(".faq-question").forEach((btn) => {
  btn.addEventListener("click", () => {
    const item = btn.parentElement;
    const icon = btn.querySelector(".faq-icon");

    // Close any other open FAQ
    document.querySelectorAll(".faq-item").forEach((faq) => {
      if (faq !== item) {
        faq.classList.remove("active");
        faq.querySelector(".faq-icon").textContent = "+";
      }
    });

    // Toggle current FAQ
    item.classList.toggle("active");

    // Change icon
    icon.textContent = item.classList.contains("active") ? "−" : "+";
  });
});


// MENU OVERLAY
const openMenu = document.querySelector(".nav"); // Your menu icon
const menuOverlay = document.getElementById("menuOverlay");
const closeMenuBtn = document.getElementById("closeMenuBtn");

// OPEN MENU
openMenu.addEventListener("click", () => {
    menuOverlay.classList.add("active");
    document.body.style.overflow = "hidden"; // LOCK SCROLL
});

// CLOSE MENU
closeMenuBtn.addEventListener("click", () => {
    menuOverlay.classList.remove("active");
    document.body.style.overflow = "auto"; // RESTORE SCROLL
});

// DROPDOWN
document.querySelector(".service-toggle").addEventListener("click", function (e) {
    e.preventDefault();

    const dropdown = this.nextElementSibling;
    const icon = this.querySelector(".fa");

    dropdown.classList.toggle("open");
    icon.classList.toggle("rotate");
});


function goHome() {
  window.location.href = "index.html";
}

function goPageW() {
  window.location.href = "Warehouse.html";
}

function goPageT() {
  window.location.href = "Transportation.html";
}

function goPageD() {
  window.location.href = "services-1.html";
}

