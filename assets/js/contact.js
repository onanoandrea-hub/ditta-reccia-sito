(() => {
  const form = document.querySelector("[data-contact-form]");
  const msg = document.querySelector("[data-contact-msg]");
  if (!form || !msg) return;

  const show = (text, kind) => {
    msg.textContent = text;
    msg.className = `alert ${kind}`;
  };

  form.addEventListener("submit", (e) => {
    // If the form is hosted with the old PHP endpoint, it can be changed later.
    // For now: prevent a dead POST during local preview and provide feedback.
    e.preventDefault();

    const data = new FormData(form);
    const name = String(data.get("name") || "").trim();
    const email = String(data.get("email") || "").trim();
    const message = String(data.get("message") || "").trim();

    if (!name || !email || !message) {
      show("Compila i campi obbligatori (Nome, Email, Messaggio).", "error");
      return;
    }

    show("Messaggio pronto: se pubblichi il sito, colleghiamo l’invio al backend. Intanto puoi chiamarci o scriverci via email.", "success");
    form.reset();
  });
})();

