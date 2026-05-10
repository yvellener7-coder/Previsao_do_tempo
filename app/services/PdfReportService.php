<?php

class PdfReportService
{
    public function generateReport(array $records, string $userName): string
    {
        $title = 'RENE METEO - Relatório de Previsões';
        $generatedAt = date('d/m/Y H:i');

        $lines = [];
        $lines[] = $title;
        $lines[] = "Usuário: {$userName}";
        $lines[] = "Gerado em: {$generatedAt}";
        $lines[] = '';
        $lines[] = 'Histórico de previsões:';
        $lines[] = '';

        if (count($records) === 0) {
            $lines[] = 'Nenhum registro encontrado.';
        } else {
            foreach ($records as $record) {
                $lines[] = sprintf(
                    '%s (%s) - %s: %s | Humidity %s%% | Wind %s m/s | %s',
                    $record['city_name'],
                    $record['country_code'],
                    $record['weather_main'],
                    $record['weather_description'],
                    $record['humidity'],
                    $record['wind_speed'],
                    $record['forecast_time']
                );
            }
        }

        return $this->buildPdf($lines);
    }

    private function escapeText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function buildPdf(array $lines): string
    {
        $stream = '';
        $y = 760;

        foreach ($lines as $line) {
            $escaped = $this->escapeText($line);
            $stream .= "BT /F1 12 Tf 50 {$y} Td ({$escaped}) Tj ET\n";
            $y -= 16;
            if ($y < 40) {
                break;
            }
        }

        $stream = trim($stream, "\n");
        $streamLength = strlen($stream);

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length {$streamLength} >>\nstream\n{$stream}\nendstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $startXref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf('%010d 00000 n \n', $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$startXref}\n%%EOF";

        return $pdf;
    }
}
