<?php

namespace App\Http\Requests;

use DateTime;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportCsvRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return Auth::user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
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
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        $file = SimpleExcelReader::create($this->file('csv_file'), 'csv')->useDelimiter(';');

        return [
            function (Validator $validator) use ($file) {
                $headers = $file->getHeaders();

                if ($headers !== ['Nom', 'Description', 'Date', 'Tags']) {
                    $validator->errors()->add('csv_file',
                        'En-têtes manquantes ou incorrectes. Attendu : Nom;Description;Date;Tags');
                }
            },
            function (Validator $validator) use ($file) {
                $rowCount = $file->getRows()->count(); // ->getRows() skips the headers

                if ($rowCount < 1) {
                    $validator->errors()->add('csv_file',
                        "Le fichier CSV doit contenir au moins une ligne de données en plus de l'en-tête.");
                }

                if ($rowCount > 1000) {
                    $validator->errors()->add('csv_file',
                        "Le fichier CSV ne peut pas contenir plus de 1000 lignes de données. Trouvé : {$rowCount} lignes de données.");
                }
            },
            function (Validator $validator) use ($file) {
                $rowNb = 2; // ->getRows() skips the headers

                $file->getRows()->each(function ($row) use (&$rowNb, $validator) {
                    if (! $row['Nom'] || ! $row['Date']) {
                        $validator->errors()->add('csv_file',
                            "La ligne {$rowNb} n'a pas les colonnes requises (Nom et Date sont obligatoires).");
                    } elseif (! $this->isValidDate($row['Date'])) {
                        $validator->errors()->add('csv_file', "La date de la ligne {$rowNb} n'est pas valide.");
                    }

                    $rowNb++;
                });
            },
        ];
    }

    /**
     * Validate date format
     */
    private function isValidDate($date): bool
    {
        $format = 'Y-m-d';
        $dateTime = DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) === $date;
    }
}
