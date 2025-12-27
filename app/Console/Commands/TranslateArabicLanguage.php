<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateArabicLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:translate-arabic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace gold with bean, visergold with robic, and translate all English text to Arabic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting translation process...');
        
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
        
        $this->info('Processing ' . count($enData) . ' language keys...');
        
        $newArData = [];
        $replacedCount = 0;
        $translatedCount = 0;
        
        // Process each key-value pair
        foreach ($enData as $key => $value) {
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
            
            $newArData[$newKey] = $translatedValue;
        }
        
        // Save the updated Arabic JSON
        $updatedJson = json_encode($newArData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        File::put($arJsonPath, $updatedJson);
        
        $this->info("✓ Successfully replaced keywords in $replacedCount entries!");
        $this->info("✓ Translated $translatedCount entries to Arabic!");
        $this->info('✓ File saved: ' . $arJsonPath);
        $this->info('');
        $this->info('Note: Some translations may need manual review.');
        $this->info('You can continue translating remaining keys from Admin Panel > Language Manager > Translate (Arabic)');
        
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
