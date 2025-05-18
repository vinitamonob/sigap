<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratKematianGenerate
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function generateFromTemplate($templatePath, $outputPath, array $data, $tandaTangan1)
    {
        try {
            // Load template
            $templateProcessor = new TemplateProcessor($templatePath);
            
            $templateProcessor->setImageValue('ttd_ketua', [
                'path' => $tandaTangan1,
                'width' => 100,
                'height' => 70,
                'ratio' => false,
            ]);
            
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