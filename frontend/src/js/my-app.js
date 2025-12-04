document.addEventListener("page:init", (e) => {
  const page = e.target;

  if (page.dataset.name === "dashboard") {
    const categoryButtons = page.querySelectorAll(".category-button");
    const foodCards = page.querySelectorAll(".food-card");

    // FILTER
    categoryButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        categoryButtons.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        const filter = btn.dataset.filter;

        foodCards.forEach((card) => {
          const cat = card.dataset.category;

          card.style.display =
            filter === "semua" || filter === cat ? "block" : "none";
        });
      });
    });

    // SEARCH
    const searchInput = page.querySelector(".searchbar input");

    searchInput.addEventListener("input", () => {
      const keyword = searchInput.value.toLowerCase();

      foodCards.forEach((card) => {
        const title = card
          .querySelector(".food-card-title")
          .textContent.toLowerCase();
        const subtitle = card
          .querySelector(".food-card-subtitle")
          .textContent.toLowerCase();

        card.style.display =
          title.includes(keyword) || subtitle.includes(keyword)
            ? "block"
            : "none";
      });
    });
  }
});
