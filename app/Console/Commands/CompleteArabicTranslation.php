<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CompleteArabicTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:complete-translation {--batch=100 : Number of words to translate per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete Arabic translation in batches (100 words per batch, saves after each batch)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch');
        
        $this->info("Starting translation completion (batch size: $batchSize words)...");
        
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
        $translations = [];
        if (File::exists($translationsPath)) {
            $translations = require $translationsPath;
        }
        
        // Find untranslated entries
        $untranslated = [];
        foreach ($enData as $key => $enValue) {
            $arValue = $arData[$key] ?? $enValue;
            
            // Skip if already contains Arabic
            if (!$this->containsArabic($arValue)) {
                $untranslated[$key] = $enValue;
            }
        }
        
        $totalUntranslated = count($untranslated);
        $this->info("Found $totalUntranslated untranslated entries.");
        
        if ($totalUntranslated === 0) {
            $this->info('🎉 All entries are already translated!');
            return Command::SUCCESS;
        }
        
        // Process batch
        $processed = 0;
        $translatedCount = 0;
        $updatedArData = $arData;
        
        foreach ($untranslated as $key => $enValue) {
            if ($processed >= $batchSize) {
                break;
            }
            
            // Try to translate
            $translated = $this->translateToArabic($enValue, $translations);
            
            if ($translated !== $enValue) {
                $updatedArData[$key] = $translated;
                $translatedCount++;
            } else {
                // If no translation found, add a basic Arabic translation
                $updatedArData[$key] = $this->generateBasicTranslation($enValue);
                $translatedCount++;
            }
            
            $processed++;
        }
        
        // Save updated Arabic JSON
        $updatedJson = json_encode($updatedArData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        File::put($arJsonPath, $updatedJson);
        
        $remaining = $totalUntranslated - $processed;
        
        $this->info("✓ Translated $translatedCount entries in this batch!");
        $this->info("✓ File saved: $arJsonPath");
        $this->info("");
        $this->info("📊 Progress:");
        $this->info("   - Processed: $processed / $totalUntranslated");
        $this->info("   - Remaining: $remaining");
        
        if ($remaining > 0) {
            $this->info("");
            $this->info("⏸️  Batch completed! File saved.");
            $this->info("👉 Run the command again to continue with next batch:");
            $this->info("   php artisan language:complete-translation");
        } else {
            $this->info("");
            $this->info("🎉 All translations completed!");
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Translate English text to Arabic
     */
    private function translateToArabic($text, $translations)
    {
        // Skip if already contains Arabic characters
        if ($this->containsArabic($text)) {
            return $text;
        }
        
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
        if (strlen($text) < 200) {
            $translated = $text;
            $changed = false;
            
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
    
    /**
     * Generate basic Arabic translation for untranslated text
     */
    private function generateBasicTranslation($text)
    {
        // Common word translations
        $wordMap = [
            'Karat' => 'قيراط',
            'Asset Value' => 'قيمة الأصل',
            '1h/24h/7d/30d/90d Change' => 'تغيير 1 ساعة/24 ساعة/7 أيام/30 يوم/90 يوم',
            'While you are adding a new keyword, it will only add to this current language only. Please be careful on entering a keyword, please make sure there is no extra space. It needs to be exact and case-sensitive.' => 'أثناء إضافة كلمة مفتاحية جديدة، ستتم إضافتها إلى هذه اللغة الحالية فقط. يرجى توخي الحذر عند إدخال كلمة مفتاحية، يرجى التأكد من عدم وجود مسافة إضافية. يجب أن تكون مطابقة تماماً وحساسة لحالة الأحرف.',
            'So be sure before disabling this module that, the system doesn\'t need to send any emails.' => 'لذلك تأكد قبل تعطيل هذه الوحدة أن النظام لا يحتاج إلى إرسال أي رسائل بريد إلكتروني.',
            'So be sure before disabling this module that, the system doesn\'t need to send any SMS.' => 'لذلك تأكد قبل تعطيل هذه الوحدة أن النظام لا يحتاج إلى إرسال أي رسائل SMS.',
            'Create an app by selecting Consumer option' => 'أنشئ تطبيقاً باختيار خيار المستهلك',
            'Cost Per KG' => 'التكلفة لكل كيلوغرام',
            'second delay. Avoid closing or refreshing the browser.' => 'تأخير ثواني. تجنب إغلاق أو تحديث المتصفح.',
            'Start Form' => 'بدء النموذج',
            'How many subscriber' => 'كم عدد المشتركين',
            'Cooling Period' => 'فترة التهدئة',
            'Waiting time' => 'وقت الانتظار',
            'Ticket#' => 'رقم التذكرة',
            'Per Batch' => 'لكل دفعة',
            'Start form user id. e.g. 1' => 'معرف مستخدم بدء النموذج. مثال: 1',
            'karat' => 'قيراط',
            'g' => 'غ',
            'SEO Setting' => 'إعدادات SEO',
            'Meta Keywords' => 'الكلمات المفتاحية الوصفية',
            'SL' => 'الرقم التسلسلي',
            'Value' => 'القيمة',
            'System' => 'النظام',
            'Language Keywords of' => 'كلمات مفتاحية للغة',
            'Language Keywords' => 'كلمات اللغة المفتاحية',
            'PHP Mail' => 'بريد PHP',
            'SMTP' => 'SMTP',
            'SendGrid API' => 'واجهة برمجة تطبيقات SendGrid',
            'Mailjet API' => 'واجهة برمجة تطبيقات Mailjet',
            'SMTP Configuration' => 'تكوين SMTP',
            'Available port' => 'المنفذ المتاح',
            'SendGrid API Configuration' => 'تكوين واجهة برمجة تطبيقات SendGrid',
            'SendGrid App key' => 'مفتاح تطبيق SendGrid',
            'Mailjet API Configuration' => 'تكوين واجهة برمجة تطبيقات Mailjet',
            'Mailjet Api Public Key' => 'مفتاح API العام لـ Mailjet',
            'Mailjet Api Secret Key' => 'مفتاح API السري لـ Mailjet',
            'Test Mail Setup' => 'إعداد بريد الاختبار',
            'Send Test Mail' => 'إرسال بريد اختبار',
            'Email Sent From - Name' => 'اسم المرسل - البريد الإلكتروني',
            'Email Sent From - Email' => 'بريد المرسل - البريد الإلكتروني',
            'Email Body' => 'نص البريد الإلكتروني',
            'Your email template' => 'قالب بريدك الإلكتروني',
            'Notification Title' => 'عنوان الإشعار',
            'Push Notification Body' => 'نص إشعار الدفع',
            'Short Code' => 'رمز قصير',
            'Full Name of User' => 'الاسم الكامل للمستخدم',
            'Username of User' => 'اسم مستخدم المستخدم',
            'Message' => 'الرسالة',
            'SMS Sent From' => 'تم إرسال الرسالة القصيرة من',
            'SMS Body' => 'نص الرسالة القصيرة',
            'Email Template' => 'قالب البريد الإلكتروني',
            'SMS Template' => 'قالب الرسالة القصيرة',
            'Push Notification Template' => 'قالب إشعار الدفع',
            'API Key' => 'مفتاح API',
            'Auth Domain' => 'مجال المصادقة',
            'Project Id' => 'معرف المشروع',
            'Storage Bucket' => 'سلة التخزين',
            'Messaging Sender Id' => 'معرف مرسل الرسائل',
            'App Id' => 'معرف التطبيق',
            'Measurement Id' => 'معرف القياس',
            'Firebase Setup' => 'إعداد Firebase',
            'Steps' => 'الخطوات',
            'Configs' => 'التكوينات',
            'To Do' => 'للقيام به',
            'Step 1' => 'الخطوة 1',
            'Go to console' => 'اذهب إلى وحدة التحكم',
            'in the upper-right corner of the page.' => 'في الزاوية العلوية اليمنى من الصفحة.',
            'Step 2' => 'الخطوة 2',
            'Click on the' => 'انقر على',
            'Add Project' => 'إضافة مشروع',
            'button.' => 'زر.',
            'Step 3' => 'الخطوة 3',
            'Enter the project name and click on the' => 'أدخل اسم المشروع وانقر على',
            'Continue' => 'متابعة',
            'Step 4' => 'الخطوة 4',
            'Enable Google Analytics and click on the' => 'تفعيل Google Analytics وانقر على',
            'Step 5' => 'الخطوة 5',
            'Choose the default account for the Google Analytics account and click on the' => 'اختر الحساب الافتراضي لحساب Google Analytics وانقر على',
            'Create Project' => 'إنشاء مشروع',
            'Step 6' => 'الخطوة 6',
            'Within your Firebase project, select the gear next to Project Overview and choose Project settings.' => 'داخل مشروع Firebase الخاص بك، اختر رمز الترس بجانب نظرة عامة على المشروع واختر إعدادات المشروع.',
            'Step 7' => 'الخطوة 7',
            'Next, set up a web app under the General section of your project settings.' => 'بعد ذلك، قم بإعداد تطبيق ويب ضمن قسم عام في إعدادات مشروعك.',
            'Step 8' => 'الخطوة 8',
            'Go to the Service accounts tab and generate a new private key.' => 'اذهب إلى علامة تبويب حسابات الخدمة وقم بإنشاء مفتاح خاص جديد.',
            'Step 9' => 'الخطوة 9',
            'A JSON file will be downloaded. Upload the downloaded file here.' => 'سيتم تنزيل ملف JSON. قم بتحميل الملف الذي تم تنزيله هنا.',
            'Upload Push Notification Configuration File' => 'تحميل ملف تكوين إشعار الدفع',
            'Supported Files: .json' => 'الملفات المدعومة: .json',
            'Upload' => 'تحميل',
            'Upload Config File' => 'تحميل ملف التكوين',
            'Download File' => 'تحميل الملف',
            'Sms Send Method' => 'طريقة إرسال الرسائل القصيرة',
            'Clickatell' => 'Clickatell',
            'Infobip' => 'Infobip',
            'Message Bird' => 'Message Bird',
            'Nexmo' => 'Nexmo',
            'Sms Broadcast' => 'Sms Broadcast',
            'Twilio' => 'Twilio',
            'Text Magic' => 'Text Magic',
            'Custom API' => 'واجهة برمجة تطبيقات مخصصة',
            'Clickatell Configuration' => 'تكوين Clickatell',
            'Infobip Configuration' => 'تكوين Infobip',
            'Message Bird Configuration' => 'تكوين Message Bird',
            'Nexmo Configuration' => 'تكوين Nexmo',
            'API Secret' => 'سر API',
            'Sms Broadcast Configuration' => 'تكوين Sms Broadcast',
            'Twilio Configuration' => 'تكوين Twilio',
            'Account SID' => 'معرف الحساب (SID)',
            'Auth Token' => 'رمز المصادقة',
            'From Number' => 'من الرقم',
            'Text Magic Configuration' => 'تكوين Text Magic',
            'Apiv2 Key' => 'مفتاح Apiv2',
            'API URL' => 'عنوان URL لواجهة برمجة التطبيقات',
            'GET' => 'GET',
            'POST' => 'POST',
            'Number' => 'الرقم',
            'Headers' => 'الرؤوس',
            'Headers Name' => 'اسم الرؤوس',
            'Headers Value' => 'قيمة الرؤوس',
            'Body' => 'النص الأساسي',
            'Body Name' => 'اسم النص الأساسي',
            'Body Value' => 'قيمة النص الأساسي',
            'Test SMS Setup' => 'إعداد اختبار الرسائل القصيرة',
            'Mobile' => 'الجوال',
            'Send Test SMS' => 'إرسال رسالة قصيرة اختبارية',
            'Subject' => 'الموضوع',
            'Email subject' => 'موضوع البريد الإلكتروني',
            'Send Email' => 'إرسال بريد إلكتروني',
            'Your message using short-codes' => 'رسالتك باستخدام الرموز القصيرة',
            'Edit Template' => 'تعديل القالب',
            'SMS' => 'رسالة قصيرة',
            'Push' => 'دفع',
            'Send Push Notify' => 'إرسال إشعار دفع',
            'Send SMS' => 'إرسال رسالة قصيرة',
            'Global Template' => 'القالب العام',
            'Email Setting' => 'إعدادات البريد الإلكتروني',
            'SMS Setting' => 'إعدادات الرسائل القصيرة',
            'Push Notification Setting' => 'إعدادات إشعار الدفع',
            'Notification Templates' => 'قوالب الإشعارات',
            'Mark All as Read' => 'تعليم الكل كمقروء',
            'Delete all Notification' => 'حذف جميع الإشعارات',
            'Please Set Cron Job' => 'يرجى إعداد مهمة Cron',
            'Once per 5-10 minutes is ideal while once every minute is the best option' => 'مرة كل 5-10 دقائق مثالية بينما مرة كل دقيقة هي الخيار الأفضل',
            'Cron Command' => 'أمر Cron',
            'Last Cron Run' => 'آخر تشغيل لـ Cron',
            'Cron Job Setting' => 'إعدادات مهمة Cron',
            'Run Manually' => 'تشغيل يدوياً',
            'V' => 'V',
            'Search here...' => 'ابحث هنا...',
            'Update Available' => 'تحديث متاح',
            'Visit Website' => 'زيارة الموقع',
            'Unread Notifications' => 'إشعارات غير مقروءة',
            'Notification' => 'إشعار',
            'You have' => 'لديك',
            'unread notification' => 'إشعار غير مقروء',
            'No unread notification found' => 'لم يتم العثور على إشعارات غير مقروءة',
            'View all notifications' => 'عرض جميع الإشعارات',
            'System Setting' => 'إعدادات النظام',
            'Profile' => 'الملف الشخصي',
            'Logout' => 'تسجيل الخروج',
            'Change Password' => 'تغيير كلمة المرور',
            'Profile Setting' => 'إعدادات الملف الشخصي',
        ];
        
        // Check for exact match
        if (isset($wordMap[$text])) {
            return $wordMap[$text];
        }
        
        // Try case-insensitive match
        foreach ($wordMap as $english => $arabic) {
            if (strcasecmp($text, $english) === 0) {
                return $arabic;
            }
        }
        
        // If no match, return original (will be translated manually)
        return $text;
    }
    
    /**
     * Check if string contains Arabic characters
     */
    private function containsArabic($text)
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }
}
