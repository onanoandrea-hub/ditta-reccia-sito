(() => {
  const prefersReducedMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches ?? false;

  // Sticky header "scrolled" state
  const header = document.querySelector("[data-header]");
  const setHeaderState = () => {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 8);
  };
  setHeaderState();
  window.addEventListener("scroll", setHeaderState, { passive: true });

  // Mobile menu
  const menuBtn = document.querySelector("[data-menu-button]");
  const menu = document.querySelector("[data-menu]");
  if (menuBtn && menu) {
    const setMenuOpen = (isOpen) => {
      menu.classList.toggle("is-open", isOpen);
      menuBtn.setAttribute("aria-expanded", String(isOpen));
      document.body.classList.toggle("menu-open", isOpen);
      document.body.style.overflow = isOpen ? "hidden" : "";
    };

    menuBtn.addEventListener("click", () => {
      const isOpen = !menu.classList.contains("is-open");
      setMenuOpen(isOpen);
    });
    menu.addEventListener("click", (e) => {
      const a = e.target.closest("a");
      if (!a) return;
      setMenuOpen(false);
    });

    // Close on escape
    window.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") return;
      if (!menu.classList.contains("is-open")) return;
      setMenuOpen(false);
    });

    // Close when tapping outside the menu (mobile overlay)
    document.addEventListener("click", (e) => {
      if (!menu.classList.contains("is-open")) return;
      if (menu.contains(e.target) || menuBtn.contains(e.target)) return;
      setMenuOpen(false);
    });
  }

  // Reveal on scroll
  const revealEls = Array.from(document.querySelectorAll("[data-reveal]"));
  if (!prefersReducedMotion && revealEls.length) {
    const io = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      },
      { threshold: 0.15 }
    );
    for (const el of revealEls) io.observe(el);
  } else {
    for (const el of revealEls) el.classList.add("is-visible");
  }

  // Scroll to top
  const scrollTopBtn = document.querySelector("[data-scrolltop]");
  const setScrollTopState = () => {
    if (!scrollTopBtn) return;
    scrollTopBtn.classList.toggle("is-visible", window.scrollY > 600);
  };
  setScrollTopState();
  window.addEventListener("scroll", setScrollTopState, { passive: true });
})();

