/**
 * EventCertificateHub front-end behaviors.
 */

function confirmDelete() {
  return confirm("Delete this certificate? This cannot be undone.");
}

function showPDF(input) {
  const label = document.getElementById("fileName");
  if (!label) return;
  if (input.files.length > 0) {
    label.innerHTML = "📄 " + input.files[0].name;
  } else {
    label.innerHTML = "";
  }
}

// Mobile navbar toggle
document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("navToggle");
  const links = document.querySelector(".navbar-links");
  if (!toggle || !links) return;

  toggle.addEventListener("click", function () {
    const isOpen = links.classList.toggle("open");
    toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
  });
});
