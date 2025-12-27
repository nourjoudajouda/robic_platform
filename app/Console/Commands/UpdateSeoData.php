<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Frontend;

class UpdateSeoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:update-coffee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update SEO data to replace gold with coffee/bean';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $seo = Frontend::where('data_keys', 'seo.data')->first();
        
        // Copy coffee image to SEO folder if it doesn't exist
        $defaultCoffeeImage = 'coffee-seo-default.png';
        $seoImagePath = public_path('assets/images/seo/' . $defaultCoffeeImage);
        $sourceImagePath = public_path('assets/images/coffee-beans-pattern.png');
        
        if (!file_exists($seoImagePath) && file_exists($sourceImagePath)) {
            $seoDir = public_path('assets/images/seo');
            if (!is_dir($seoDir)) {
                mkdir($seoDir, 0755, true);
            }
            copy($sourceImagePath, $seoImagePath);
            $this->info('Coffee image copied to SEO folder.');
        }
        
        if (!$seo) {
            $this->info('Creating new SEO data...');
            $dataValues = [
                'keywords' => ['coffee', 'bean', 'green coffee', 'coffee beans', 'coffee trading'],
                'description' => 'Buy and sell green coffee beans online. Trade coffee beans with secure transactions.',
                'social_title' => 'Coffee Bean Trading Platform',
                'social_description' => 'Buy and sell green coffee beans online. Trade coffee beans with secure transactions.',
                'image' => file_exists($seoImagePath) ? $defaultCoffeeImage : null
            ];
            
            $frontend = new Frontend();
            $frontend->data_keys = 'seo.data';
            $frontend->data_values = $dataValues;
            $frontend->save();
            
            $this->info('SEO data created successfully!');
        } else {
            $this->info('Updating existing SEO data...');
            $dataValues = $seo->data_values;
            
            if (is_object($dataValues)) {
                $dataValues = (array) $dataValues;
            }
            
            // Set default coffee image if no image exists or if current image doesn't exist
            if ((empty($dataValues['image']) || !file_exists(public_path('assets/images/seo/' . $dataValues['image']))) && file_exists($seoImagePath)) {
                $dataValues['image'] = $defaultCoffeeImage;
                $this->info('Default coffee image set.');
            }
            
            // Replace gold-related keywords with coffee/bean
            if (isset($dataValues['keywords']) && is_array($dataValues['keywords'])) {
                $dataValues['keywords'] = array_map(function($keyword) {
                    $keyword = str_ireplace('gold', 'coffee', $keyword);
                    $keyword = str_ireplace('ذهب', 'قهوة', $keyword);
                    return $keyword;
                }, $dataValues['keywords']);
                
                // Add coffee/bean keywords if not present
                $coffeeKeywords = ['coffee', 'bean', 'green coffee', 'coffee beans', 'coffee trading'];
                foreach ($coffeeKeywords as $coffeeKeyword) {
                    if (!in_array($coffeeKeyword, $dataValues['keywords'])) {
                        $dataValues['keywords'][] = $coffeeKeyword;
                    }
                }
            } else {
                $dataValues['keywords'] = ['coffee', 'bean', 'green coffee', 'coffee beans', 'coffee trading'];
            }
            
            // Replace gold in descriptions
            if (isset($dataValues['description'])) {
                $dataValues['description'] = str_ireplace('gold', 'coffee', $dataValues['description']);
                $dataValues['description'] = str_ireplace('ذهب', 'قهوة', $dataValues['description']);
            } else {
                $dataValues['description'] = 'Buy and sell green coffee beans online. Trade coffee beans with secure transactions.';
            }
            
            if (isset($dataValues['social_title'])) {
                $dataValues['social_title'] = str_ireplace('gold', 'coffee', $dataValues['social_title']);
                $dataValues['social_title'] = str_ireplace('ذهب', 'قهوة', $dataValues['social_title']);
            } else {
                $dataValues['social_title'] = 'Coffee Bean Trading Platform';
            }
            
            if (isset($dataValues['social_description'])) {
                $dataValues['social_description'] = str_ireplace('gold', 'coffee', $dataValues['social_description']);
                $dataValues['social_description'] = str_ireplace('ذهب', 'قهوة', $dataValues['social_description']);
            } else {
                $dataValues['social_description'] = 'Buy and sell green coffee beans online. Trade coffee beans with secure transactions.';
            }
            
            $seo->data_values = $dataValues;
            $seo->save();
            
            $this->info('SEO data updated successfully!');
            $this->info('Keywords: ' . implode(', ', $dataValues['keywords']));
            $this->info('Description: ' . $dataValues['description']);
            $this->info('Social Title: ' . $dataValues['social_title']);
        }
        
        return Command::SUCCESS;
    }
}

