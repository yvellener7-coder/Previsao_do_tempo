<?php

class OpenWeatherService
{
    private string $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

    private function fetchJson(string $url): array
    {
        $response = false;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 8,
            ]);
            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($response === false || $httpCode >= 400) {
                JsonResponse::send(['error' => 'Falha ao consultar OpenWeatherMap'], 502);
            }
        } else {
            $response = @file_get_contents($url);
            if ($response === false) {
                JsonResponse::send(['error' => 'Falha ao consultar OpenWeatherMap'], 502);
            }
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data) || !isset($data['main'], $data['weather'][0], $data['wind'])) {
            JsonResponse::send(['error' => 'Resposta inválida da OpenWeatherMap'], 502);
        }

        return $data;
    }

    public function currentByCity(string $city): array
    {
        $apiKey = getenv('OWM_API_KEY') ?: '';
        if ($apiKey === '') {
            JsonResponse::send(['error' => 'OWM_API_KEY não configurada no .env'], 500);
        }

        $url = sprintf(
            '%s?q=%s&appid=%s&units=metric&lang=pt_br',
            $this->baseUrl,
            rawurlencode($city),
            rawurlencode($apiKey)
        );

        return $this->fetchJson($url);
    }

    public function currentByCoordinates(float $lat, float $lon): array
    {
        $apiKey = getenv('OWM_API_KEY') ?: '';
        if ($apiKey === '') {
            JsonResponse::send(['error' => 'OWM_API_KEY não configurada no .env'], 500);
        }

        $url = sprintf(
            '%s?lat=%s&lon=%s&appid=%s&units=metric&lang=pt_br',
            $this->baseUrl,
            rawurlencode((string) $lat),
            rawurlencode((string) $lon),
            rawurlencode($apiKey)
        );

        return $this->fetchJson($url);
    }
}
