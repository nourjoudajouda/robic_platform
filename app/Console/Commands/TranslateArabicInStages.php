<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateArabicInStages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:translate-arabic-stage {stage : Stage number (1-10)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate Arabic language in 10 stages (run stage 1, then 2, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stage = (int) $this->argument('stage');
        
        if ($stage < 1 || $stage > 10) {
            $this->error('Stage must be between 1 and 10!');
            return Command::FAILURE;
        }
        
        $this->info("Starting translation stage $stage/10...");
        
        $enJsonPath = resource_path('lang/en.json');
        $arJsonPath = resource_path('lang/ar.json');
        
        if (!File::exists($enJsonPath)) {
            $this->error('en.json file not found!');
            return Command::FAILURE;
        }
        
        // Read English JSON file
        $jsonContent = File::get($enJsonPath);
        $enData = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON in en.json file!');
            return Command::FAILURE;
        }
        
        $totalKeys = count($enData);
        $keysPerStage = ceil($totalKeys / 10);
        $startIndex = ($stage - 1) * $keysPerStage;
        $endIndex = min($startIndex + $keysPerStage, $totalKeys);
        
        $this->info("Processing keys $startIndex to $endIndex (out of $totalKeys total keys)...");
        
        // Load existing Arabic data if file exists
        $arData = [];
        if (File::exists($arJsonPath)) {
            $arJsonContent = File::get($arJsonPath);
            $arData = json_decode($arJsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $arData = [];
            }
        }
        
        $enKeys = array_keys($enData);
        $replacedCount = 0;
        $translatedCount = 0;
        
        // Process only the keys for this stage
        for ($i = $startIndex; $i < $endIndex; $i++) {
            if (!isset($enKeys[$i])) {
                break;
            }
            
            $key = $enKeys[$i];
            $value = $enData[$key];
            
            // Replace in key
            $newKey = $key;
            $newKey = str_ireplace('viser gold', 'robic', $newKey);
            $newKey = str_ireplace('visergold', 'robic', $newKey);
            $newKey = str_ireplace('gold', 'bean', $newKey);
            
            // Replace in value
            $newValue = $value;
            $newValue = str_ireplace('viser gold', 'robic', $newValue);
            $newValue = str_ireplace('visergold', 'robic', $newValue);
            $newValue = str_ireplace('gold', 'bean', $newValue);
            
            if ($key !== $newKey || $value !== $newValue) {
                $replacedCount++;
            }
            
            // Translate to Arabic using translation dictionary
            $translatedValue = $this->translateToArabic($newValue);
            if ($translatedValue !== $newValue) {
                $translatedCount++;
            }
            
            $arData[$newKey] = $translatedValue;
        }
        
        // Save the updated Arabic JSON
        $updatedJson = json_encode($arData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        File::put($arJsonPath, $updatedJson);
        
        $this->info("✓ Stage $stage/10 completed!");
        $this->info("✓ Replaced keywords in $replacedCount entries!");
        $this->info("✓ Translated $translatedCount entries to Arabic!");
        $this->info('✓ File saved: ' . $arJsonPath);
        $this->info('');
        
        if ($stage < 10) {
            $this->info("Next: Run 'php artisan language:translate-arabic-stage " . ($stage + 1) . "'");
        } else {
            $this->info('🎉 All stages completed! Translation finished.');
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Translate English text to Arabic
     */
    private function translateToArabic($text)
    {
        // Skip if already contains Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }
        
        // Load translation dictionary
        $translations = require resource_path('lang/translations_ar.php');
        
        // Check for exact match first (longest strings first)
        $sortedTranslations = $translations;
        uksort($sortedTranslations, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($sortedTranslations as $english => $arabic) {
            // Exact match
            if ($text === $english) {
                return $arabic;
            }
            // Case-insensitive exact match
            if (strcasecmp($text, $english) === 0) {
                return $arabic;
            }
        }
        
        // If no exact match, try to translate word by word for simple phrases
        // But only if the text is short (to avoid bad translations)
        if (strlen($text) < 100) {
            $translated = $text;
            $changed = false;
            
            // Sort by length (longest first) to match phrases before words
            foreach ($sortedTranslations as $english => $arabic) {
                // Skip single character translations to avoid over-translation
                if (strlen($english) <= 2 && strlen($text) > 2) {
                    continue;
                }
                
                // Try to find and replace the phrase
                $pattern = '/\b' . preg_quote($english, '/') . '\b/i';
                if (preg_match($pattern, $translated)) {
                    $translated = preg_replace($pattern, $arabic, $translated);
                    $changed = true;
                }
            }
            
            if ($changed) {
                return $translated;
            }
        }
        
        // If no translation found, return original text
        return $text;
    }
}
