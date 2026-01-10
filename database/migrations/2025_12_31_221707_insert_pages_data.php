<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert pages data if not exists
        $pages = [
            [
                'id' => 1,
                'name' => 'HOME',
                'slug' => '/',
                'tempname' => 'templates.basic.',
                'secs' => '["service","why_invest","feature","how_it_works","testimonial","faq","blog","payment_methods","subscribe"]',
                'seo_content' => '{"image":"678e109a77baa1737363610.png","description":null,"social_title":null,"social_description":null,"keywords":null}',
                'is_default' => 1,
                'created_at' => '2020-07-11 06:23:58',
                'updated_at' => '2025-01-20 03:00:12',
            ],
            [
                'id' => 4,
                'name' => 'Blog',
                'slug' => 'blog',
                'tempname' => 'templates.basic.',
                'secs' => null,
                'seo_content' => null,
                'is_default' => 1,
                'created_at' => '2020-10-22 01:14:43',
                'updated_at' => '2020-10-22 01:14:43',
            ],
            [
                'id' => 5,
                'name' => 'Contact',
                'slug' => 'contact',
                'tempname' => 'templates.basic.',
                'secs' => null,
                'seo_content' => null,
                'is_default' => 1,
                'created_at' => '2020-10-22 01:14:53',
                'updated_at' => '2020-10-22 01:14:53',
            ],
            [
                'id' => 26,
                'name' => 'Faq',
                'slug' => 'faq',
                'tempname' => 'templates.basic.',
                'secs' => '["feature","payment_methods"]',
                'seo_content' => null,
                'is_default' => 1,
                'created_at' => '2024-12-05 09:22:55',
                'updated_at' => '2024-12-07 06:46:48',
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->updateOrInsert(
                ['id' => $page['id']],
                $page
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the data
        // DB::table('pages')->whereIn('id', [1, 4, 5, 26])->delete();
    }
};
