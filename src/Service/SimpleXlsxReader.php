<?php

namespace App\Service;

final class SimpleXlsxReader
{
    public function readSheetRows(string $path, string $sheetName): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException(sprintf('Impossible d’ouvrir %s', $path));
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetTarget = $this->resolveSheetTarget($zip, $sheetName);
        $sheetXml = $zip->getFromName($sheetTarget);
        $zip->close();

        if (!is_string($sheetXml) || $sheetXml === '') {
            throw new \RuntimeException(sprintf('Feuille introuvable : %s', $sheetName));
        }

        return $this->parseSheetRows($sheetXml, $sharedStrings);
    }

    private function readSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if (!is_string($xml) || $xml === '') {
            return [];
        }

        $doc = new \SimpleXMLElement($xml);
        $strings = [];
        foreach ($doc->si as $si) {
            $text = '';
            if (isset($si->t)) {
                $text = (string) $si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $run) {
                    $text .= (string) $run->t;
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function resolveSheetTarget(\ZipArchive $zip, string $sheetName): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if (!is_string($workbookXml) || !is_string($relsXml)) {
            throw new \RuntimeException('Classeur XLSX incomplet.');
        }

        $workbook = new \SimpleXMLElement($workbookXml);
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $relationshipId = null;
        foreach ($workbook->sheets->sheet as $sheet) {
            if ((string) $sheet['name'] === $sheetName) {
                $attributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $relationshipId = (string) $attributes['id'];
                break;
            }
        }

        if ($relationshipId === null || $relationshipId === '') {
            throw new \RuntimeException(sprintf('Feuille %s absente du classeur.', $sheetName));
        }

        $rels = new \SimpleXMLElement($relsXml);
        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = ltrim((string) $relationship['Target'], '/');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        throw new \RuntimeException(sprintf('Relation de feuille introuvable : %s', $sheetName));
    }

    private function parseSheetRows(string $sheetXml, array $sharedStrings): array
    {
        $sheet = new \SimpleXMLElement($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->columnIndexFromReference($reference);
                $type = (string) $cell['t'];
                $value = '';

                if ($type === 'inlineStr') {
                    $value = isset($cell->is->t) ? (string) $cell->is->t : '';
                } else {
                    $raw = isset($cell->v) ? (string) $cell->v : '';
                    if ($type === 's' && $raw !== '') {
                        $value = $sharedStrings[(int) $raw] ?? '';
                    } else {
                        $value = $raw;
                    }
                }

                $cells[$columnIndex] = trim($value);
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);
            $max = max(array_keys($cells));
            $normalized = [];
            for ($i = 0; $i <= $max; ++$i) {
                $normalized[] = $cells[$i] ?? '';
            }
            $rows[] = $normalized;
        }

        return $rows;
    }

    private function columnIndexFromReference(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }
}
