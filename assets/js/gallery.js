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

  const isProbablyFlickrFeed = (url) =>
    typeof url === "string" && /flickr\.com\/services\/feeds\/photos_public\.gne/i.test(url);

  const loadJsonp = (url) =>
    new Promise((resolve, reject) => {
      if (typeof url !== "string" || !url) {
        reject(new Error("jsonp_url_invalid"));
        return;
      }

      const cb = `__jsonp_cb_${Date.now()}_${Math.random().toString(16).slice(2)}`;

      const cleanup = () => {
        try {
          delete window[cb];
        } catch {
          window[cb] = undefined;
        }
        script.remove();
        clearTimeout(timer);
      };

      window[cb] = (data) => {
        cleanup();
        resolve(data);
      };

      // Flickr JSONP: serve format=json&jsoncallback=CB (no CORS needed).
      const u = new URL(url, window.location.href);
      u.searchParams.set("format", "json");
      u.searchParams.set("jsoncallback", cb);
      u.searchParams.delete("nojsoncallback");

      const script = document.createElement("script");
      script.src = u.toString();
      script.async = true;
      script.onerror = () => {
        cleanup();
        reject(new Error("jsonp_load_error"));
      };

      const timer = window.setTimeout(() => {
        cleanup();
        reject(new Error("jsonp_timeout"));
      }, 15000);

      document.head.appendChild(script);
    });

  const flickrBestGuessLarge = (thumbUrl) => {
    // Flickr static URLs often end with _m.jpg (small). _b is usually a good large size.
    // If not present, we just return the original.
    if (typeof thumbUrl !== "string") return "";
    return thumbUrl.replace(/_m(\.[a-z0-9]+)$/i, "_b$1");
  };

  const normalizeFeed = (data) => {
    // Supported formats:
    // 1) Existing local JSON: [{ title, src, thumb }]
    // 2) Flickr public feed JSON: { items: [{ title, media: { m }, ...}] }
    if (Array.isArray(data)) return data.filter((x) => x && x.src);

    if (data && Array.isArray(data.items)) {
      return data.items
        .map((item) => {
          const thumb = item?.media?.m;
          const src = flickrBestGuessLarge(thumb);
          const title = item?.title || "Foto";
          return thumb ? { title, src: src || thumb, thumb } : null;
        })
        .filter(Boolean);
    }

    return [];
  };

  const renderItems = (data) => {
    gallery.innerHTML = "";

    for (const item of data) {
      const el = document.createElement("div");
      // Items are injected after main.js runs IntersectionObserver on [data-reveal].
      // So we render them already visible (no reveal animation), otherwise they may stay hidden.
      el.className = "gallery__item is-visible";
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
        const items = normalizeFeed(data);
        if (!items.length) return;
        renderItems(items);
        bind();
      })
      .catch(() => {
        // If it's a Flickr feed, fetch() can be blocked by CORS in the browser.
        // Flickr supports JSONP, which avoids CORS entirely.
        if (isProbablyFlickrFeed(feedUrl)) {
          loadJsonp(feedUrl)
            .then((data) => {
              const items = normalizeFeed(data);
              if (!items.length) return;
              renderItems(items);
            })
            .finally(() => bind());
          return;
        }

        // fallback to existing markup
        bind();
      });
  } else {
    bind();
  }
})();

