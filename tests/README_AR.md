# نظام الاختبارات - Robic Platform

## ✅ تم إنشاء نظام اختبارات شامل للمنصة

تم إنشاء نظام اختبارات متكامل باستخدام **PHPUnit** و **Laravel Testing Framework** لاختبار جميع أجزاء المنصة.

---

## 📁 ما تم إنشاؤه

### 1. الهيكل الأساسي
- ✅ `tests/TestCase.php` - الكلاس الأساسي للاختبارات
- ✅ `tests/CreatesApplication.php` - Trait لإنشاء التطبيق

### 2. اختبارات الوحدات (Unit Tests)
- ✅ `tests/Unit/Models/UserTest.php` - اختبارات نموذج المستخدم
- ✅ `tests/Unit/Models/AdminTest.php` - اختبارات نموذج المدير

### 3. اختبارات الوظائف (Feature Tests)

#### المستخدمون (User)
- ✅ `tests/Feature/User/AuthenticationTest.php` - تسجيل الدخول/الخروج/التسجيل
- ✅ `tests/Feature/User/DashboardTest.php` - الوصول للوحة التحكم

#### المدراء (Admin)
- ✅ `tests/Feature/Admin/AuthenticationTest.php` - تسجيل الدخول/الخروج
- ✅ `tests/Feature/Admin/DashboardTest.php` - لوحة التحكم والبروفايل

#### API
- ✅ `tests/Feature/Api/ApiAuthenticationTest.php` - مصادقة API

### 4. Factories
- ✅ `database/factories/RoleFactory.php` - مصنع الأدوار

### 5. الوثائق
- ✅ `tests/TESTING_GUIDE_AR.md` - دليل شامل بالعربية

---

## 🚀 كيفية التشغيل

### تشغيل جميع الاختبارات
```bash
php artisan test
```

### تشغيل نوع محدد من الاختبارات
```bash
# اختبارات الوحدات فقط
php artisan test --testsuite=Unit

# اختبارات الوظائف فقط
php artisan test --testsuite=Feature
```

### تشغيل اختبار محدد
```bash
php artisan test tests/Feature/User/AuthenticationTest.php
```

### تشغيل اختبار واحد
```bash
php artisan test --filter test_user_can_login_with_valid_credentials
```

---

## 📊 ما يتم اختباره حالياً

### ✅ المصادقة (Authentication)
- تسجيل دخول المستخدمين
- تسجيل خروج المستخدمين
- تسجيل مستخدمين جدد
- تسجيل دخول المدراء
- تسجيل خروج المدراء
- مصادقة API

### ✅ النماذج (Models)
- علاقات User (wallets, transactions, assets)
- علاقات Admin (roles)
- Scopes للمستخدمين
- Attributes (fullname, mobileNumber)
- Methods (hasRole, hasPermission)

### ✅ الوصول (Access Control)
- الوصول للوحة تحكم المستخدم
- الوصول للوحة تحكم المدير
- حماية الصفحات المحمية

---

## 📝 إضافة اختبارات جديدة

### مثال: اختبار Controller جديد

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_products(): void
    {
        $admin = Admin::factory()->create();
        Product::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
    }
}
```

### مثال: اختبار Model جديد

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
        ]);
    }
}
```

---

## 🎯 الخطوات التالية (موصى بها)

### أولويات عالية
1. ✅ اختبارات المصادقة - **تم**
2. ⏳ اختبارات Products CRUD
3. ⏳ اختبارات Transactions
4. ⏳ اختبارات Wallets
5. ⏳ اختبارات Deposits/Withdrawals

### أولويات متوسطة
- ⏳ اختبارات Buy/Sell Orders
- ⏳ اختبارات Support Tickets
- ⏳ اختبارات API endpoints
- ⏳ اختبارات Payment Gateways

### أولويات منخفضة
- ⏳ اختبارات Middleware
- ⏳ اختبارات Traits
- ⏳ اختبارات Commands
- ⏳ اختبارات Jobs

---

## 📖 للمزيد من المعلومات

راجع ملف `tests/TESTING_GUIDE_AR.md` للحصول على دليل شامل ومفصل.

---

## ⚠️ ملاحظات مهمة

1. **قاعدة البيانات**: الاختبارات تستخدم `RefreshDatabase` لإعادة تعيين قاعدة البيانات قبل كل اختبار
2. **CSRF**: يتم تجاهل CSRF تلقائياً في الاختبارات
3. **Authentication**: استخدم `actingAs($user, 'guard')` لمحاكاة المستخدم المسجل الدخول
4. **Factories**: استخدم Factories لإنشاء البيانات بدلاً من إنشائها يدوياً

---

**تم إنشاء النظام بنجاح! 🎉**

يمكنك الآن البدء في إضافة المزيد من الاختبارات تدريجياً لتغطية جميع أجزاء المنصة.

