document.addEventListener("DOMContentLoaded", () => {

  /* ========= SLIDER (only if exists) ========= */
  (function () {
    const slides = document.getElementById("slides");
    if (!slides) return;

    const total = slides.children.length;
    let i = 0;

    const show = (n) => {
      i = (n + total) % total;
      slides.style.transform = `translateX(-${i * 100}%)`;
    };

    document.querySelector(".s-prev")?.addEventListener("click", () => show(i - 1));
    document.querySelector(".s-next")?.addEventListener("click", () => show(i + 1));

    setInterval(() => show(i + 1), 4000);
  })();


  /* ========= PACKAGES PAGINATION (only if exists) ========= */
  (function () {
    const items = Array.from(document.querySelectorAll("#packagesGrid .pkg-item"));
    const pagination = document.getElementById("pkgPagination");
    if (!items.length || !pagination) return;

    const perPage = 6;
    let currentPage = 1;
    const totalPages = Math.ceil(items.length / perPage);

    function showPage(page) {
      currentPage = page;
      const start = (page - 1) * perPage;
      const end = start + perPage;

      items.forEach((item, idx) => {
        item.style.display = (idx >= start && idx < end) ? "" : "none";
      });

      renderPagination();
    }

    function renderPagination() {
      if (totalPages <= 1) { pagination.innerHTML = ""; return; }

      let html = "";
      html += `<button class="pkg-page-btn" ${currentPage===1?'disabled':''} data-page="${currentPage-1}">Prev</button>`;
      for (let p = 1; p <= totalPages; p++) {
        html += `<button class="pkg-page-btn ${p===currentPage?'active':''}" data-page="${p}">${p}</button>`;
      }
      html += `<button class="pkg-page-btn" ${currentPage===totalPages?'disabled':''} data-page="${currentPage+1}">Next</button>`;
      pagination.innerHTML = html;

      pagination.querySelectorAll("[data-page]").forEach(btn => {
        btn.addEventListener("click", function () {
          const page = parseInt(this.getAttribute("data-page"), 10);
          if (page >= 1 && page <= totalPages) showPage(page);
        });
      });
    }

    showPage(1);
  })();


  /* ========= Portfolio cards bg slider (only if exists) ========= */
  (function () {
    const section = document.getElementById("portfolioBg");
    const cards = document.querySelectorAll(".pf-card");
    if (!section || !cards.length) return;

    let index = 0;

    function render() {
      cards.forEach((card, i) => {
        const offset = i - index;

        card.style.transform =
          `translate(-50%, -50%) translateX(${offset * 120}px) scale(${offset === 0 ? 1 : 0.85})`;
        card.style.opacity = Math.abs(offset) > 2 ? 0 : 1;
        card.style.zIndex = 10 - Math.abs(offset);
      });

      const bg = cards[index].dataset.bg;
      if (bg) section.style.backgroundImage = `url(${bg})`;
    }

    function next() {
      index = (index + 1) % cards.length;
      render();
    }

    render();
    setInterval(next, 3500);
  })();


  /* ========= Booking Sidebar (Book Now + offcanvas) ========= */
   (function () {
    if (typeof flatpickr === "undefined") return;
    const checkinEl = document.getElementById("checkin");
    const checkoutEl = document.getElementById("checkout");
    if (!checkinEl || !checkoutEl) return;
    let checkoutManuallySelected = false;
    const checkoutPicker = flatpickr(checkoutEl, {
      dateFormat: "d/m/Y",
      disableMobile: true,
      clickOpens: true,
      onChange: () => checkoutManuallySelected = true
    });
    const checkinPicker = flatpickr(checkinEl, {
      dateFormat: "d/m/Y",
      minDate: "today",
      disableMobile: true,
      clickOpens: true,
      onChange: function (selectedDates) {
        if (!selectedDates?.[0]) return;
        const checkinDate = selectedDates[0];
        checkoutPicker.set("minDate", checkinDate);
        if (!checkoutManuallySelected) {
          checkoutPicker.setDate(checkinDate, true);
        } else {
          const co = checkoutPicker.selectedDates[0];
          if (co && co < checkinDate) {
            checkoutPicker.clear();
            checkoutManuallySelected = false;
            checkoutPicker.setDate(checkinDate, true);
          }
        }
      }
    });
    document.getElementById("checkinBox")?.addEventListener("click", () => checkinPicker.open());
    document.getElementById("checkoutBox")?.addEventListener("click", () => checkoutPicker.open());
    checkinEl.addEventListener("click", () => checkinPicker.open());
    checkoutEl.addEventListener("click", () => checkoutPicker.open());
  })();


});




