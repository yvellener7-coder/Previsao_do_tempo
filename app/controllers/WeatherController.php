<?php

class WeatherController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function fetchCurrent(): void
    {
        $userId = Auth::requireUserId();
        $city = trim((string) ($_GET['city'] ?? ''));
        if ($city === '') {
            JsonResponse::send(['error' => 'Parâmetro city é obrigatório'], 422);
        }

        $service = new OpenWeatherService();
        $data = $service->currentByCity($city);

        $cityModel = new City($this->pdo);
        $recordModel = new WeatherRecord($this->pdo);

        $cityId = $cityModel->findOrCreate(
            (string) $data['name'],
            (string) ($data['sys']['country'] ?? 'PT'),
            (float) $data['coord']['lat'],
            (float) $data['coord']['lon']
        );

        $recordId = $recordModel->create($userId, $cityId, [
            'temperature_c' => (float) $data['main']['temp'],
            'weather_main' => (string) $data['weather'][0]['main'],
            'weather_description' => (string) $data['weather'][0]['description'],
            'humidity' => (int) $data['main']['humidity'],
            'wind_speed' => (float) $data['wind']['speed'],
            'forecast_time' => date('Y-m-d H:i:s'),
        ]);

        JsonResponse::send([
            'message' => 'Previsão obtida e guardada',
            'record_id' => $recordId,
            'weather' => $data,
        ]);
    }

    public function publicCurrent(): void
    {
        if (!isset($_GET['lat'], $_GET['lon'])) {
            JsonResponse::send(['error' => 'Parâmetros lat e lon são obrigatórios'], 422);
        }
        $lat = (float) $_GET['lat'];
        $lon = (float) $_GET['lon'];

        $service = new OpenWeatherService();
        $data = $service->currentByCoordinates($lat, $lon);

        JsonResponse::send(['weather' => $data]);
    }

    public function list(): void
    {
        $userId = Auth::requireUserId();
        $recordModel = new WeatherRecord($this->pdo);
        JsonResponse::send(['records' => $recordModel->listByUser($userId)]);
    }

    public function update(): void
    {
        $userId = Auth::requireUserId();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            JsonResponse::send(['error' => 'Parâmetro id inválido'], 422);
        }

        $body = Request::json();
        $required = ['temperature_c', 'weather_main', 'weather_description', 'humidity', 'wind_speed', 'forecast_time'];
        foreach ($required as $key) {
            if (!isset($body[$key])) {
                JsonResponse::send(['error' => "Campo obrigatório: $key"], 422);
            }
        }

        $recordModel = new WeatherRecord($this->pdo);
        $ok = $recordModel->updateByUser($id, $userId, $body);
        if (!$ok) {
            JsonResponse::send(['error' => 'Registo não encontrado'], 404);
        }

        JsonResponse::send(['message' => 'Registo atualizado']);
    }

    public function delete(): void
    {
        $userId = Auth::requireUserId();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            JsonResponse::send(['error' => 'Parâmetro id inválido'], 422);
        }

        $recordModel = new WeatherRecord($this->pdo);
        $ok = $recordModel->deleteByUser($id, $userId);
        if (!$ok) {
            JsonResponse::send(['error' => 'Registo não encontrado'], 404);
        }

        JsonResponse::send(['message' => 'Registo eliminado']);
    }
}
