<?php

return [
    'POST /api/auth/register' => ['AuthController', 'register'],
    'POST /api/auth/login' => ['AuthController', 'login'],
    'POST /api/auth/logout' => ['AuthController', 'logout'],
    'GET /api/auth/me' => ['AuthController', 'me'],
    'POST /api/auth/forgot-password' => ['AuthController', 'forgotPassword'],
    'POST /api/auth/reset-password' => ['AuthController', 'resetPassword'],

    'GET /api/weather/public-current' => ['WeatherController', 'publicCurrent'],
    'GET /api/weather/current' => ['WeatherController', 'fetchCurrent'],
    'GET /api/weather/records' => ['WeatherController', 'list'],
    'GET /api/reports/weather-pdf' => ['WeatherController', 'exportPdf'],
    'PUT /api/weather/records' => ['WeatherController', 'update'],
    'DELETE /api/weather/records' => ['WeatherController', 'delete'],
];
