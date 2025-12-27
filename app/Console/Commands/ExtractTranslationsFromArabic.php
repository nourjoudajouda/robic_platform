<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtractTranslationsFromArabic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:extract-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract translated words from ar.json and add them to translations_ar.php';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting extraction process...');
        
        $enJsonPath = resource_path('lang/en.json');
        $arJsonPath = resource_path('lang/ar.json');
        $translationsPath = resource_path('lang/translations_ar.php');
        
        if (!File::exists($enJsonPath) || !File::exists($arJsonPath)) {
            $this->error('en.json or ar.json file not found!');
            return Command::FAILURE;
        }
        
        // Read JSON files
        $enData = json_decode(File::get($enJsonPath), true);
        $arData = json_decode(File::get($arJsonPath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON in language files!');
            return Command::FAILURE;
        }
        
        // Load existing translations
        $existingTranslations = [];
        if (File::exists($translationsPath)) {
            $existingTranslations = require $translationsPath;
        }
        
        $newTranslations = [];
        $addedCount = 0;
        $updatedCount = 0;
        
        // Extract fully translated entries (containing Arabic characters)
        foreach ($enData as $enKey => $enValue) {
            // Find corresponding Arabic value
            $arValue = $arData[$enKey] ?? null;
            
            if ($arValue && $this->containsArabic($arValue)) {
                // Check if it's a new translation or update
                if (!isset($existingTranslations[$enValue])) {
                    $newTranslations[$enValue] = $arValue;
                    $addedCount++;
                } elseif ($existingTranslations[$enValue] !== $arValue) {
                    // Update if different
                    $newTranslations[$enValue] = $arValue;
                    $updatedCount++;
                }
            }
        }
        
        // Merge with existing translations (new ones take priority)
        $allTranslations = array_merge($existingTranslations, $newTranslations);
        
        // Sort by key length (longest first) for better matching
        uksort($allTranslations, function($a, $b) {
            $lenDiff = strlen($b) - strlen($a);
            if ($lenDiff !== 0) {
                return $lenDiff;
            }
            return strcmp($a, $b);
        });
        
        // Generate PHP file content
        $phpContent = "<?php\n\n";
        $phpContent .= "/**\n";
        $phpContent .= " * Arabic Translations Dictionary\n";
        $phpContent .= " * This file contains common English to Arabic translations\n";
        $phpContent .= " * Extracted from ar.json on " . date('Y-m-d H:i:s') . "\n";
        $phpContent .= " */\n\n";
        $phpContent .= "return [\n";
        
        // Group translations by category
        $categories = [
            'Basic words' => [],
            'Common phrases' => [],
            'Orders and Transactions' => [],
            'Shipping' => [],
            'Balance and Wallet' => [],
            'Bean related' => [],
            'Robic related' => [],
            'Admin Panel Specific' => [],
            'Other' => []
        ];
        
        foreach ($allTranslations as $en => $ar) {
            $category = 'Other';
            
            // Categorize
            if (in_array(strtolower($en), ['name', 'email', 'username', 'password', 'submit', 'update', 'edit', 'delete', 'close', 'save changes', 'cancel', 'yes', 'no', 'search', 'filter'])) {
                $category = 'Basic words';
            } elseif (strpos(strtolower($en), 'bean') !== false || strpos(strtolower($en), 'coffee') !== false) {
                $category = 'Bean related';
            } elseif (strpos(strtolower($en), 'robic') !== false) {
                $category = 'Robic related';
            } elseif (strpos(strtolower($en), 'shipping') !== false || strpos(strtolower($en), 'delivery') !== false) {
                $category = 'Shipping';
            } elseif (strpos(strtolower($en), 'balance') !== false || strpos(strtolower($en), 'deposit') !== false || strpos(strtolower($en), 'withdraw') !== false) {
                $category = 'Balance and Wallet';
            } elseif (strpos(strtolower($en), 'order') !== false || strpos(strtolower($en), 'transaction') !== false || strpos(strtolower($en), 'buy') !== false || strpos(strtolower($en), 'sell') !== false) {
                $category = 'Orders and Transactions';
            } elseif (strpos(strtolower($en), 'admin') !== false || strpos(strtolower($en), 'role') !== false) {
                $category = 'Admin Panel Specific';
            } else {
                $category = 'Common phrases';
            }
            
            $categories[$category][$en] = $ar;
        }
        
        // Write categorized translations
        foreach ($categories as $categoryName => $translations) {
            if (empty($translations)) {
                continue;
            }
            
            $phpContent .= "    // $categoryName\n";
            foreach ($translations as $en => $ar) {
                $enEscaped = addslashes($en);
                $arEscaped = addslashes($ar);
                $phpContent .= "    '$enEscaped' => '$arEscaped',\n";
            }
            $phpContent .= "\n";
        }
        
        $phpContent .= "];\n";
        
        // Save the file
        File::put($translationsPath, $phpContent);
        
        $this->info("✓ Extracted $addedCount new translations!");
        $this->info("✓ Updated $updatedCount existing translations!");
        $this->info("✓ Total translations in dictionary: " . count($allTranslations));
        $this->info('✓ File saved: ' . $translationsPath);
        
        return Command::SUCCESS;
    }
    
    /**
     * Check if string contains Arabic characters
     */
    private function containsArabic($text)
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }
}
