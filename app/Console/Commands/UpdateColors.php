<?php

namespace App\Console\Commands;

use App\Models\GeneralSetting;
use Illuminate\Console\Command;

class UpdateColors extends Command
{
    protected $signature = 'colors:update';
    protected $description = 'Update website colors to green theme';

    public function handle()
    {
        $gs = GeneralSetting::first();
        
        if ($gs) {
            $gs->base_color = '81C104';
            $gs->secondary_color = 'AFFA19'; // لون مشتق من الأساسي بدرجة أفتح
            $gs->save();
            
            \Cache::forget('GeneralSetting');
            
            $this->info('✅ تم تحديث الألوان بنجاح!');
            $this->info('اللون الأساسي: #81C104');
            $this->info('اللون الثانوي: #AFFA19 (مشتق من الأساسي)');
            return 0;
        }
        
        $this->error('❌ لم يتم العثور على إعدادات عامة');
        return 1;
    }
}

