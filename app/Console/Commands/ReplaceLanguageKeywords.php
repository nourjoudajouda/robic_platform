<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReplaceLanguageKeywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:replace-keywords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace gold with bean, visergold with robic, and viser gold with robic in language files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting keyword replacement...');
        
        $enJsonPath = resource_path('lang/en.json');
        
        if (!File::exists($enJsonPath)) {
            $this->error('en.json file not found!');
            return Command::FAILURE;
        }
        
        // Read the JSON file
        $jsonContent = File::get($enJsonPath);
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON in en.json file!');
            return Command::FAILURE;
        }
        
        $this->info('Processing ' . count($data) . ' language keys...');
        
        $replacedCount = 0;
        $newData = [];
        
        // Process each key-value pair
        foreach ($data as $key => $value) {
            $newKey = $key;
            $newValue = $value;
            
            // Replace in key
            $originalKey = $newKey;
            $newKey = str_ireplace('viser gold', 'robic', $newKey);
            $newKey = str_ireplace('visergold', 'robic', $newKey);
            $newKey = str_ireplace('gold', 'bean', $newKey);
            
            // Replace in value
            $originalValue = $newValue;
            $newValue = str_ireplace('viser gold', 'robic', $newValue);
            $newValue = str_ireplace('visergold', 'robic', $newValue);
            $newValue = str_ireplace('gold', 'bean', $newValue);
            
            if ($originalKey !== $newKey || $originalValue !== $newValue) {
                $replacedCount++;
            }
            
            $newData[$newKey] = $newValue;
        }
        
        // Save the updated JSON
        $updatedJson = json_encode($newData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        File::put($enJsonPath, $updatedJson);
        
        $this->info("✓ Successfully replaced keywords in $replacedCount entries!");
        $this->info('✓ File saved: ' . $enJsonPath);
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Go to Admin Panel > Language Manager');
        $this->info('2. Click "Translate" on Arabic language');
        $this->info('3. Click "Import Keywords" and select "System" or "English" to import all updated keys');
        
        return Command::SUCCESS;
    }
}
