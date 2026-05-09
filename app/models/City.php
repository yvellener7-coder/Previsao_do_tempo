<?php

class City
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findOrCreate(string $name, string $countryCode, float $lat, float $lon): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM cities WHERE name = :name AND country_code = :country_code LIMIT 1');
        $stmt->execute([
            'name' => $name,
            'country_code' => strtoupper($countryCode),
        ]);

        $existing = $stmt->fetch();
        if ($existing) {
            return (int) $existing['id'];
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO cities (name, country_code, lat, lon) VALUES (:name, :country_code, :lat, :lon)'
        );
        $insert->execute([
            'name' => $name,
            'country_code' => strtoupper($countryCode),
            'lat' => $lat,
            'lon' => $lon,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
