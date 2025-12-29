# دليل الاختبارات الشامل للمنصة (Comprehensive Testing Guide)

## 📋 نظرة عامة

تم إنشاء نظام اختبارات شامل للمنصة باستخدام PHPUnit و Laravel Testing Framework. هذا الدليل يوضح كيفية استخدام وإضافة المزيد من الاختبارات.

---

## 🗂️ هيكل الاختبارات

```
tests/
├── TestCase.php              # الكلاس الأساسي للاختبارات
├── CreatesApplication.php    # Trait لإنشاء التطبيق
├── Feature/                  # اختبارات الوظائف الكاملة (Feature Tests)
│   ├── User/
│   │   ├── AuthenticationTest.php
│   │   └── DashboardTest.php
│   ├── Admin/
│   │   ├── AuthenticationTest.php
│   │   └── DashboardTest.php
│   └── Api/
│       └── ApiAuthenticationTest.php
└── Unit/                     # اختبارات الوحدات (Unit Tests)
    └── Models/
        ├── UserTest.php
        └── AdminTest.php
```

---

## 🚀 تشغيل الاختبارات

### تشغيل جميع الاختبارات
```bash
php artisan test
# أو
vendor/bin/phpunit
```

### تشغيل مجموعة معينة من الاختبارات
```bash
# اختبارات الوحدات فقط
php artisan test --testsuite=Unit

# اختبارات الوظائف فقط
php artisan test --testsuite=Feature

# اختبارات معينة
php artisan test tests/Feature/User/AuthenticationTest.php
```

### تشغيل اختبار محدد
```bash
php artisan test --filter test_user_can_login_with_valid_credentials
```

### مع عرض التغطية (Coverage)
```bash
php artisan test --coverage
```

---

## 📝 أنواع الاختبارات

### 1. Unit Tests (اختبارات الوحدات)
اختبارات للوحدات الفردية (Models, Classes, Functions) بدون الاعتماد على قاعدة البيانات أو HTTP requests.

**مثال**: `tests/Unit/Models/UserTest.php`
- اختبار علاقات Models
- اختبار Scopes
- اختبار Attributes/Mutators
- اختبار Methods

### 2. Feature Tests (اختبارات الوظائف)
اختبارات للوظائف الكاملة بما في ذلك HTTP requests، قاعدة البيانات، routing، و middleware.

**مثال**: `tests/Feature/User/AuthenticationTest.php`
- اختبار تسجيل الدخول/الخروج
- اختبار التسجيل
- اختبار CRUD operations
- اختبار API endpoints

---

## ✍️ كتابة اختبارات جديدة

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

        $response->assertStatus(200)
            ->assertViewIs('admin.products.index');
    }

    public function test_admin_can_create_product(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'name' => 'New Product',
                'sku' => 'PROD-001',
                // ... بيانات أخرى
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'sku' => 'PROD-001',
        ]);

        $response->assertRedirect();
    }
}
```

---

## 🔧 الإعدادات المطلوبة

### 1. ملف .env.testing (اختياري)
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### 2. قاعدة بيانات الاختبارات
يمكن استخدام SQLite في الذاكرة (`:memory:`) للسرعة، أو قاعدة بيانات منفصلة.

---

## 📊 الاختبارات الحالية

### ✅ تم إنشاؤها

#### Unit Tests
- ✅ `UserTest` - اختبارات نموذج المستخدم
- ✅ `AdminTest` - اختبارات نموذج المدير

#### Feature Tests
- ✅ `User/AuthenticationTest` - تسجيل الدخول/الخروج/التسجيل للمستخدمين
- ✅ `User/DashboardTest` - الوصول للوحة التحكم
- ✅ `Admin/AuthenticationTest` - تسجيل الدخول/الخروج للمدراء
- ✅ `Admin/DashboardTest` - الوصول للوحة التحكم وتحديث البروفايل
- ✅ `Api/ApiAuthenticationTest` - API authentication

---

## 📋 قائمة الاختبارات الموصى بها (TODO)

### Models (Unit Tests)
- [ ] ProductTest
- [ ] TransactionTest
- [ ] WalletTest
- [ ] DepositTest
- [ ] WithdrawalTest
- [ ] AssetTest
- [ ] BatchTest
- [ ] SupportTicketTest
- [ ] RoleTest
- [ ] PermissionTest

### Controllers (Feature Tests)
- [ ] ProductControllerTest (Admin)
- [ ] TransactionControllerTest (Admin & User)
- [ ] WalletControllerTest (User)
- [ ] DepositControllerTest (Admin & User)
- [ ] WithdrawalControllerTest (Admin & User)
- [ ] BuyControllerTest (User)
- [ ] SellControllerTest (User)
- [ ] SupportTicketControllerTest (Admin & User)
- [ ] ProfileControllerTest (User)
- [ ] ManageUsersControllerTest (Admin)
- [ ] PaymentControllerTest (Gateway)

### API (Feature Tests)
- [ ] ProductApiTest
- [ ] TransactionApiTest
- [ ] WalletApiTest
- [ ] MarketPricesApiTest

### Middleware Tests
- [ ] AdminMiddlewareTest
- [ ] CheckStatusMiddlewareTest
- [ ] RegistrationCompleteMiddlewareTest

### Traits Tests
- [ ] UserNotifyTest
- [ ] AuditableTest
- [ ] SupportTicketManagerTest

---

## 🎯 أفضل الممارسات

### 1. استخدام RefreshDatabase
```php
use RefreshDatabase; // لإعادة تعيين قاعدة البيانات لكل اختبار
```

### 2. استخدام Factories
```php
$user = User::factory()->create(); // أفضل من إنشاء البيانات يدوياً
```

### 3. تسمية الاختبارات الواضحة
```php
// ✅ جيد
public function test_user_can_login_with_valid_credentials(): void

// ❌ سيء
public function test_login(): void
```

### 4. اختبار حالة واحدة لكل test method
```php
// ✅ جيد
public function test_user_can_login(): void { ... }
public function test_user_cannot_login_with_wrong_password(): void { ... }

// ❌ سيء
public function test_login_scenarios(): void { 
    // جميع السيناريوهات في test واحد
}
```

### 5. استخدام Assertions المناسبة
```php
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$response->assertStatus(200);
$response->assertRedirect(route('user.home'));
$response->assertJson(['success' => true]);
```

---

## 🐛 حل المشاكل الشائعة

### 1. خطأ في قاعدة البيانات
- تأكد من إعداد `phpunit.xml` بشكل صحيح
- استخدم `RefreshDatabase` trait

### 2. خطأ في Authentication
- استخدم `actingAs($user, 'guard')` للاختبارات
- تأكد من استخدام Guard الصحيح (`web`, `admin`, `sanctum`)

### 3. خطأ في CSRF Token
- `TestCase` يتجاهل CSRF تلقائياً
- إذا لم يعمل، أضف: `$this->withoutMiddleware(VerifyCsrfToken::class);`

---

## 📈 قياس التغطية

### عرض التغطية
```bash
php artisan test --coverage
```

### مع تفاصيل أكثر
```bash
php artisan test --coverage --min=80
```

### HTML Report
```bash
php artisan test --coverage-html coverage/
```

---

## 🔗 روابط مفيدة

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Dusk (Browser Testing)](https://laravel.com/docs/dusk)

---

## 💡 نصائح إضافية

1. **اكتب الاختبارات قبل أو أثناء كتابة الكود** (TDD)
2. **اجعل الاختبارات سريعة** - استخدم `:memory:` database
3. **اكتب اختبارات للسلوكيات، وليس للتنفيذ**
4. **استخدم descriptive names** للاختبارات
5. **احذف الاختبارات التي لم تعد مفيدة**

---

## 📞 الدعم

إذا واجهت أي مشاكل أو لديك أسئلة حول الاختبارات، يرجى مراجعة:
1. Laravel Testing Documentation
2. PHPUnit Documentation
3. كود الاختبارات الموجودة كأمثلة

---

**آخر تحديث**: تم إنشاء هذا الدليل مع إنشاء نظام الاختبارات الأساسي.

