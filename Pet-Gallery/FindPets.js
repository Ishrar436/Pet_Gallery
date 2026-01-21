function openDetails(name, behaviour) {
  if (!behaviour) behaviour = "No specific behaviour notes available.";

  alert("Name: " + name + "\n\nBehaviour: " + behaviour);
}

const searchInput = document.querySelector('input[name="search"]');
searchInput.addEventListener("input", function (e) {
  const cards = document.querySelectorAll(".card");
  const term = e.target.value.toLowerCase();

  cards.forEach((card) => {
    const title = card.querySelector("h4").textContent.toLowerCase();
    if (title.includes(term)) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
});
