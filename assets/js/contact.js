(() => {
  const form = document.querySelector("[data-contact-form]");
  const msg = document.querySelector("[data-contact-msg]");
  if (!form || !msg) return;

  const show = (text, kind) => {
    msg.textContent = text;
    msg.className = `alert ${kind}`;
  };

  let busy = false;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (busy) return;

    const data = new FormData(form);
    const name = String(data.get("name") || "").trim();
    const email = String(data.get("email") || "").trim();
    const message = String(data.get("message") || "").trim();

    if (!name || !email || !message) {
      show("Compila i campi obbligatori (Nome, Email, Messaggio).", "error");
      return;
    }

    const action = form.getAttribute("action") || "";
    if (!action) {
      show("Invio non configurato (manca action).", "error");
      return;
    }

    busy = true;
    show("Invio in corso…", "info");

    try {
      const res = await fetch(action, { method: "POST", body: data });
      const text = await res.text();

      if (res.ok) {
        show("Messaggio inviato correttamente. Ti ricontatteremo al più presto.", "success");
        form.reset();
      } else {
        show(text || "Errore durante l’invio. Riprova più tardi o contattaci via telefono/email.", "error");
      }
    } catch {
      show("Impossibile inviare ora. Riprova più tardi o contattaci via telefono/email.", "error");
    } finally {
      busy = false;
    }
  });
})();

