const baseUrl = document.body.dataset.baseUrl || "";
const apiBase = `${baseUrl}/api`;

const state = {
  isAuthenticated: false,
  lang: localStorage.getItem("lang") || "pt",
  theme: localStorage.getItem("theme") || "light",
};

const text = {
  pt: {
    theme: "Tema",
    login: "Login",
    register: "Registar",
    logout: "Sair",
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
  weatherForm: document.getElementById("weatherForm"),
  cityInput: document.getElementById("cityInput"),
  loginBtn: document.getElementById("loginBtn"),
  registerBtn: document.getElementById("registerBtn"),
  logoutBtn: document.getElementById("logoutBtn"),
  searchBtn: document.querySelector("#weatherForm button[type='submit']"),
  languageToggle: document.getElementById("languageToggle"),
  themeToggle: document.getElementById("themeToggle"),
  loginModal: document.getElementById("loginModal"),
  registerModal: document.getElementById("registerModal"),
  loginForm: document.getElementById("loginForm"),
  registerForm: document.getElementById("registerForm"),
};

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
  el.searchBtn.textContent = text[state.lang].search;
  el.cityInput.placeholder = text[state.lang].placeholder;
  applyTheme();
}

function updateAuthUi() {
  const guest = !state.isAuthenticated;
  el.cityInput.disabled = guest;
  el.searchBtn.disabled = guest;
  el.loginBtn.style.display = guest ? "inline-block" : "inline-block";
  el.registerBtn.style.display = guest ? "inline-block" : "none";
  el.logoutBtn.style.display = guest ? "none" : "inline-block";
}

function paintWeather(w) {
  el.cityName.textContent = `${w.name}, ${w.sys.country}`;
  el.weatherDesc.textContent = w.weather[0].description;
  el.tempValue.textContent = `${Math.round(w.main.temp)}°`;
  el.humidityValue.textContent = `${w.main.humidity}%`;
  el.windValue.textContent = `${w.wind.speed} m/s`;
  el.skyMessage.textContent = w.weather[0].main;
}

async function loadSession() {
  try {
    await api("/auth/me");
    state.isAuthenticated = true;
  } catch (_e) {
    state.isAuthenticated = false;
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
      state.isAuthenticated = true;
      updateAuthUi();
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
      state.isAuthenticated = true;
      updateAuthUi();
      notify(text[state.lang].registerOk);
    } catch (err) {
      notify(err.message);
    }
  });

  el.logoutBtn.addEventListener("click", async () => {
    try {
      await api("/auth/logout", { method: "POST" });
      state.isAuthenticated = false;
      updateAuthUi();
      notify(text[state.lang].logoutOk);
      await loadPublicWeather();
    } catch (err) {
      notify(err.message);
    }
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
const baseUrl = document.body.dataset.baseUrl || "";
const apiBase = `${baseUrl}/api`;

const state = {
  isAuthenticated: false,
  lang: localStorage.getItem("lang") || "pt",
  theme: localStorage.getItem("theme") || "light",
};

const t = {
  pt: {
    theme: "Tema",
    login: "Login",
    register: "Registar",
    logout: "Sair",
    search: "Pesquisar",
    cityPlaceholder: "Digite a cidade...",
    needLogin: "Faz login para pesquisar cidades.",
    loggedIn: "Login efetuado",
    registered: "Conta criada",
    loggedOut: "Sessão terminada",
    geolocDenied: "Permite localização para ver previsão da tua cidade.",
  },
  en: {
    theme: "Theme",
    login: "Login",
    register: "Register",
    logout: "Logout",
    search: "Search",
    cityPlaceholder: "Enter city name...",
    needLogin: "Please login to search cities.",
    loggedIn: "Logged in",
    registered: "Account created",
    loggedOut: "Logged out",
    geolocDenied: "Allow location to see weather in your city.",
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
  weatherForm: document.getElementById("weatherForm"),
  cityInput: document.getElementById("cityInput"),
  loginBtn: document.getElementById("loginBtn"),
  registerBtn: document.getElementById("registerBtn"),
  logoutBtn: document.getElementById("logoutBtn"),
  searchBtn: document.querySelector("#weatherForm button[type='submit']"),
  languageToggle: document.getElementById("languageToggle"),
  themeToggle: document.getElementById("themeToggle"),
  loginModal: document.getElementById("loginModal"),
  registerModal: document.getElementById("registerModal"),
  loginForm: document.getElementById("loginForm"),
  registerForm: document.getElementById("registerForm"),
};

function toast(message) {
  el.toast.textContent = message;
  el.toast.classList.add("show");
  setTimeout(() => el.toast.classList.remove("show"), 2200);
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
  const label = state.theme === "dark" ? "Light" : "Dark";
  el.themeToggle.textContent = `${t[state.lang].theme}: ${label}`;
}

function applyLanguage() {
  el.languageToggle.textContent = state.lang === "pt" ? "EN" : "PT";
  el.loginBtn.textContent = t[state.lang].login;
  el.registerBtn.textContent = t[state.lang].register;
  el.logoutBtn.textContent = t[state.lang].logout;
  el.searchBtn.textContent = t[state.lang].search;
  el.cityInput.placeholder = t[state.lang].cityPlaceholder;
  applyTheme();
}

function updateAuthUi() {
  const guest = !state.isAuthenticated;
  el.cityInput.disabled = guest;
  el.searchBtn.disabled = guest;
  el.loginBtn.style.display = guest ? "inline-block" : "none";
  el.registerBtn.style.display = guest ? "inline-block" : "none";
  el.logoutBtn.style.display = guest ? "none" : "inline-block";
}

function paintWeather(weather) {
  el.cityName.textContent = `${weather.name}, ${weather.sys.country}`;
  el.weatherDesc.textContent = weather.weather[0].description;
  el.tempValue.textContent = `${Math.round(weather.main.temp)}°`;
  el.humidityValue.textContent = `${weather.main.humidity}%`;
  el.windValue.textContent = `${weather.wind.speed} m/s`;
  el.skyMessage.textContent = weather.weather[0].main;
}

async function loadSession() {
  try {
    await api("/auth/me");
    state.isAuthenticated = true;
  } catch (_err) {
    state.isAuthenticated = false;
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
        toast(err.message);
      }
    },
    () => toast(t[state.lang].geolocDenied)
  );
}

function openModal(modal) {
  modal.style.display = "block";
}

function closeModal(modal) {
  modal.style.display = "none";
}

function bindModals() {
  el.loginBtn.addEventListener("click", () => openModal(el.loginModal));
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
      state.isAuthenticated = true;
      updateAuthUi();
      toast(t[state.lang].loggedIn);
    } catch (err) {
      toast(err.message);
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
      state.isAuthenticated = true;
      updateAuthUi();
      toast(t[state.lang].registered);
    } catch (err) {
      toast(err.message);
    }
  });

  el.logoutBtn.addEventListener("click", async () => {
    try {
      await api("/auth/logout", { method: "POST" });
      state.isAuthenticated = false;
      updateAuthUi();
      toast(t[state.lang].loggedOut);
      await loadPublicWeather();
    } catch (err) {
      toast(err.message);
    }
  });

  el.weatherForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.isAuthenticated) {
      toast(t[state.lang].needLogin);
      return;
    }
    try {
      const city = el.cityInput.value.trim();
      if (!city) return;
      const data = await api(`/weather/current?city=${encodeURIComponent(city)}`);
      paintWeather(data.weather);
    } catch (err) {
      toast(err.message);
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
const baseUrl = document.body.dataset.baseUrl || "";
const apiBase = `${baseUrl}/api`;

const state = {
  isAuthenticated: false,
  records: [],
  lang: localStorage.getItem("lang") || "pt",
  theme: localStorage.getItem("theme") || "light",
};

const el = {
  toast: document.getElementById("toast"),
  cityName: document.getElementById("cityName"),
  weatherDesc: document.getElementById("weatherDesc"),
  tempValue: document.getElementById("tempValue"),
  humidityValue: document.getElementById("humidityValue"),
  windValue: document.getElementById("windValue"),
  skyMessage: document.getElementById("skyMessage"),
  weatherForm: document.getElementById("weatherForm"),
  cityInput: document.getElementById("cityInput"),
  loginBtn: document.getElementById("loginBtn"),
  registerBtn: document.getElementById("registerBtn"),
  logoutBtn: document.getElementById("logoutBtn"),
  loginModal: document.getElementById("loginModal"),
  registerModal: document.getElementById("registerModal"),
  loginForm: document.getElementById("loginForm"),
  registerForm: document.getElementById("registerForm"),
  languageToggle: document.getElementById("languageToggle"),
  themeToggle: document.getElementById("themeToggle"),
};

function showToast(message) {
  el.toast.textContent = message;
  el.toast.classList.add("show");
  setTimeout(() => el.toast.classList.remove("show"), 2200);
}

function updateTheme() {
  document.body.classList.toggle("dark", state.theme === "dark");
}

function updateLanguageButton() {
  el.languageToggle.textContent = state.lang === "pt" ? "EN" : "PT";
}

function updateAuthUi() {
  const locked = !state.isAuthenticated;
  el.cityInput.disabled = locked;
  el.logoutBtn.style.display = locked ? "none" : "inline-block";

  el.loginBtn.style.display = locked ? "inline-block" : "none";
  el.registerBtn.style.display = locked ? "inline-block" : "none";
}

async function request(path, options = {}) {
  const response = await fetch(`${apiBase}${path}`, {
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    ...options,
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(payload.error || "Falha no pedido");
  return payload;
}

function paintWeather(weather) {
  el.cityName.textContent = `${weather.name}, ${weather.sys.country}`;
  el.weatherDesc.textContent = weather.weather[0].description;
  el.tempValue.textContent = `${Math.round(weather.main.temp)}°`;
  el.humidityValue.textContent = `${weather.main.humidity}%`;
  el.windValue.textContent = `${weather.wind.speed} m/s`;
  el.skyMessage.textContent = weather.weather[0].main === "Clear" ? "Clear Skies Ahead" : weather.weather[0].main;
}

async function loadPublicWeather() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(async ({ coords }) => {
    try {
      const data = await request(`/weather/public-current?lat=${coords.latitude}&lon=${coords.longitude}`);
      paintWeather(data.weather);
    } catch (err) {
      showToast(err.message);
    }
  });
}

async function loadMe() {
  try {
    await request("/auth/me");
    state.isAuthenticated = true;
  } catch (_err) {
    state.isAuthenticated = false;
  }
  updateAuthUi();
}

function setupModals() {
  const closes = document.querySelectorAll(".close");
  el.loginBtn.addEventListener("click", () => (el.loginModal.style.display = "block"));
  el.registerBtn.addEventListener("click", () => (el.registerModal.style.display = "block"));
  closes.forEach((c) =>
    c.addEventListener("click", () => {
      el.loginModal.style.display = "none";
      el.registerModal.style.display = "none";
    })
  );
}

function bindActions() {
  el.loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await request("/auth/login", {
        method: "POST",
        body: JSON.stringify(Object.fromEntries(new FormData(el.loginForm).entries())),
      });
      el.loginModal.style.display = "none";
      await loadMe();
      showToast("Login efetuado");
    } catch (err) {
      showToast(err.message);
    }
  });

  el.registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await request("/auth/register", {
        method: "POST",
        body: JSON.stringify(Object.fromEntries(new FormData(el.registerForm).entries())),
      });
      el.registerModal.style.display = "none";
      await loadMe();
      showToast("Conta criada");
    } catch (err) {
      showToast(err.message);
    }
  });

  el.weatherForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.isAuthenticated) {
      showToast("Faz login para pesquisar cidades");
      return;
    }
    try {
      const city = el.cityInput.value.trim();
      const data = await request(`/weather/current?city=${encodeURIComponent(city)}`);
      paintWeather(data.weather);
    } catch (err) {
      showToast(err.message);
    }
  });

  el.logoutBtn.addEventListener("click", async () => {
    try {
      await request("/auth/logout", { method: "POST" });
      state.isAuthenticated = false;
      updateAuthUi();
      await loadPublicWeather();
    } catch (err) {
      showToast(err.message);
    }
  });

  el.languageToggle.addEventListener("click", () => {
    state.lang = state.lang === "pt" ? "en" : "pt";
    localStorage.setItem("lang", state.lang);
    updateLanguageButton();
    updateAuthUi();
  });

  el.themeToggle.addEventListener("click", () => {
    state.theme = state.theme === "light" ? "dark" : "light";
    localStorage.setItem("theme", state.theme);
    updateTheme();
  });
}

async function init() {
  updateTheme();
  updateLanguageButton();
  setupModals();
  bindActions();
  await loadMe();
  if (!state.isAuthenticated) {
    await loadPublicWeather();
  }
}

init();
const baseUrl = document.body.dataset.baseUrl || "";
const apiBase = `${baseUrl}/api`;
const toast = document.getElementById("toast");
const userInfo = document.getElementById("userInfo");
const weatherNow = document.getElementById("weatherNow");
const recordsBody = document.getElementById("recordsBody");
const languageToggle = document.getElementById("languageToggle");
const themeToggle = document.getElementById("themeToggle");
const weatherForm = document.getElementById("weatherForm");
const refreshRecords = document.getElementById("refreshRecords");
const exportCsv = document.getElementById("exportCsv");
const exportPdf = document.getElementById("exportPdf");
const loginBtn = document.getElementById("loginBtn");
const registerBtn = document.getElementById("registerBtn");
const loginModal = document.getElementById("loginModal");
const registerModal = document.getElementById("registerModal");
const closeButtons = document.querySelectorAll(".close");

const i18n = {
  pt: { "dashboard.notLogged": "Não autenticado. A mostrar previsão local.", "dashboard.loginToSearch": "Faz login para pesquisar cidades e guardar histórico." },
  en: { "dashboard.notLogged": "Not authenticated. Showing your local forecast.", "dashboard.loginToSearch": "Login to search cities and save history." },
};

const state = {
  lang: localStorage.getItem("lang") || "pt",
  theme: localStorage.getItem("theme") || "light",
  records: [],
  isAuthenticated: false,
};

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 2200);
}

function applyLanguage() {
  languageToggle.textContent = state.lang === "pt" ? "EN" : "PT";
}

function applyTheme() {
  document.body.classList.toggle("dark", state.theme === "dark");
}

async function request(path, options = {}) {
  const response = await fetch(`${apiBase}${path}`, {
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    ...options,
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(payload.error || "Falha no pedido");
  return payload;
}

function setDashboardLock() {
  const locked = !state.isAuthenticated;
  weatherForm.querySelector('input[name="city"]').disabled = locked;
  weatherForm.querySelector('button[type="submit"]').disabled = locked;
  refreshRecords.disabled = locked;
  exportCsv.disabled = locked;
  exportPdf.disabled = locked;
  logoutBtn.disabled = locked;
  if (locked) {
    recordsBody.innerHTML = "";
    userInfo.textContent = i18n[state.lang]["dashboard.notLogged"];
  }
  updateAuthBadge();
  // Hide login/register buttons when authenticated
  loginBtn.style.display = locked ? "inline-block" : "none";
  registerBtn.style.display = locked ? "inline-block" : "none";
}

function updateAuthBadge() {
  if (!authModeBadge) return;
  if (state.isAuthenticated) {
    authModeBadge.textContent = state.lang === "pt" ? "Modo autenticado" : "Authenticated";
    authModeBadge.classList.remove("visitor");
    authModeBadge.classList.add("authenticated");
  } else {
    authModeBadge.textContent = state.lang === "pt" ? "Modo visitante" : "Visitor mode";
    authModeBadge.classList.remove("authenticated");
    authModeBadge.classList.add("visitor");
  }
}

function setupModals() {
  loginBtn.addEventListener("click", () => {
    loginModal.style.display = "block";
  });

  registerBtn.addEventListener("click", () => {
    registerModal.style.display = "block";
  });

  closeButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      loginModal.style.display = "none";
      registerModal.style.display = "none";
    });
  });

  window.addEventListener("click", (event) => {
    if (event.target === loginModal) {
      loginModal.style.display = "none";
    }
    if (event.target === registerModal) {
      registerModal.style.display = "none";
    }
  });
}

function renderWeatherCard(w) {
  weatherNow.innerHTML = `
    <strong>${w.name} (${w.sys.country})</strong><br>
    ${w.main.temp} C - ${w.weather[0].main} (${w.weather[0].description})<br>
    Humidade: ${w.main.humidity}% | Vento: ${w.wind.speed}<br>
    <small>${state.isAuthenticated ? "Pesquisa completa disponível." : i18n[state.lang]["dashboard.loginToSearch"]}</small>
  `;
}

async function loadPublicLocalForecast() {
  if (!navigator.geolocation) {
    weatherNow.innerHTML = "<em>Geolocalização não suportada pelo navegador.</em>";
    return;
  }
  navigator.geolocation.getCurrentPosition(async (pos) => {
    try {
      const { latitude, longitude } = pos.coords;
      const data = await request(`/weather/public-current?lat=${latitude}&lon=${longitude}`);
      renderWeatherCard(data.weather);
    } catch (err) {
      showToast(err.message);
    }
  }, () => {
    weatherNow.innerHTML = "<em>Permite localização para ver previsão da tua cidade.</em>";
  });
}

function renderRecords() {
  recordsBody.innerHTML = "";
  state.records.forEach((r) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td>${r.city_name} (${r.country_code})</td><td>${r.temperature_c}</td><td>${r.weather_main} - ${r.weather_description}</td><td>${r.humidity}%</td><td>${r.wind_speed}</td><td>${new Date(r.forecast_time).toLocaleString()}</td><td><button class="btn ghost edit-btn" data-id="${r.id}" type="button">Editar</button> <button class="btn danger delete-btn" data-id="${r.id}" type="button">Remover</button></td>`;
    recordsBody.appendChild(tr);
  });
}

async function loadMe() {
  try {
    const data = await request("/auth/me");
    state.isAuthenticated = true;
    userInfo.textContent = `${data.user.name} (${data.user.email})`;
  } catch (_err) {
    state.isAuthenticated = false;
  }
  setDashboardLock();
}

async function loadRecords() {
  if (!state.isAuthenticated) return;
  try {
    const data = await request("/weather/records");
    state.records = data.records || [];
    renderRecords();
  } catch (err) {
    showToast(err.message);
  }
}

function bindForms() {
  document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await request("/auth/login", { method: "POST", body: JSON.stringify(Object.fromEntries(new FormData(e.target).entries())) });
      showToast("Login efetuado");
      loginModal.style.display = "none";
      await loadMe();
      await loadRecords();
    } catch (err) { showToast(err.message); }
  });

  document.getElementById("registerForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await request("/auth/register", { method: "POST", body: JSON.stringify(Object.fromEntries(new FormData(e.target).entries())) });
      showToast("Conta criada");
      registerModal.style.display = "none";
      await loadMe();
      await loadRecords();
    } catch (err) { showToast(err.message); }
  });

  document.getElementById("forgotForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      const data = await request("/auth/forgot-password", { method: "POST", body: JSON.stringify(Object.fromEntries(new FormData(e.target).entries())) });
      showToast(`Token: ${data.reset_token || "gerado"}`);
    } catch (err) { showToast(err.message); }
  });

  document.getElementById("resetForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    try {
      await request("/auth/reset-password", { method: "POST", body: JSON.stringify(Object.fromEntries(new FormData(e.target).entries())) });
      showToast("Password alterada");
      e.target.reset();
    } catch (err) { showToast(err.message); }
  });

  weatherForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.isAuthenticated) {
      showToast(i18n[state.lang]["dashboard.loginToSearch"]);
      return;
    }
    const city = new FormData(e.target).get("city");
    try {
      const data = await request(`/weather/current?city=${encodeURIComponent(city)}`);
      renderWeatherCard(data.weather);
      showToast("Previsão guardada");
      await loadRecords();
    } catch (err) { showToast(err.message); }
  });
}

function bindActions() {
  logoutBtn.addEventListener("click", async () => {
    try {
      await request("/auth/logout", { method: "POST" });
      state.isAuthenticated = false;
      state.records = [];
      renderRecords();
      setDashboardLock();
      await loadPublicLocalForecast();
      showToast("Sessão terminada");
    } catch (err) { showToast(err.message); }
  });
  refreshRecords.addEventListener("click", loadRecords);
  recordsBody.addEventListener("click", async (e) => {
    if (!state.isAuthenticated) return;
    const target = e.target;
    const id = target.dataset.id;
    if (!id) return;

    if (target.classList.contains("delete-btn")) {
      try {
        await request(`/weather/records?id=${id}`, { method: "DELETE" });
        showToast("Registo removido");
        await loadRecords();
      } catch (err) { showToast(err.message); }
      return;
    }

    if (target.classList.contains("edit-btn")) {
      const record = state.records.find((r) => String(r.id) === String(id));
      if (!record) return;
      const temperature = prompt("Nova temperatura (C):", record.temperature_c);
      if (temperature === null) return;
      try {
        await request(`/weather/records?id=${id}`, {
          method: "PUT",
          body: JSON.stringify({
            temperature_c: Number(temperature),
            weather_main: record.weather_main,
            weather_description: record.weather_description,
            humidity: Number(record.humidity),
            wind_speed: Number(record.wind_speed),
            forecast_time: record.forecast_time,
          }),
        });
        showToast("Registo atualizado");
        await loadRecords();
      } catch (err) { showToast(err.message); }
    }
  });

  exportCsv.addEventListener("click", () => {
    if (!state.isAuthenticated || !state.records.length) {
      showToast("Sem dados para exportar");
      return;
    }
    const header = ["Cidade", "Pais", "Temp_C", "Clima", "Humidade", "Vento", "Data"];
    const rows = state.records.map((r) => [
      r.city_name, r.country_code, r.temperature_c,
      `${r.weather_main} ${r.weather_description}`, r.humidity, r.wind_speed, r.forecast_time,
    ]);
    const csv = [header, ...rows].map((line) => line.join(";")).join("\n");
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "weather_records.csv";
    a.click();
    URL.revokeObjectURL(url);
  });

  exportPdf.addEventListener("click", () => {
    if (!state.isAuthenticated) {
      showToast(i18n[state.lang]["dashboard.loginToSearch"]);
      return;
    }
    window.print();
  });

  languageToggle.addEventListener("click", () => {
    state.lang = state.lang === "pt" ? "en" : "pt";
    localStorage.setItem("lang", state.lang);
    applyLanguage();
    setDashboardLock();
  });
  themeToggle.addEventListener("click", () => {
    state.theme = state.theme === "light" ? "dark" : "light";
    localStorage.setItem("theme", state.theme);
    applyTheme();
  });
}

async function init() {
  applyLanguage();
  applyTheme();
  setupModals();
  bindForms();
  bindActions();
  await loadMe();
  if (state.isAuthenticated) {
    await loadRecords();
  } else {
    await loadPublicLocalForecast();
  }
}

init();
