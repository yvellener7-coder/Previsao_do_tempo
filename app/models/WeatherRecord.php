<?php

class WeatherRecord
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, int $cityId, array $payload): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO weather_records
            (user_id, city_id, temperature_c, weather_main, weather_description, humidity, wind_speed, forecast_time)
            VALUES
            (:user_id, :city_id, :temperature_c, :weather_main, :weather_description, :humidity, :wind_speed, :forecast_time)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'city_id' => $cityId,
            'temperature_c' => $payload['temperature_c'],
            'weather_main' => $payload['weather_main'],
            'weather_description' => $payload['weather_description'],
            'humidity' => $payload['humidity'],
            'wind_speed' => $payload['wind_speed'],
            'forecast_time' => $payload['forecast_time'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT wr.id, wr.temperature_c, wr.weather_main, wr.weather_description, wr.humidity, wr.wind_speed, wr.forecast_time, wr.created_at,
                    c.name AS city_name, c.country_code
             FROM weather_records wr
             INNER JOIN cities c ON c.id = wr.city_id
             WHERE wr.user_id = :user_id
             ORDER BY wr.id DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function updateByUser(int $id, int $userId, array $payload): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE weather_records
             SET temperature_c = :temperature_c,
                 weather_main = :weather_main,
                 weather_description = :weather_description,
                 humidity = :humidity,
                 wind_speed = :wind_speed,
                 forecast_time = :forecast_time
             WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'temperature_c' => $payload['temperature_c'],
            'weather_main' => $payload['weather_main'],
            'weather_description' => $payload['weather_description'],
            'humidity' => $payload['humidity'],
            'wind_speed' => $payload['wind_speed'],
            'forecast_time' => $payload['forecast_time'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteByUser(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM weather_records WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
