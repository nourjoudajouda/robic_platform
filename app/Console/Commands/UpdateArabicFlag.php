<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Language;
use Illuminate\Support\Facades\File;

class UpdateArabicFlag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:update-arabic-flag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Arabic language flag image';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating Arabic language flag image...');

        $language = Language::where('code', 'ar')->first();
        
        if (!$language) {
            $this->error('Arabic language not found in database!');
            $this->info('Please run: php artisan language:add-arabic');
            return Command::FAILURE;
        }

        // Create language directory if it doesn't exist
        $languageDir = public_path('assets/images/language');
        if (!File::isDirectory($languageDir)) {
            File::makeDirectory($languageDir, 0755, true);
        }

        // Create a simple Arabic flag placeholder image
        $imageFileName = 'ar_flag.png';
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

            // Delete old image if it exists and is different
            if ($language->image && $language->image != $imageFileName && File::exists($languageDir . '/' . $language->image)) {
                // Keep the old image for now, just update the reference
            }

            $language->image = $imageFileName;
            $language->save();

            $this->info('Arabic flag image created and updated successfully!');
            $this->info('Image path: ' . $imagePath);
            return Command::SUCCESS;
        } else {
            $this->error('GD library not available. Cannot create flag image.');
            $this->info('Please upload an Arabic flag image manually from admin panel.');
            return Command::FAILURE;
        }
    }
}

