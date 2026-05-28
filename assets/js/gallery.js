(() => {
  const gallery = document.querySelector("[data-gallery]");
  if (!gallery) return;

  const lightbox = document.querySelector("[data-lightbox]");
  const imgEl = lightbox?.querySelector("[data-lightbox-img]");
  const titleEl = lightbox?.querySelector("[data-lightbox-title]");
  const prevBtn = lightbox?.querySelector("[data-lightbox-prev]");
  const nextBtn = lightbox?.querySelector("[data-lightbox-next]");
  const closeBtn = lightbox?.querySelector("[data-lightbox-close]");

  if (!lightbox || !imgEl || !titleEl || !prevBtn || !nextBtn || !closeBtn) return;

  const feedUrl = gallery.getAttribute("data-gallery-feed");

  const renderItems = (data) => {
    gallery.innerHTML = "";

    for (const item of data) {
      const el = document.createElement("div");
      el.className = "gallery__item reveal";
      el.setAttribute("data-reveal", "");
      el.setAttribute("data-gallery-item", "");
      el.setAttribute("tabindex", "0");
      el.setAttribute("role", "button");
      el.setAttribute("data-src", item.src);
      el.setAttribute("data-title", item.title || "Foto");

      const img = document.createElement("img");
      img.className = "gallery__thumb";
      img.src = item.thumb || item.src;
      img.alt = item.title || "Foto";
      img.loading = "lazy";
      img.decoding = "async";
      el.appendChild(img);

      gallery.appendChild(el);
    }
  };

  const collectDomItems = () =>
    Array.from(gallery.querySelectorAll("[data-gallery-item]")).map((el) => ({
      el,
      src: el.getAttribute("data-src") || "",
      title: el.getAttribute("data-title") || "Foto",
    }));

  let items = collectDomItems();

  let index = 0;
  let lastActive = null;

  const open = (i) => {
    index = (i + items.length) % items.length;
    lastActive = document.activeElement;

    const item = items[index];
    imgEl.src = item.src;
    imgEl.alt = item.title;
    titleEl.textContent = item.title;

    lightbox.classList.add("is-open");
    lightbox.setAttribute("aria-hidden", "false");
    closeBtn.focus();
    document.body.style.overflow = "hidden";
  };

  const close = () => {
    lightbox.classList.remove("is-open");
    lightbox.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    if (lastActive && typeof lastActive.focus === "function") lastActive.focus();
  };

  const prev = () => open(index - 1);
  const next = () => open(index + 1);

  const bind = () => {
    items = collectDomItems();
    if (!items.length) return;

    for (const [i, item] of items.entries()) {
      item.el.addEventListener("click", () => open(i));
      item.el.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          open(i);
        }
      });
    }
  };

  prevBtn.addEventListener("click", prev);
  nextBtn.addEventListener("click", next);
  closeBtn.addEventListener("click", close);

  lightbox.addEventListener("click", (e) => {
    if (e.target === lightbox) close();
  });

  window.addEventListener("keydown", (e) => {
    if (!lightbox.classList.contains("is-open")) return;
    if (e.key === "Escape") close();
    if (e.key === "ArrowLeft") prev();
    if (e.key === "ArrowRight") next();
  });

  if (feedUrl) {
    fetch(feedUrl, { cache: "no-store" })
      .then((r) => (r.ok ? r.json() : Promise.reject(new Error("feed_error"))))
      .then((data) => {
        if (!Array.isArray(data)) return;
        renderItems(data.filter((x) => x && x.src));
        bind();
      })
      .catch(() => {
        // fallback to existing markup
        bind();
      });
  } else {
    bind();
  }
})();

