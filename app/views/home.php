<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENE METEO</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css">
</head>
<body data-base-url="<?= htmlspecialchars($baseUrl) ?>">
    <div class="animated-clouds">
        <svg viewBox="0 0 1920 1080" preserveAspectRatio="none" class="clouds-layer clouds-slow">
            <defs>
                <filter id="cloudFilter">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2"/>
                </filter>
            </defs>
            <!-- Primeira camada de nuvens - movimento lento -->
            <g filter="url(#cloudFilter)" opacity="0.7">
                <ellipse cx="100" cy="150" rx="80" ry="40" fill="#ffffff"/>
                <ellipse cx="160" cy="160" rx="70" ry="35" fill="#ffffff"/>
                <ellipse cx="40" cy="170" rx="60" ry="30" fill="#ffffff"/>
                
                <ellipse cx="500" cy="120" rx="90" ry="45" fill="#ffffff"/>
                <ellipse cx="570" cy="130" rx="75" ry="38" fill="#ffffff"/>
                <ellipse cx="430" cy="145" rx="70" ry="35" fill="#ffffff"/>
                
                <ellipse cx="1000" cy="140" rx="85" ry="42" fill="#ffffff"/>
                <ellipse cx="1070" cy="155" rx="80" ry="40" fill="#ffffff"/>
                <ellipse cx="930" cy="160" rx="65" ry="32" fill="#ffffff"/>
                
                <ellipse cx="1500" cy="130" rx="95" ry="48" fill="#ffffff"/>
                <ellipse cx="1580" cy="145" rx="85" ry="42" fill="#ffffff"/>
                <ellipse cx="1420" cy="150" rx="75" ry="38" fill="#ffffff"/>
            </g>
        </svg>

        <svg viewBox="0 0 1920 1080" preserveAspectRatio="none" class="clouds-layer clouds-medium">
            <defs>
                <filter id="cloudFilter2">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2.5"/>
                </filter>
            </defs>
            <!-- Segunda camada de nuvens - movimento médio -->
            <g filter="url(#cloudFilter2)" opacity="0.5">
                <ellipse cx="200" cy="200" rx="75" ry="38" fill="#ffffff"/>
                <ellipse cx="260" cy="215" rx="65" ry="32" fill="#ffffff"/>
                <ellipse cx="140" cy="220" rx="55" ry="27" fill="#ffffff"/>
                
                <ellipse cx="700" cy="180" rx="88" ry="44" fill="#ffffff"/>
                <ellipse cx="770" cy="195" rx="72" ry="36" fill="#ffffff"/>
                <ellipse cx="630" cy="200" rx="68" ry="34" fill="#ffffff"/>
                
                <ellipse cx="1200" cy="190" rx="82" ry="41" fill="#ffffff"/>
                <ellipse cx="1270" cy="205" rx="78" ry="39" fill="#ffffff"/>
                <ellipse cx="1130" cy="210" rx="62" ry="31" fill="#ffffff"/>
                
                <ellipse cx="1700" cy="175" rx="92" ry="46" fill="#ffffff"/>
                <ellipse cx="1780" cy="190" rx="82" ry="41" fill="#ffffff"/>
                <ellipse cx="1620" cy="195" rx="72" ry="36" fill="#ffffff"/>
            </g>
        </svg>

        <svg viewBox="0 0 1920 1080" preserveAspectRatio="none" class="clouds-layer clouds-fast">
            <defs>
                <filter id="cloudFilter3">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="3"/>
                </filter>
            </defs>
            <!-- Terceira camada de nuvens - movimento rápido (mais distante) -->
            <g filter="url(#cloudFilter3)" opacity="0.3">
                <ellipse cx="150" cy="250" rx="70" ry="35" fill="#ffffff"/>
                <ellipse cx="210" cy="265" rx="60" ry="30" fill="#ffffff"/>
                <ellipse cx="90" cy="270" rx="50" ry="25" fill="#ffffff"/>
                
                <ellipse cx="600" cy="230" rx="80" ry="40" fill="#ffffff"/>
                <ellipse cx="670" cy="245" rx="70" ry="35" fill="#ffffff"/>
                <ellipse cx="530" cy="250" rx="65" ry="32" fill="#ffffff"/>
                
                <ellipse cx="1100" cy="240" rx="78" ry="39" fill="#ffffff"/>
                <ellipse cx="1170" cy="255" rx="75" ry="37" fill="#ffffff"/>
                <ellipse cx="1030" cy="260" rx="60" ry="30" fill="#ffffff"/>
                
                <ellipse cx="1600" cy="225" rx="88" ry="44" fill="#ffffff"/>
                <ellipse cx="1680" cy="240" rx="80" ry="40" fill="#ffffff"/>
                <ellipse cx="1520" cy="245" rx="70" ry="35" fill="#ffffff"/>
            </g>
        </svg>
    </div>

    <main class="page">
        <header class="topbar shell">
            <a href="#" class="brand">RENE METEO</a>
            <div class="topbar-actions">
                <button id="themeToggle" class="btn link-btn" type="button">Tema</button>
                <button id="languageToggle" class="btn link-btn" type="button">EN</button>
                <button id="loginBtn" class="btn link-btn" type="button">Login</button>
                <button id="registerBtn" class="btn pill-btn" type="button">Registar</button>
                <button id="exportPdfBtn" class="btn pill-btn" type="button">Exportar PDF</button>
                <button id="logoutBtn" class="btn link-btn" type="button" style="display:none;">Logout</button>
            </div>
        </header>

        <section class="hero shell">
            <article class="hero-left">
                <h1 class="hero-title">Atmospheric<br>precision for your<br>journey.</h1>
                <p id="heroText" class="hero-text">Encontre previsões confiáveis e salve seus resultados de clima favoritos.</p>
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
                <p style="margin-top: 14px; font-size: 0.95rem; color: var(--muted);">
                    <a href="#" id="forgotPasswordLink" style="color: var(--blue); text-decoration: none;">Esqueceu a senha?</a>
                </p>
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

    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Recuperar senha</h2>
            <form id="forgotPasswordForm">
                <label>Email</label>
                <input type="email" name="email" required>
                <button class="btn pill-btn" type="submit">Enviar link</button>
            </form>
        </div>
    </div>

    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Redefinir senha</h2>
            <form id="resetPasswordForm">
                <label>Token de recuperação</label>
                <input type="text" name="token" id="resetTokenInput" required>
                <label>Nova senha</label>
                <input type="password" name="new_password" minlength="6" required>
                <button class="btn pill-btn" type="submit">Redefinir senha</button>
            </form>
        </div>
    </div>

    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/main.js"></script>
</body>
</html>
