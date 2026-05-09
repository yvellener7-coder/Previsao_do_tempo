<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENE METEO</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css">
</head>
<body data-base-url="<?= htmlspecialchars($baseUrl) ?>">
    <main class="page">
        <header class="topbar shell">
            <a href="#" class="brand">RENE METEO</a>
            <div class="topbar-actions">
                <button id="themeToggle" class="btn link-btn" type="button">Tema</button>
                <button id="languageToggle" class="btn link-btn" type="button">EN</button>
                <button id="loginBtn" class="btn link-btn" type="button">Login</button>
                <button id="registerBtn" class="btn pill-btn" type="button">Registar</button>
                <button id="logoutBtn" class="btn link-btn" type="button" style="display:none;">Logout</button>
            </div>
        </header>

        <section class="hero shell">
            <article class="hero-left">
                <h1 class="hero-title">Atmospheric<br>precision for your<br>journey.</h1>
                <form id="weatherForm" class="search">
                    <input type="text" name="city" id="cityInput" placeholder="Enter city name..." required>
                    <button class="btn search-btn" type="submit">Search</button>
                </form>

                <div class="forecast-card">
                    <div class="forecast-top">
                        <div>
                            <p class="city-line" id="cityName">--</p>
                            <p class="meta-line" id="weatherDesc">---</p>
                        </div>
                        <p class="temp-line" id="tempValue">--°</p>
                    </div>
                    <div class="metrics">
                        <div><span>Humidity</span><strong id="humidityValue">--%</strong></div>
                        <div><span>Wind</span><strong id="windValue">--</strong></div>
                        <div><span>UV Index</span><strong>4/10</strong></div>
                    </div>
                </div>

            </article>

            <article class="hero-right">
                <div class="weather-visual">
                    <div class="icon-wrap">☀️</div>
                    <p id="skyMessage">Clear Skies Ahead</p>
                </div>
            </article>
        </section>

        <div id="toast" class="toast" role="status" aria-live="polite"></div>
    </main>

    <footer class="footer shell">
        <p>© 2026 RENE METEO. Forecast weather. Precision in every breeze.</p>
        <nav>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">API Documentation</a>
            <a href="#">Contact Us</a>
        </nav>
    </footer>

    <!-- Modals -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Entrar</h2>
            <form id="loginForm">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button class="btn pill-btn" type="submit">Entrar</button>
            </form>
        </div>
    </div>

    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Registar</h2>
            <form id="registerForm">
                <label>Nome</label>
                <input type="text" name="name" required>
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" minlength="6" required>
                <button class="btn pill-btn" type="submit">Registar</button>
            </form>
        </div>
    </div>

    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/main.js"></script>
</body>
</html>
