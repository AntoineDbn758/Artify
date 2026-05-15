document.documentElement.classList.add("js");

const navToggle = document.querySelector(".nav-toggle");
const siteNav = document.querySelector(".site-nav");
const navLinks = document.querySelectorAll(".site-nav a");
const toast = document.getElementById("toast");
const toastTriggers = document.querySelectorAll("[data-toast]");
const searchForm = document.getElementById("search-form");
const searchInput = document.getElementById("search-input");
const categorySelect = document.getElementById("category-select");
const resetFiltersButton = document.getElementById("reset-filters");
const chipButtons = document.querySelectorAll(".chip");
const creationCards = document.querySelectorAll(".creation-card");
const resultsCount = document.getElementById("results-count");
const faqItems = document.querySelectorAll(".faq-item");
const revealItems = document.querySelectorAll(".reveal");
const demoForms = document.querySelectorAll("[data-demo-form]");

let toastTimer;

function normalizeValue(value) {
  return value
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
}

function showToast(message) {
  window.clearTimeout(toastTimer);
  toast.textContent = message;
  toast.classList.add("is-visible");

  toastTimer = window.setTimeout(() => {
    toast.classList.remove("is-visible");
  }, 2200);
}

function closeMobileNav() {
  if (!navToggle || !siteNav) {
    return;
  }

  navToggle.setAttribute("aria-expanded", "false");
  siteNav.classList.remove("is-open");
}

function applyFilters() {
  if (!searchInput || !categorySelect || !resultsCount || creationCards.length === 0) {
    return;
  }

  const query = normalizeValue(searchInput.value.trim());
  const selectedCategory = categorySelect.value;
  let visibleCards = 0;

  creationCards.forEach((card) => {
    const matchesCategory =
      selectedCategory === "all" || card.dataset.category === selectedCategory;
    const matchesQuery =
      query.length === 0 || normalizeValue(card.dataset.search).includes(query);
    const isVisible = matchesCategory && matchesQuery;

    card.classList.toggle("is-hidden", !isVisible);

    if (isVisible) {
      visibleCards += 1;
    }
  });

  resultsCount.textContent = String(visibleCards);
}

function setActiveChip(category) {
  if (chipButtons.length === 0) {
    return;
  }

  chipButtons.forEach((button) => {
    button.classList.toggle("is-active", button.dataset.category === category);
  });
}

if (navToggle && siteNav) {
  navToggle.addEventListener("click", () => {
    const isOpen = navToggle.getAttribute("aria-expanded") === "true";
    navToggle.setAttribute("aria-expanded", String(!isOpen));
    siteNav.classList.toggle("is-open", !isOpen);
  });
}

navLinks.forEach((link) => {
  link.addEventListener("click", closeMobileNav);
});

toastTriggers.forEach((trigger) => {
  trigger.addEventListener("click", () => {
    showToast(trigger.dataset.toast);
  });
});

demoForms.forEach((form) => {
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    showToast(form.dataset.toast || "Action simulee");
  });
});

if (searchForm) {
  searchForm.addEventListener("submit", (event) => {
    event.preventDefault();
    applyFilters();
    document.getElementById("creations").scrollIntoView({ behavior: "smooth", block: "start" });
  });
}

if (searchInput) {
  searchInput.addEventListener("input", applyFilters);
}

if (categorySelect) {
  categorySelect.addEventListener("change", () => {
    setActiveChip(categorySelect.value);
    applyFilters();
  });
}

chipButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const { category } = button.dataset;
    categorySelect.value = category;
    setActiveChip(category);
    applyFilters();
  });
});

if (resetFiltersButton) {
  resetFiltersButton.addEventListener("click", () => {
    searchInput.value = "";
    categorySelect.value = "all";
    setActiveChip("all");
    applyFilters();
  });
}

faqItems.forEach((item) => {
  const trigger = item.querySelector(".faq-question");

  trigger.addEventListener("click", () => {
    const isOpen = item.classList.contains("is-open");

    faqItems.forEach((faqItem) => {
      faqItem.classList.remove("is-open");
      faqItem.querySelector(".faq-question").setAttribute("aria-expanded", "false");
    });

    if (!isOpen) {
      item.classList.add("is-open");
      trigger.setAttribute("aria-expanded", "true");
    }
  });
});

if ("IntersectionObserver" in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.18,
    }
  );

  revealItems.forEach((item) => observer.observe(item));
} else {
  revealItems.forEach((item) => item.classList.add("is-visible"));
}

if (categorySelect) {
  setActiveChip(categorySelect.value || "all");
}

applyFilters();
