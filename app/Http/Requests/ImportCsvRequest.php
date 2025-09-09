<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class ImportCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:csv,txt',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'csv_file.required' => 'Un fichier CSV est requis.',
            'csv_file.file' => "Le fichier téléchargé n'est pas valide.",
            'csv_file.mimes' => 'Le fichier doit être au format CSV.',
            'csv_file.max' => 'Le fichier ne peut pas dépasser 10 Mo.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('csv_file');

            // Read and validate the header line
            $resource = fopen($file->getRealPath(), 'rb');

            // Check if fopen errored
            if (! $resource) {
                $validator->errors()->add('csv_file', 'Impossible de lire le fichier CSV.');

                return;
            }

            $firstLine = fgets($resource);
            if (! $firstLine) {
                $validator->errors()->add('csv_file', 'Le fichier CSV est vide.');
                fclose($resource);

                return;
            }

            // Check the column count first
            $columns = explode(';', trim($firstLine));
            if (count($columns) !== 4) {
                $validator->errors()->add('csv_file',
                    'Le fichier CSV doit contenir exactement 4 colonnes séparées par des points-virgules. Trouvé : '.count($columns).' colonnes.');
                fclose($resource);

                return;
            }

            // Skip header validation for now - just check column count
            // TODO: Add back header name validation later

            // Count total lines for row limit validation
            $lineCount = 1; // Already read the first line
            while (fgets($resource) !== false) {
                $lineCount++;
            }
            fclose($resource);

            // Check row count
            if ($lineCount > 1000) {
                $validator->errors()->add('csv_file',
                    "Le fichier CSV ne peut pas contenir plus de 1000 lignes. Trouvé : {$lineCount} lignes de données.");

                return;
            }

            // Check minimum rows
            if ($lineCount < 2) {
                $validator->errors()->add('csv_file',
                    "Le fichier CSV doit contenir au moins une ligne de données en plus de l'en-tête.");

                return;
            }
        });
    }

    /**
     * Validate CSV file structure and content
     */
    private function validateCsvStructure($validator, $file): void
    {
        try {
            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                $validator->errors()->add('csv_file', 'Impossible de lire le fichier CSV.');

                return;
            }

            $lines = explode("\n", $content);

            // Check if the file has at least a header + 1 data row
            if (count($lines) < 2) {
                $validator->errors()->add('csv_file',
                    "Le fichier CSV doit contenir au moins une ligne de données en plus de l'en-tête.");

                return;
            }

            // Check row count
            if ($lines > 1000) {
                $validator->errors()->add('csv_file', 'Le fichier CSV ne peut pas contenir plus de 1000 lignes.');

                return;
            }

            // Simple header check
            $headerLine = trim($lines[0]);
            $headerParts = explode(';', $headerLine);

            if (count($headerParts) !== 4) {
                $validator->errors()->add('csv_file', 'Le fichier CSV doit contenir exactement 4 colonnes.');

                return;
            }

            // Basic header validation (case-insensitive, accent insensitive)
            $normalizedHeaders = array_map(static function ($header) {
                $header = strtolower(trim($header));

                return str_replace(['é', 'è', 'ê', 'ë'], 'e', $header);
            }, $headerParts);

            $requiredHeaders = ['name', 'description', 'date', 'tags'];
            $missing = array_diff($requiredHeaders, $normalizedHeaders);

            if (! empty($missing)) {
                $validator->errors()->add('csv_file',
                    'En-têtes manquantes ou incorrectes. Attendu : Nom;Description;Date;Tags');

                return;
            }

        } catch (\Exception $e) {
            $validator->errors()->add('csv_file', 'Erreur lors de la lecture du fichier CSV.');
        }
    }

    /**
     * Validate date format
     */
    private function isValidDate($date): bool
    {
        $format = 'Y-m-d';
        $dateTime = \DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) === $date;
    }
}
