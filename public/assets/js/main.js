const baseUrl = document.body.dataset.baseUrl || "";
const apiBase = `${baseUrl}/api`;

const state = {
  isAuthenticated: false,
  lang: localStorage.getItem("lang") || "pt",
  theme: localStorage.getItem("theme") || "light",
  userName: null,
};

const text = {
  pt: {
    theme: "Tema",
    login: "Login",
    register: "Registar",
    logout: "Sair",
    exportPdf: "Exportar PDF",
    search: "Pesquisar",
    placeholder: "Digite a cidade...",
    needLogin: "Faz login para pesquisar cidades.",
    geolocDenied: "Permite localização para ver previsão da tua cidade.",
    loginOk: "Login efetuado",
    registerOk: "Conta criada",
    logoutOk: "Sessão terminada",
  },
  en: {
    theme: "Theme",
    login: "Login",
    register: "Register",
    logout: "Logout",
    search: "Search",
    placeholder: "Enter city name...",
    needLogin: "Please login to search cities.",
    geolocDenied: "Allow location to view weather in your city.",
    loginOk: "Login successful",
    registerOk: "Account created",
    logoutOk: "Logged out",
  },
};

const el = {
  toast: document.getElementById("toast"),
  cityName: document.getElementById("cityName"),
  weatherDesc: document.getElementById("weatherDesc"),
  tempValue: document.getElementById("tempValue"),
  humidityValue: document.getElementById("humidityValue"),
  windValue: document.getElementById("windValue"),
  skyMessage: document.getElementById("skyMessage"),
  iconWrap: document.querySelector(".icon-wrap"),
  weatherForm: document.getElementById("weatherForm"),
  cityInput: document.getElementById("cityInput"),
  loginBtn: document.getElementById("loginBtn"),
  registerBtn: document.getElementById("registerBtn"),
  exportPdfBtn: document.getElementById("exportPdfBtn"),
  logoutBtn: document.getElementById("logoutBtn"),
  searchBtn: document.querySelector("#weatherForm button[type='submit']"),
  languageToggle: document.getElementById("languageToggle"),
  themeToggle: document.getElementById("themeToggle"),
  loginModal: document.getElementById("loginModal"),
  registerModal: document.getElementById("registerModal"),
  loginForm: document.getElementById("loginForm"),
  registerForm: document.getElementById("registerForm"),
  heroTitle: document.querySelector(".hero-title"),
};

function weatherIcon(main) {
  const key = String(main || "").toLowerCase();
  if (key.includes("thunderstorm")) return "⛈️";
  if (key.includes("drizzle")) return "🌦️";
  if (key.includes("rain")) return "🌧️";
  if (key.includes("snow")) return "❄️";
  if (key.includes("mist") || key.includes("fog") || key.includes("haze") || key.includes("smoke")) return "🌫️";
  if (key.includes("cloud")) return "☁️";
  if (key.includes("clear")) return "☀️";
  return "🌤️";
}

function notify(message) {
  el.toast.textContent = message;
  el.toast.classList.add("show");
  setTimeout(() => el.toast.classList.remove("show"), 2000);
}

async function api(path, options = {}) {
  const response = await fetch(`${apiBase}${path}`, {
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    ...options,
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(payload.error || "Erro na API");
  return payload;
}

function applyTheme() {
  document.body.classList.toggle("dark", state.theme === "dark");
  const modeLabel = state.theme === "dark" ? "Light" : "Dark";
  el.themeToggle.textContent = `${text[state.lang].theme}: ${modeLabel}`;
}

function applyLanguage() {
  el.languageToggle.textContent = state.lang === "pt" ? "EN" : "PT";
  el.loginBtn.textContent = text[state.lang].login;
  el.registerBtn.textContent = text[state.lang].register;
  el.logoutBtn.textContent = text[state.lang].logout;
  el.exportPdfBtn.textContent = text[state.lang].exportPdf;
  el.searchBtn.textContent = text[state.lang].search;
  el.cityInput.placeholder = text[state.lang].placeholder;
  applyTheme();
}

function updateAuthUi() {
  const guest = !state.isAuthenticated;
  el.cityInput.disabled = guest;
  el.searchBtn.disabled = guest;
  el.exportPdfBtn.disabled = guest;
  el.loginBtn.style.display = guest ? "inline-block" : "none";
  el.registerBtn.style.display = guest ? "inline-block" : "none";
  el.logoutBtn.style.display = guest ? "none" : "inline-block";
  if (state.isAuthenticated && state.userName) {
    el.heroTitle.innerHTML = `Bem vindo,<br>${state.userName}`;
  } else {
    el.heroTitle.innerHTML = `Atmospheric<br>precision for your<br>journey.`;
  }
}

function paintWeather(w) {
  el.cityName.textContent = `${w.name}, ${w.sys.country}`;
  el.weatherDesc.textContent = w.weather[0].description;
  el.tempValue.textContent = `${Math.round(w.main.temp)}°`;
  el.humidityValue.textContent = `${w.main.humidity}%`;
  el.windValue.textContent = `${w.wind.speed} m/s`;
  const main = w.weather[0].main;
  el.skyMessage.textContent = main;
  el.iconWrap.textContent = weatherIcon(main);
}

async function loadSession() {
  try {
    const data = await api("/auth/me");
    state.isAuthenticated = true;
    state.userName = data.user.name;
  } catch (_e) {
    state.isAuthenticated = false;
    state.userName = null;
  }
  updateAuthUi();
}

async function loadPublicWeather() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(
    async ({ coords }) => {
      try {
        const data = await api(`/weather/public-current?lat=${coords.latitude}&lon=${coords.longitude}`);
        paintWeather(data.weather);
      } catch (err) {
        notify(err.message);
      }
    },
    () => notify(text[state.lang].geolocDenied)
  );
}

function openModal(modal) {
  modal.style.display = "block";
}

function closeModal(modal) {
  modal.style.display = "none";
}

function bindModals() {
  el.loginBtn.addEventListener("click", () => {
    if (state.isAuthenticated) return;
    openModal(el.loginModal);
  });
  el.registerBtn.addEventListener("click", () => openModal(el.registerModal));

  document.querySelectorAll(".close").forEach((btn) => {
    btn.addEventListener("click", () => {
      closeModal(el.loginModal);
      closeModal(el.registerModal);
    });
  });

  window.addEventListener("click", (e) => {
    if (e.target === el.loginModal) closeModal(el.loginModal);
    if (e.target === el.registerModal) closeModal(el.registerModal);
  });
}

function bindActions() {
  el.loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await api("/auth/login", {
        method: "POST",
        body: JSON.stringify(Object.fromEntries(new FormData(el.loginForm).entries())),
      });
      closeModal(el.loginModal);
      await loadSession();
      notify(text[state.lang].loginOk);
    } catch (err) {
      notify(err.message);
    }
  });

  el.registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await api("/auth/register", {
        method: "POST",
        body: JSON.stringify(Object.fromEntries(new FormData(el.registerForm).entries())),
      });
      closeModal(el.registerModal);
      await loadSession();
      notify(text[state.lang].registerOk);
    } catch (err) {
      notify(err.message);
    }
  });

  el.logoutBtn.addEventListener("click", async () => {
    try {
      await api("/auth/logout", { method: "POST" });
      state.isAuthenticated = false;
      state.userName = null;
      updateAuthUi();
      notify(text[state.lang].logoutOk);
      await loadPublicWeather();
    } catch (err) {
      notify(err.message);
    }
  });

  el.exportPdfBtn.addEventListener("click", () => {
    if (!state.isAuthenticated) {
      notify(text[state.lang].needLogin);
      openModal(el.loginModal);
      return;
    }
    window.location.href = `${apiBase}/reports/weather-pdf`;
  });

  el.weatherForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.isAuthenticated) {
      notify(text[state.lang].needLogin);
      return;
    }
    const city = el.cityInput.value.trim();
    if (!city) return;
    try {
      const data = await api(`/weather/current?city=${encodeURIComponent(city)}`);
      paintWeather(data.weather);
    } catch (err) {
      notify(err.message);
    }
  });

  el.languageToggle.addEventListener("click", () => {
    state.lang = state.lang === "pt" ? "en" : "pt";
    localStorage.setItem("lang", state.lang);
    applyLanguage();
  });

  el.themeToggle.addEventListener("click", () => {
    state.theme = state.theme === "light" ? "dark" : "light";
    localStorage.setItem("theme", state.theme);
    applyTheme();
  });
}

async function init() {
  applyLanguage();
  bindModals();
  bindActions();
  await loadSession();
  if (!state.isAuthenticated) {
    await loadPublicWeather();
  }
}

init();
