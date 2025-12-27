<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Language;
use App\Constants\Status;
use Illuminate\Support\Facades\File;

class AddArabicLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:add-arabic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Arabic language to the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Adding Arabic language to the system...');

        // Check if Arabic language already exists
        $existingLang = Language::where('code', 'ar')->first();
        
        if ($existingLang) {
            $this->warn('Arabic language already exists in the database!');
            $this->info('Language ID: ' . $existingLang->id);
            $this->info('Language Name: ' . $existingLang->name);
            $this->info('Language Code: ' . $existingLang->code);
            
            // Check if ar.json file exists and has all keys
            $arJsonPath = resource_path('lang/ar.json');
            $enJsonPath = resource_path('lang/en.json');
            
            if (File::exists($enJsonPath)) {
                $enData = json_decode(File::get($enJsonPath), true);
                $arData = File::exists($arJsonPath) ? json_decode(File::get($arJsonPath), true) : [];
                
                if (count($arData) < count($enData)) {
                    $this->info('Updating ar.json with missing keys from en.json...');
                    $merged = array_merge($enData, $arData);
                    File::put($arJsonPath, json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    $this->info('ar.json updated successfully!');
                } else {
                    $this->info('ar.json is up to date.');
                }
            }
            
            return Command::SUCCESS;
        }

        // Create new Arabic language record
        $language = new Language();
        $language->name = 'Arabic';
        $language->code = 'ar';
        $language->is_default = Status::NO;
        $language->image = 'default.png'; // Default image, can be updated later from admin panel
        
        // Check if default image exists, if not create a placeholder
        $imagePath = public_path('assets/images/language/default.png');
        if (!File::exists($imagePath)) {
            $languageDir = public_path('assets/images/language');
            if (!File::isDirectory($languageDir)) {
                File::makeDirectory($languageDir, 0755, true);
            }
            // Create a simple placeholder or copy from another language
            $this->warn('Default language image not found. Please upload an image from admin panel.');
        }
        
        $language->save();
        $this->info('Arabic language added to database successfully!');

        // Copy all keys from en.json to ar.json
        $enJsonPath = resource_path('lang/en.json');
        $arJsonPath = resource_path('lang/ar.json');
        
        if (File::exists($enJsonPath)) {
            $enData = json_decode(File::get($enJsonPath), true);
            
            // If ar.json exists, merge with existing translations
            if (File::exists($arJsonPath)) {
                $arData = json_decode(File::get($arJsonPath), true);
                // Merge: keep existing Arabic translations, add missing keys from English
                $merged = array_merge($enData, $arData);
                File::put($arJsonPath, json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $this->info('ar.json updated with all keys from en.json!');
            } else {
                // Create new ar.json with all keys from en.json
                File::put($arJsonPath, json_encode($enData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $this->info('ar.json created with all keys from en.json!');
            }
            
            $this->info('Total keys in ar.json: ' . count(json_decode(File::get($arJsonPath), true)));
        } else {
            $this->error('en.json file not found!');
            return Command::FAILURE;
        }

        $this->info('');
        $this->info('✓ Arabic language has been added successfully!');
        $this->info('You can now:');
        $this->info('  1. Go to Admin Panel > Language Manager');
        $this->info('  2. Upload an Arabic flag image');
        $this->info('  3. Translate all keys from the Translate page');
        $this->info('  4. Set it as default language if needed');
        
        return Command::SUCCESS;
    }
}
