// script.js
document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".card");

  cards.forEach(card => {
    card.addEventListener("click", () => {
      alert(`Has seleccionado: ${card.querySelector("h3").textContent}`);
    });
  });
});

//Función cambios en la base de datos = cambios en la página web
