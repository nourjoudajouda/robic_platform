<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Language;
use App\Constants\Status;
use Illuminate\Support\Facades\File;

class AddEnglishLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:add-english';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add English language to the system and set it as default';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Adding English language to the system...');

        // Check if English language already exists
        $existingLang = Language::where('code', 'en')->first();
        
        if ($existingLang) {
            $this->warn('English language already exists in the database!');
            $this->info('Language ID: ' . $existingLang->id);
            $this->info('Language Name: ' . $existingLang->name);
            $this->info('Language Code: ' . $existingLang->code);
            $this->info('Is Default: ' . ($existingLang->is_default == Status::YES ? 'Yes' : 'No'));
            
            // Set as default if not already
            if ($existingLang->is_default != Status::YES) {
                // Remove default from other languages
                Language::where('is_default', Status::YES)->update(['is_default' => Status::NO]);
                $existingLang->is_default = Status::YES;
                $existingLang->save();
                $this->info('English language has been set as default!');
            } else {
                $this->info('English language is already set as default.');
            }
            
            return Command::SUCCESS;
        }

        // Remove default from other languages
        Language::where('is_default', Status::YES)->update(['is_default' => Status::NO]);

        // Create new English language record
        $language = new Language();
        $language->name = 'English';
        $language->code = 'en';
        $language->is_default = Status::YES; // Set as default
        
        // Create language directory if it doesn't exist
        $languageDir = public_path('assets/images/language');
        if (!File::isDirectory($languageDir)) {
            File::makeDirectory($languageDir, 0755, true);
        }

        // Create a simple English flag placeholder image
        $imageFileName = 'en_flag.png';
        $imagePath = $languageDir . '/' . $imageFileName;

        // Create a simple flag image using GD (50x50 as per FileInfo)
        if (function_exists('imagecreatetruecolor') && !File::exists($imagePath)) {
            $img = imagecreatetruecolor(50, 50);

            // English/UK flag colors (Union Jack simplified)
            $blue = imagecolorallocate($img, 0, 35, 149);
            $red = imagecolorallocate($img, 207, 20, 43);
            $white = imagecolorallocate($img, 255, 255, 255);

            // Fill with blue background
            imagefilledrectangle($img, 0, 0, 50, 50, $blue);
            
            // Add white cross
            imagefilledrectangle($img, 20, 0, 30, 50, $white);
            imagefilledrectangle($img, 0, 20, 50, 30, $white);
            
            // Add red cross
            imagefilledrectangle($img, 22, 0, 28, 50, $red);
            imagefilledrectangle($img, 0, 22, 50, 28, $red);

            imagepng($img, $imagePath);
            imagedestroy($img);

            $language->image = $imageFileName;
            $this->info('English flag image created successfully!');
        } else {
            // Use existing image or default
            if (File::exists($imagePath)) {
                $language->image = $imageFileName;
            } else {
                $language->image = 'default.png';
                $this->warn('GD library not available or image exists. Using default image. Please upload an English flag image from admin panel.');
            }
        }
        
        $language->save();
        $this->info('English language added to database successfully!');
        $this->info('English language has been set as default!');

        $this->info('');
        $this->info('✓ English language has been added successfully!');
        
        return Command::SUCCESS;
    }
}

