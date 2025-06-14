<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratBaptisGenerate
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function generateFromTemplate($templatePath, $outputPath, array $data, $tandaTangan1, $tandaTangan2, $tandaTangan3)
    {
        try {
            // Load template
            $templateProcessor = new TemplateProcessor($templatePath);
            
            if ($tandaTangan1 != null) {
                $templateProcessor->setImageValue('ttd_ortu', [
                    'path' => $tandaTangan1,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            if ($tandaTangan2 != null) {
                $templateProcessor->setImageValue('ttd_ketua', [
                    'path' => $tandaTangan2,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            if ($tandaTangan3 != null) {
                $templateProcessor->setImageValue('ttd_pastor', [
                    'path' => $tandaTangan3,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            // Replace variables in template
            foreach ($data as $key => $value) {
                $templateProcessor->setValue($key, $value);
            }
            
            // Save generated document
            $templateProcessor->saveAs($outputPath);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error generating document: ' . $e->getMessage());
            return false;
        }
    }
}
