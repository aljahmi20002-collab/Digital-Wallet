# تحليل كود مشروع Digital Wallet (DigitalWallet)

> تقرير تحليلي شامل للكود — أُعد بتاريخ 2026-08-16

---

## 1. نظرة عامة

| العنصر | التفاصيل |
|---|---|
| **الاسم** | DigitalWallet — منصة محفظة رقمية (Digital Wallet) |
| **الإطار** | Laravel 11.51.0 (يتطلب PHP 8.3+) |
| **قاعدة البيانات** | MySQL/MariaDB + SQLite (مع ملف تفريغ جاهز `DB/digitalwallet.sql`) |
| **حجم الكود** | ~65,600 سطر PHP في `app/` + ~146,000 سطر إجمالاً مع الواجهات |
| **عدد الملفات** | 152 كونترولر، 82 موديل، 98 خدمة، 146 مهاجرة، 631 واجهة Blade |
| **الواجهة** | CoreUI (لوحة تحكم) + قوالب Blade + Tailwind |
| **الترجمة** | إنجليزية وإسبانية (joedixon/laravel-translation) |

---

## 2. التقنيات والمكتبات

| المكتبة | الاستخدام |
|---|---|
| `laravel/framework ^11.44` | الإطار الأساسي |
| `laravel/sanctum` + `pragmarx/google2fa` | المصادقة و 2FA |
| `spatie/laravel-permission` | الأدوار والصلاحيات |
| `laravel/reverb` | WebSockets / إشعارات لحظية |
| `stripe/stripe-php` | بطاقات افتراضية (Stripe Issuing) |
| `intervention/image` + `bacon-qr-code` | الصور ورموز QR |
| `mews/purifier` + `barryvdh/laravel-dompdf` | تنقية HTML وتوليد PDF |
| `jenssegers/agent` | كشف الجهاز/المتصفح |
| بوابات دفع كثيرة (~25 بوابة) | Stripe، PayPal، Binance، Paystack، Flutterwave، Coinbase، Cryptomus، Mollie، Razorpay... إلخ |

---

## 3. البنية المعمارية

البنية منظمة جيداً ومتقدمة مقارنة بمتوسط مشاريع CodeCanyon:

```
app/
├── Http/Controllers/   → مقسمة: Frontend / Backend / Common / Api / Webhook / Installer
├── Services/           → طبقة منطق الأعمال (98 خدمة) — جيدة جداً
│   ├── Payment/        → 25 بوابة دفع عبر Factory pattern
│   ├── P2P/            → التداول النظير-لنظير
│   ├── VirtualCard/    → بطاقات افتراضية (Bitnob, Stripe...)
│   └── MobileRecharge/ → شحن الرصيد (Reloadly, Twilio...)
├── Models/             → 82 موديل + Observers (User, Merchant, Agent)
├── Enums/ + Constants/ + Data/ + Contracts/  → كتابة حديثة (PHP 8.3 typed)
├── Policies/           → Authorization (P2P, Merchant, Language)
├── Middleware/         → 25 طبقة وسيطة مخصصة
├── Jobs/ Events/ Listeners/ Notifications/ Mail/
├── Support/            → InstallationManager، QRCodeService...
└── helpers.php         → دوال عامة (isActive، notifyEvs...)
```

**أنماط معمارية ملاحظة:**
- **Service Layer** واضحة (فصل منطق الأعمال عن الكونترولر).
- **Strategy/Factory** لبوابات الدفع (`PaymentGatewayFactory`).
- **Handlers** لنجاح/فشل المعاملات (`SuccessHandlerInterface` / `FailHandlerInterface`).
- **Middleware-based features** (التحقق من KYC، حالة الحساب، ميزات المستخدم، قفل الشاشة، 2FA).
- استخدام واسع لـ **Enums** وأنواع `readonly` والـ DTOs — مستوى PHP 8.3 حديث.

---

## 4. الوحدات الوظيفية

1. **المحافظ والعملات** — محافظ متعددة العملات، تحويل داخلي، أسعار صرف.
2. **الإيداع والسحب** — طرق دفع متعددة مع IPN/Webhooks (استثناء CSRF محمي بتوقيع Bitnob).
3. **تحويل الأموال** — إرسال/طلب أموال بين المستخدمين، روابط دفع للتجار.
4. **P2P (تداول نظير-لنظير)** — عروض بيع/شراء، أوامر مع انتهاء تلقائي، نزاعات، ترويج العروض.
5. **البطاقات الافتراضية** — Stripe Issuing و Bitnob.
6. **شحن الرصيد** — Reloadly + مزود HTTP عام + Sandbox.
7. **الاشتراكات و Wallet Earn** — خطط شهرية، مكافآت، عمولات الوكلاء.
8. **KYC، التذاكر، الإحالات، بطاقات الهدايا، القسائم، الرتب، الإشعارات**.
9. **نظام تثبيت كامل** — معالج تثبيت بخطوات (بعد إزالة رمز الشراء).
10. **أداة إصدار** (`BuildReleaseCommand`) — تنظيف كود البائع وتفريغ SQL للمشتري.

---

## 5. نقاط القوة ✅

1. **سلامة العمليات المالية**: استخدام `DB::transaction` مع `lockForUpdate` عند إتمام/فشل/إلغاء المعاملات، مع إعادة المحاولة (3 محاولات) — حماية جيدة ضد سباقات المعالجة (race conditions) في المسارات الحرجة.
2. **PIN المحفظة مشفّر** (`hashed` cast في موديل User) ✅.
3. **أمان HTTP**: طبقة `SecureHeaders` مضافة لكل الطلبات.
4. **حماية المدخلات**: `XSS` middleware + `mews/purifier` + `PurifyTrait`.
5. **منع الاحتيال**: `PreventDuplicateSubmission` (منع الإرسال المكرر)، `BlockIp`، `EnsureKYCVerified`.
6. **عزل الأدوار**: `spatie/permission` مع سياسات Policies ومراقبين Observers.
7. **Webhooks آمنة نسبياً**: `VerifyBitnobSignature` للتحقق من توقيع الـ webhook بدل الاعتماد على CSRF فقط.
8. **توثيق وكتابة نظيفة**: تعليقات توضيحية ممتازة في الكود، استخدام `declare(strict_types=1)`، `match`، الـ Enums، التلميحات النوعية في كل مكان.
9. **جدولة المهام** (Scheduler): أوامر انتهاء أوامر P2P، معالجة الاشتراكات، مكافآت Wallet Earn، تنظيف الملفات المؤقتة.
10. **معالج تثبيت متطور** مع فحص متطلبات الخادم وكتابة `.env` تلقائياً.

---

## 6. نقاط الضعف والمخاطر ⚠️

### 6.1 🔴 مفاتيح API حقيقية مكشوفة في ملف التفريغ `DB/digitalwallet.sql`

هذه أخطر ملاحظة. الملف يحتوي **مفاتيح إنتاجية حقيقية** لمزوّدي الخدمات:

| المزود | ما هو مكشوف |
|---|---|
| Twilio | `account_sid` + `auth_token` كاملين |
| Pusher | `app_id` + `key` + `secret` + `cluster` |
| Currencylayer | `api_key` كامل |
| IPinfo.io | `access_token` كامل |
| Tawk Chat | `property_id` + `widget_id` |

**الخطر:** أي شخص يستنسخ المستودع يملك هذه المفاتيح — يمكن استخدامها لإرسال رسائل SMS على حساب صاحب المشروع أو سرقة بيانات. **يجب فوراً:**
- تدوير (rotate) كل هذه المفاتيح لدى المزوّدين،
- إزالة القيم الحقيقية من التفريغ (وضع قيم فارغة أو مثال)،
- إضافة الملف لـ `.gitignore` أو التعامل معه كملف غير حسّاس.

### 6.2 🟠 لا توجد أي اختبارات (Tests)

مجلد `tests/` فارغ رغم تثبيت Pest/PHPUnit في `require-dev`. مشروع مالي بهذا الحجم **بدون اختبار واحد** يشكّل خطراً كبيراً عند أي تعديل — خصوصاً في حسابات العمولات والرسوم.

### 6.3 🟠 XSS Middleware يستخدم `strip_tags` على كل المدخلات

- سيجرّد المحتوى الشرعي أيضاً (مثل `<` في النصوص أو الأكواد)،
- يعمل **قبل** الـ validation وقد يغيّر بيانات مثل كلمات المرور إن احتوت رموزاً،
- الأفضل الاعتماد على الـ sanitization في طبقة العرض (Blade تهرّب تلقائياً) و `purifier` للحقول المحددة فقط.

### 6.4 🟠 كونترولرات وملفات ضخمة جداً

| الملف | الأسطر |
|---|---|
| `installer/index.blade.php` | 3,065 |
| `gift-card-templates/index.blade.php` | 2,077 |
| `BitnobCardProvider.php` | 1,828 |
| `MerchantPaymentReceiveController.php` | 963 |
| `SubscriptionService.php` | 894 |

تجاوز بعضها 1000 سطر → صعوبة في الصيانة والمراجعة، وتحتاج لتقسيم.

### 6.5 🟠 مهام مجدولة كل دقيقة بدون `withoutOverlapping()`

```php
$schedule->command('p2p:orders:expire')->everyMinute();
$schedule->command('p2p:promotions:expire')->everyMinute();
$schedule->command('wallet-earn:process')->everyMinute();
```

إن استغرق التنفيذ أكثر من دقيقة سيتداخل مع نفسه. يُنصح بإضافة `->withoutOverlapping()`.

### 6.6 🟠 بقايا كود قديمة وبيانات تجريبية

- بيانات الإعدادات الافتراضية في `config/settings.php` تحتوي بريد `coevs@gmail.com` و `mail.coevs.co`.
- مدير افتراضي `admin@coevs.com` في `CreateAdminUserSeeder` (يُحذف عند التثبيت فقط).
- اسم الحزمة في `composer.json` ما زال `laravel/laravel` (غير مخصّص).
- رسالة آخر commit ("oook") و README يوثق نسخاً حتى v1.0.6 فقط.

### 6.7 🟡 ملاحظات أصغر

- `autoload` يستخدم `classmap` لمجلد `app/Services/Payment` — أفضل تحويله لـ PSR-4.
- استثناءات CSRF لـ `ipn/*` مفتوحة النطاق — تأكد أن كل بوابة تتحقق من توقيع/مرجع موثوق.
- لا يوجد `public/build` (أصول Vite غير مبنية داخل المستودع) — قد يحتاج المشتري `npm run build`.
- الـ queue يعتمد على قاعدة البيانات (افتراضياً) — مقبول للنطاق الصغير.

---

## 7. جودة الكود — تقييم عام

| المحور | التقييم |
|---|---|
| التنظيم المعماري | ⭐⭐⭐⭐⭐ ممتاز |
| أمان العمليات المالية | ⭐⭐⭐⭐ جيد جداً |
| أمان المدخلات والجلسات | ⭐⭐⭐⭐ جيد |
| إدارة الأسرار (secrets) | ⭐ خطير — مفاتيح حقيقية في المستودع |
| الاختبارات | ⭐ غائبة تماماً |
| التوثيق الداخلي | ⭐⭐⭐⭐ ممتاز |
| قابلية الصيانة | ⭐⭐⭐ متوسط (ملفات ضخمة) |

---

## 8. توصيات مرتبة بالأولوية

1. **فوراً**: تدوير مفاتيح Twilio / Pusher / Currencylayer / IPinfo / Tawk وإزالة قيمها الحقيقية من `DB/digitalwallet.sql`.
2. إضافة `.gitignore` يتضمن `.env` (تأكد) ومراجعة ما يُرفع.
3. كتابة اختبارات Pest للتدفقات المالية الحرجة: الإيداع، السحب، التحويل، P2P، العمولات.
4. إضافة `->withoutOverlapping()` للمهام المجدولة.
5. تقسيم الملفات الضخمة (الكونترولرات والخدمات فوق 500 سطر).
6. استبدال `strip_tags` الشامل بمعالجة موجهة عبر Purifier للحقول النصية الغنية فقط.
7. تحديث `composer.json` (اسم الحزمة والوصف) وتوثيق النسخة الحالية في README.

---

## 9. الخلاصة

**DigitalWallet مشروع ناضج معمارياً** — فوق المتوسط بوضوح بالنسبة لمشاريع CodeCanyon: طبقة خدمات منفصلة، أنماط حديثة (PHP 8.3, Enums, DTOs, Policies, Middleware متخصص)، حماية جيدة للعمليات المالية، ومعالج تثبيت احترافي.

**لكنه يحتاج** قبل أي نشر إنتاجي: تنظيف الأسرار المكشوفة (أولوية قصوى)، بناء اختبارات، وتقليص الملفات الضخمة. بعد معالجة هذه النقاط يكون جاهزاً للاستخدام التجاري بثقة أعلى بكثير.
