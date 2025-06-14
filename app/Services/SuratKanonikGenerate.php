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
            // dd($tandaTangan1, $tandaTangan2, $tandaTangan3, $tandaTangan4, $tandaTangan5);
            if ($tandaTangan1 != null) {
                $templateProcessor->setImageValue('ttd_calon_suami', [
                    'path' => $tandaTangan1,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            if ($tandaTangan2 != null) {
                $templateProcessor->setImageValue('ttd_calon_istri', [
                    'path' => $tandaTangan2,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            if ($tandaTangan3 != null) {
                $templateProcessor->setImageValue('ttd_ketua_suami', [
                    'path' => $tandaTangan3,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            if ($tandaTangan4 != null) {
                $templateProcessor->setImageValue('ttd_ketua_istri', [
                    'path' => $tandaTangan4,
                    'width' => 100,
                    'height' => 70,
                    'ratio' => false,
                ]);
            }
            
            if ($tandaTangan5 != null) {
                $templateProcessor->setImageValue('ttd_pastor', [
                    'path' => $tandaTangan5,
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
