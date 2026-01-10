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
        
        // Create language directory if it doesn't exist
        $languageDir = public_path('assets/images/language');
        if (!File::isDirectory($languageDir)) {
            File::makeDirectory($languageDir, 0755, true);
        }
        
        // Create a simple Arabic flag placeholder image
        $imageFileName = 'ar_flag_' . time() . '.png';
        $imagePath = $languageDir . '/' . $imageFileName;
        
        // Create a simple flag image using GD (50x50 as per FileInfo)
        if (function_exists('imagecreatetruecolor')) {
            $img = imagecreatetruecolor(50, 50);
            
            // Arabic flag colors: black, red, white, green
            $black = imagecolorallocate($img, 0, 0, 0);
            $red = imagecolorallocate($img, 206, 17, 38);
            $white = imagecolorallocate($img, 255, 255, 255);
            $green = imagecolorallocate($img, 0, 122, 61);
            
            // Create horizontal stripes (simplified version)
            imagefilledrectangle($img, 0, 0, 50, 12, $black);   // Black stripe
            imagefilledrectangle($img, 0, 13, 50, 25, $white);  // White stripe
            imagefilledrectangle($img, 0, 26, 50, 37, $green);  // Green stripe
            imagefilledrectangle($img, 0, 38, 50, 50, $red);     // Red stripe
            
            imagepng($img, $imagePath);
            imagedestroy($img);
            
            $language->image = $imageFileName;
            $this->info('Arabic flag image created successfully!');
        } else {
            // Fallback if GD is not available
            $language->image = 'default.png';
            $this->warn('GD library not available. Using default image. Please upload an Arabic flag image from admin panel.');
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
