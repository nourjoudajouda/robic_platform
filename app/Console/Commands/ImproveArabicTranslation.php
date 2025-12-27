<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImproveArabicTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:improve-arabic-translation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Improve Arabic translation by adding more translations to dictionary and translating remaining words';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting translation improvement process...');
        
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
        
        // Add common word translations
        $commonTranslations = [
            // Common words
            'Extension' => 'الامتداد',
            'Configure' => 'تكوين',
            'Help' => 'مساعدة',
            'Script' => 'سكريبت',
            'Need Help' => 'تحتاج مساعدة',
            'Page' => 'صفحة',
            'Sections' => 'الأقسام',
            'Checking' => 'جاري التحقق',
            'Available' => 'متاح',
            'comma' => 'فاصلة',
            'or' => 'أو',
            'enter' => 'أدخل',
            'key' => 'مفتاح',
            'Image' => 'صورة',
            'Remove' => 'إزالة',
            'Item' => 'عنصر',
            'Import' => 'استيراد',
            'SELECTED' => 'محدد',
            'SELECT' => 'اختر',
            'Get This' => 'احصل على هذا',
            'Configurations' => 'التكوينات',
            'Copy' => 'نسخ',
            'Range' => 'النطاق',
            'Symbol' => 'الرمز',
            'Configuration' => 'التكوين',
            'Gateway' => 'البوابة',
            'All' => 'الكل',
            'Recipient' => 'المستلم',
            'N/A' => 'غير متاح',
            'Ship' => 'شحن',
            'Deliver' => 'تسليم',
            'Delivery' => 'التسليم',
            'Code' => 'الرمز',
            'Selectable' => 'قابل للاختيار',
            'Translate' => 'ترجمة',
            'Flag' => 'العلم',
            'SET' => 'تعيين',
            'UNSET' => 'إلغاء التعيين',
            'Host' => 'المضيف',
            'Port' => 'المنفذ',
            'Encryption' => 'التشفير',
            'SSL' => 'SSL',
            'TLS' => 'TLS',
            'App Key' => 'مفتاح التطبيق',
            'Api Public Key' => 'مفتاح API العام',
            'Api Secret Key' => 'مفتاح API السري',
            'File' => 'الملف',
            'Upload' => 'تحميل',
            'Mobile' => 'الجوال',
            'Subject' => 'الموضوع',
            'SMS' => 'رسالة قصيرة',
            'Push' => 'دفع',
            'V' => 'V',
            'Notification' => 'إشعار',
            'You have' => 'لديك',
            'unread notification' => 'إشعار غير مقروء',
            'No unread notification found' => 'لم يتم العثور على إشعارات غير مقروءة',
            'View all notifications' => 'عرض جميع الإشعارات',
            'Logins' => 'تسجيلات الدخول',
            'Notifications' => 'الإشعارات',
            'Verified' => 'تم التحقق',
            'Unverified' => 'لم يتم التحقق',
            'KYC' => 'التحقق من الهوية',
            'Reason' => 'السبب',
            'Company' => 'الشركة',
            'Home' => 'الرئيسية',
            'FAQ' => 'الأسئلة الشائعة',
            'Blog' => 'المدونة',
            'Contact' => 'اتصال',
            'Useful Links' => 'روابط مفيدة',
            'All Rights Reserved' => 'جميع الحقوق محفوظة',
            'Faq' => 'الأسئلة الشائعة',
            'Support' => 'الدعم',
            'Log Out' => 'تسجيل الخروج',
            'Subscribe' => 'اشترك',
            'Days' => 'أيام',
            'Overview' => 'نظرة عامة',
            'Limit' => 'الحد',
            'In' => 'في',
            'Max' => 'الحد الأقصى',
            'Unknown' => 'غير معروف',
            'Sold' => 'مباع',
            'Mixed' => 'مختلط',
            'Text' => 'نص',
            'URL' => 'عنوان URL',
            'Checkbox' => 'خانة اختيار',
            'Radio' => 'زر اختيار',
            'Label' => 'التسمية',
            'Width' => 'العرض',
            'Instruction' => 'تعليمات',
            'px' => 'بكسل',
            'View All' => 'عرض الكل',
            'Go to Home' => 'الذهاب إلى الصفحة الرئيسية',
            'Captcha' => 'Captcha',
            'Read More' => 'قراءة المزيد',
            'Share This' => 'شارك هذا',
            'Latest Blog' => 'أحدث المدونات',
            'Your message' => 'رسالتك',
            'learn more' => 'تعلم المزيد',
            'Allow' => 'السماح',
            'Low' => 'منخفض',
            'Medium' => 'متوسط',
            'High' => 'مرتفع',
            'Version' => 'الإصدار',
            'Click to clear' => 'انقر للمسح',
            'Uploaded' => 'تم الرفع',
            'Back' => 'رجوع',
            'Activate' => 'تفعيل',
            'Deactivate' => 'تعطيل',
            'Staff' => 'الموظفين',
            'Priority' => 'الأولوية',
            'Ticket' => 'التذكرة',
            'Posted on' => 'نشر في',
            'Reply' => 'رد',
            'New Ticket' => 'تذكرة جديدة',
            'Close Ticket' => 'إغلاق التذكرة',
            'Enter reply here' => 'أدخل الرد هنا',
            'Add Attachment' => 'إضافة مرفق',
            'No replies found here!' => 'لم يتم العثور على ردود هنا!',
            'Submitted By' => 'مقدم من',
            'Last Reply' => 'آخر رد',
            'Close Support Ticket!' => 'إغلاق تذكرة الدعم!',
            'Are you want to close this support ticket?' => 'هل تريد إغلاق تذكرة الدعم هذه؟',
            'Paste your script with proper key' => 'الصق السكريبت الخاص بك مع المفتاح المناسب',
            'Drag & drop your section here' => 'اسحب وأفلت قسمك هنا',
            'Drag the section to the left side you want to show on the page.' => 'اسحب القسم إلى الجانب الأيسر الذي تريد إظهاره في الصفحة.',
            'The SEO setting is optional for this page. If you don\'t configure SEO here, the global SEO contents will work for this page, which you can configure from' => 'إعداد SEO اختياري لهذه الصفحة. إذا لم تقم بتكوين SEO هنا، فستعمل محتويات SEO العالمية لهذه الصفحة، والتي يمكنك تكوينها من',
            'Separate multiple keywords by' => 'افصل الكلمات المفتاحية المتعددة بواسطة',
            'Set the URL to your server\'s cron job to validate the payment.' => 'عيّن عنوان URL لمهمة cron على خادمك للتحقق من الدفع.',
            'Global Setting for' => 'الإعداد العالمي لـ',
            'Content Management Options' => 'خيارات إدارة المحتوى',
            'No search result found' => 'لم يتم العثور على نتائج بحث',
            'No search result found.' => 'لم يتم العثور على نتائج بحث.',
            'No shipping methods found' => 'لم يتم العثور على طرق شحن',
            'No notification found.' => 'لم يتم العثور على إشعارات.',
            'No data found' => 'لم يتم العثور على بيانات',
            'No active categories found. Please add categories from admin panel.' => 'لم يتم العثور على فئات نشطة. يرجى إضافة فئات من لوحة الإدارة.',
            'No pending orders found' => 'لم يتم العثور على طلبات معلقة',
            'No products available at the moment.' => 'لا توجد منتجات متاحة حالياً.',
            'Page not found' => 'الصفحة غير موجودة',
            'Page you are looking for doesn\'t exist or an other error ocurred' => 'الصفحة التي تبحث عنها غير موجودة أو حدث خطأ آخر',
            'or temporarily unavailable.' => 'أو غير متاحة مؤقتاً.',
            'Sorry! Your session has expired' => 'عذراً! انتهت صلاحية جلستك',
            'Please go back and refresh your browser and try again' => 'يرجى العودة وتحديث متصفحك والمحاولة مرة أخرى',
            'Sorry! Internal server error' => 'عذراً! خطأ داخلي في الخادم',
            'Something went wrong on our end. We\'re working on fixing it.' => 'حدث خطأ ما من جانبنا. نحن نعمل على إصلاحه.',
            'Please Allow / Reset Browser Notification' => 'يرجى السماح / إعادة تعيين إشعار المتصفح',
            'By contacting us, you agree to out' => 'عن طريق الاتصال بنا، فإنك توافق على',
            'Company' => 'الشركة',
            'Latest News' => 'آخر الأخبار',
            'Facebook' => 'فيسبوك',
            'Instragram' => 'انستغرام',
            'Twitter' => 'تويتر',
            'Linkedin' => 'لينكد إن',
            'Test' => 'اختبار',
            'Test 2' => 'اختبار 2',
            'Our Services' => 'خدماتنا',
            'Privacy Policy' => 'سياسة الخصوصية',
            'Terms of Service' => 'شروط الخدمة',
            'Latest Newsss' => 'آخر الأخبار',
            'Competitive Pricing' => 'أسعار تنافسية',
            'Secure Transactions' => 'معاملات آمنة',
            'Easy Liquidity' => 'سيولة سهلة',
            'User-Friendly Experience' => 'تجربة سهلة الاستخدام',
            'Frequently Asked Questions' => 'الأسئلة الشائعة',
            'Still have a questions?' => 'هل لا تزال لديك أسئلة؟',
            'Feel free to contact us with any questions.' => 'لا تتردد في الاتصال بنا بأي أسئلة.',
            'contact' => 'اتصال',
            'Hear from Our Happy Customers' => 'استمع من عملائنا السعداء',
            'Discover how robic makes bean investing easy, safe, and rewarding' => 'اكتشف كيف يجعل Robic استثمار البن سهلاً وآمناً ومجزياً',
            'Adam Smith' => 'آدم سميث',
            'CEO at Webflow Agency' => 'الرئيس التنفيذي في وكالة Webflow',
            'Find answers to the most common questions about robic and our services' => 'ابحث عن إجابات لأكثر الأسئلة شيوعاً حول Robic وخدماتنا',
            'You are with us' => 'أنت معنا',
            'Join Us' => 'انضم إلينا',
            'Get in Touch' => 'تواصل معنا',
            'You can reach us anytime' => 'يمكنك التواصل معنا في أي وقت',
            'Level 1, 12 Sample St, Sydney NSW 2000' => 'المستوى 1، 12 شارع Sample، سيدني نيو ساوث ويلز 2000',
            'info@viserlab.com' => 'info@viserlab.com',
            '+89525249684' => '+89525249684',
        ];
        
        // Merge with existing translations
        $translations = array_merge($translations, $commonTranslations);
        
        // Find and translate remaining untranslated words
        $translatedCount = 0;
        $updatedArData = $arData;
        
        foreach ($enData as $key => $enValue) {
            $arValue = $arData[$key] ?? $enValue;
            
            // Skip if already contains Arabic
            if ($this->containsArabic($arValue)) {
                continue;
            }
            
            // Try to translate
            $translated = $this->translateToArabic($enValue, $translations);
            
            if ($translated !== $enValue && $translated !== $arValue) {
                $updatedArData[$key] = $translated;
                $translatedCount++;
            }
        }
        
        // Save updated Arabic JSON
        $updatedJson = json_encode($updatedArData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        File::put($arJsonPath, $updatedJson);
        
        // Save updated translations dictionary
        $this->saveTranslationsFile($translations, $translationsPath);
        
        $this->info("✓ Translated $translatedCount additional entries!");
        $this->info("✓ Total translations in dictionary: " . count($translations));
        $this->info('✓ File saved: ' . $arJsonPath);
        
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
     * Check if string contains Arabic characters
     */
    private function containsArabic($text)
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }
    
    /**
     * Save translations to PHP file
     */
    private function saveTranslationsFile($translations, $path)
    {
        uksort($translations, function($a, $b) {
            $lenDiff = strlen($b) - strlen($a);
            if ($lenDiff !== 0) {
                return $lenDiff;
            }
            return strcmp($a, $b);
        });
        
        $phpContent = "<?php\n\n";
        $phpContent .= "/**\n";
        $phpContent .= " * Arabic Translations Dictionary\n";
        $phpContent .= " * This file contains common English to Arabic translations\n";
        $phpContent .= " * Updated on " . date('Y-m-d H:i:s') . "\n";
        $phpContent .= " */\n\n";
        $phpContent .= "return [\n";
        
        foreach ($translations as $en => $ar) {
            $enEscaped = addslashes($en);
            $arEscaped = addslashes($ar);
            $phpContent .= "    '$enEscaped' => '$arEscaped',\n";
        }
        
        $phpContent .= "];\n";
        
        File::put($path, $phpContent);
    }
}
