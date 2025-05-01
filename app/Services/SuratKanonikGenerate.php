<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratKanonikGenerate
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function generateFromTemplate($templatePath, $outputPath, array $data, $tandaTangan1, $tandaTangan2, $tandaTangan3, $tandaTangan4, $tandaTangan5)
    {
        try {
            // Load template
            $templateProcessor = new TemplateProcessor($templatePath);
            
            // $templateProcessor->setImageValue('tanda_tangan', [
            //     'path' => $tandaTangan,
            //     'width' => 100,
            //     'height' => 100,
            //     'ratio' => false,
            // ]);
            
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
