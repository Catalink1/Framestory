/* ═══════════════════════════════════════
   FRAMESTORY.RO — main.js
═══════════════════════════════════════ */

// ── Page navigation ──
function showPage(id) {
  document
    .querySelectorAll(".page")
    .forEach((p) => p.classList.remove("active"));
  const target = document.getElementById(id);
  if (target) target.classList.add("active");
  window.scrollTo({ top: 0, behavior: "smooth" });
  updateNav(id);
  initReveal();
  if (id === "portfolio") setTimeout(() => initCarousel("port"), 100);
  if (id === "phone") setTimeout(() => initCarousel("phone"), 100);
}

// ── Nav state ──
function updateNav(pageId) {
  const nav = document.getElementById("nav");
  if (pageId === "home") {
    nav.classList.add("on-dark");
  } else {
    nav.classList.remove("on-dark");
    nav.classList.add("opaque");
  }
}

window.addEventListener("scroll", () => {
  const nav = document.getElementById("nav");
  const active = document.querySelector(".page.active");
  const isHome = active && active.id === "home";
  if (window.scrollY > 40) {
    nav.classList.add("opaque");
  } else {
    nav.classList.remove("opaque");
    if (isHome) nav.classList.add("on-dark");
  }
});

// ── Mobile menu ──
document.getElementById("hamburger").addEventListener("click", () => {
  document.getElementById("mobileNav").classList.add("open");
});
document.getElementById("mobileClose").addEventListener("click", closeMobile);

function closeMobile() {
  document.getElementById("mobileNav").classList.remove("open");
}

// ── Gallery filters ──
document.querySelectorAll(".f-btn").forEach((btn) => {
  btn.addEventListener("click", function () {
    document
      .querySelectorAll(".f-btn")
      .forEach((b) => b.classList.remove("active"));
    this.classList.add("active");
    const filter = this.dataset.filter;
    const c = carousels["port"];
    if (!c) return;
    c.slides.forEach((s) => {
      s.style.display =
        filter === "all" || s.dataset.cat === filter ? "" : "none";
    });
    const firstVisible = Array.from(c.slides).findIndex(
      (s) => s.style.display !== "none",
    );
    if (firstVisible >= 0) carouselGo("port", firstVisible);
  });
});

// ── Scroll reveal ──
function initReveal() {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add("visible");
          observer.unobserve(e.target);
        }
      });
    },
    { threshold: 0.1 },
  );
  document.querySelectorAll(".reveal").forEach((el) => {
    el.classList.remove("visible");
    observer.observe(el);
  });
}

// ── Contact form ──
function submitForm() {
  const name = document.getElementById("fname").value.trim();
  const email = document.getElementById("femail").value.trim();
  const msg = document.getElementById("fmsg").value.trim();
  if (!name || !email || !msg) {
    alert("Te rog completează toate câmpurile obligatorii.");
    return;
  }
  document.getElementById("fSuccess").style.display = "block";
}
// ── FAQ ──
function toggleFaq(btn) {
  const item = btn.parentElement;
  const answer = item.querySelector(".faq-a");
  const isOpen = btn.classList.contains("open");

  document.querySelectorAll(".faq-q.open").forEach((q) => {
    q.classList.remove("open");
    q.parentElement.querySelector(".faq-a").classList.remove("open");
  });

  if (!isOpen) {
    btn.classList.add("open");
    answer.classList.add("open");
  }
}

// ── Carusel ──
const carousels = {};

function setCarouselPositions(id) {
  const c = carousels[id];
  if (!c) return;
  const count = c.slides.length;
  const angle = 360 / count;
  const radius = Math.max(
    300,
    Math.round(c.stage.clientWidth / 2 / Math.tan(Math.PI / count)),
  );
  c.angle = angle;
  c.radius = radius;
  c.slides.forEach((slide, i) => {
    const itemAngle = i * angle;
    slide.style.transform = `rotateY(${itemAngle}deg) translateZ(${radius}px)`;
    slide.style.opacity = "0.28";
    slide.style.zIndex = "1";
    slide.classList.remove("active");
  });
}

function setCarouselRotation(id) {
  const c = carousels[id];
  if (!c || !c.track) return;
  c.track.style.transform = `translateZ(-${c.radius}px) rotateY(${-c.current * c.angle}deg)`;
}

function setActiveSlide(id, index) {
  const c = carousels[id];
  if (!c) return;
  c.slides.forEach((slide) => {
    slide.classList.remove("active");
    slide.style.opacity = "0.28";
    slide.style.zIndex = "1";
  });
  c.thumbs.forEach((thumb) => thumb.classList.remove("active"));
  const slide = c.slides[index];
  if (!slide) return;
  slide.classList.add("active");
  slide.style.opacity = "1";
  slide.style.zIndex = "2";
  const thumb = c.thumbs[index];
  if (thumb) {
    thumb.classList.add("active");
    thumb.scrollIntoView({
      behavior: "smooth",
      block: "nearest",
      inline: "center",
    });
  }
}

function initCarousel(id) {
  const stage = document.getElementById(`${id}-stage`);
  const track = stage ? stage.querySelector(".carousel-track") : null;
  const slides = track ? track.querySelectorAll(".carousel-slide") : [];
  const thumbs = document.querySelectorAll(`#${id}-thumbs .thumb`);
  if (!track || slides.length === 0) return;
  if (carousels[id] && carousels[id].timer) clearInterval(carousels[id].timer);
  carousels[id] = {
    stage,
    track,
    slides: Array.from(slides),
    thumbs: Array.from(thumbs),
    current: 0,
    timer: null,
    angle: 0,
    radius: 0,
    touch: { startX: 0, startY: 0, endX: 0, endY: 0 },
  };
  const resetAutoPlay = () => {
    if (carousels[id].timer) clearInterval(carousels[id].timer);
    carousels[id].timer = setInterval(() => carouselMove(id, 1), 4000);
  };
  const onTouchStart = (event) => {
    const touch = event.changedTouches ? event.changedTouches[0] : event;
    carousels[id].touch.startX = touch.clientX;
    carousels[id].touch.startY = touch.clientY;
  };
  const onTouchMove = (event) => {
    const touch = event.changedTouches ? event.changedTouches[0] : event;
    carousels[id].touch.endX = touch.clientX;
    carousels[id].touch.endY = touch.clientY;
  };
  const onTouchEnd = () => {
    const dx = carousels[id].touch.endX - carousels[id].touch.startX;
    const dy = carousels[id].touch.endY - carousels[id].touch.startY;
    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
      if (dx < 0) carouselMove(id, 1);
      else carouselMove(id, -1);
      resetAutoPlay();
    }
  };
  stage.addEventListener("touchstart", onTouchStart, { passive: true });
  stage.addEventListener("touchmove", onTouchMove, { passive: true });
  stage.addEventListener("touchend", onTouchEnd);
  setCarouselPositions(id);
  setActiveSlide(id, 0);
  setCarouselRotation(id);
  carousels[id].timer = setInterval(() => carouselMove(id, 1), 4000);
}

function carouselGo(id, index) {
  const c = carousels[id];
  if (!c) return;
  const count = c.slides.length;
  index = ((index % count) + count) % count;
  c.current = index;
  setCarouselRotation(id);
  setActiveSlide(id, index);
}

function carouselMove(id, dir) {
  const c = carousels[id];
  if (!c) return;
  let next = (c.current + dir + c.slides.length) % c.slides.length;
  let attempts = 0;
  while (
    c.slides[next].style.display === "none" &&
    attempts < c.slides.length
  ) {
    next = (next + dir + c.slides.length) % c.slides.length;
    attempts++;
  }
  carouselGo(id, next);
}
function togglePause(id) {
  const c = carousels[id];
  if (!c) return;
  const btn = document.getElementById(`${id}-pause`);
  if (c.timer) {
    clearInterval(c.timer);
    c.timer = null;
    btn.textContent = "▶";
  } else {
    c.timer = setInterval(() => carouselMove(id, 1), 4000);
    btn.textContent = "⏸";
  }
}

function validateCarousel(id) {
  const stage = document.getElementById(`${id}-stage`);
  const thumbs = document.getElementById(`${id}-thumbs`);
  if (!stage) return console.warn(`carousel ${id}: stage missing`);
  const slides = stage.querySelectorAll(".carousel-slide");
  const thumbsList = thumbs ? thumbs.querySelectorAll(".thumb") : [];
  const activeSlide = stage.querySelector(".carousel-slide.active");
  const activeThumb = thumbs ? thumbs.querySelector(".thumb.active") : null;
  console.group(`validateCarousel(${id})`);
  console.log("slides:", slides.length);
  console.log("thumbs:", thumbsList.length);
  console.log(
    "active slide index:",
    activeSlide ? [...slides].indexOf(activeSlide) : "none",
  );
  console.log(
    "active thumb index:",
    activeThumb ? [...thumbsList].indexOf(activeThumb) : "none",
  );
  if (slides.length > 0 && !activeSlide) console.warn("no active slide found");
  if (thumbsList.length > 0 && !activeThumb)
    console.warn("no active thumb found");
  console.groupEnd();
}

function validateAllCarousels() {
  validateCarousel("port");
  validateCarousel("phone");
}

// ── Init ──
document.addEventListener("DOMContentLoaded", () => {
  updateNav("home");
  initReveal();
  validateAllCarousels();
});
