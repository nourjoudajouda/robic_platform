# ملفات الترجمة في الموقع

## 1. ملفات اللغة الرئيسية (JSON)
هذه الملفات تحتوي على معظم النصوص القابلة للترجمة في الموقع:

- `resources/lang/en.json` - ملف اللغة الإنجليزية (المصدر)
- `resources/lang/ar.json` - ملف اللغة العربية (الهدف الرئيسي)
- `resources/lang/bn.json` - ملف اللغة البنغالية
- `resources/lang/hn.json` - ملف اللغة الهندية

## 2. ملفات Laravel القياسية (PHP)
هذه الملفات تستخدم للتحقق من الصحة والتوثيق:

- `resources/lang/en/auth.php` - رسائل المصادقة
- `resources/lang/en/pagination.php` - رسائل التصفح
- `resources/lang/en/validation.php` - رسائل التحقق من الصحة

**ملاحظة:** تحتاج لإنشاء نسخ عربية من هذه الملفات:
- `resources/lang/ar/auth.php`
- `resources/lang/ar/pagination.php`
- `resources/lang/ar/validation.php`

## 3. ملف القاموس المخصص
- `resources/lang/translations_ar.php` - قاموس الترجمات المخصص (يستخدم في الأوامر)

## 4. ملفات الـ Views (Blade Templates)
هذه الملفات تحتوي على نصوص مدمجة قد تحتاج ترجمة:

### أ) لوحة الإدارة (Admin)
- `resources/views/admin/` - جميع ملفات لوحة الإدارة
- `resources/views/admin/language/` - صفحات إدارة اللغات
- `resources/views/admin/partials/` - الأجزاء المشتركة

### ب) واجهة المستخدم (User/Frontend)
- `resources/views/templates/basic/` - قالب الواجهة الرئيسي
- `resources/views/templates/basic/user/` - صفحات المستخدم
- `resources/views/templates/basic/partials/` - الأجزاء المشتركة

### ج) المكونات (Components)
- `resources/views/components/` - مكونات قابلة لإعادة الاستخدام

### د) صفحات الخطأ
- `resources/views/errors/` - صفحات 404, 419, 500

## 5. كيفية استخدام الترجمات في الكود

في ملفات Blade، تستخدم الترجمات بالطرق التالية:
- `{{ __("key") }}`
- `{{ trans("key") }}`
- `@lang("key")`

في ملفات PHP، تستخدم:
- `__("key")`
- `trans("key")`
- `Lang::get("key")`

## 6. الملفات التي تحتاج مراجعة يدوية

1. **ملفات PHP للتحقق (validation.php, auth.php, pagination.php)**
   - يجب إنشاء نسخ عربية

2. **ملفات Views التي قد تحتوي على نصوص مكتوبة مباشرة**
   - البحث عن النصوص الإنجليزية المباشرة في ملفات .blade.php

3. **ملفات JavaScript**
   - قد تحتوي على رسائل JavaScript تحتاج ترجمة

4. **ملفات Config**
   - قد تحتوي على نصوص افتراضية

