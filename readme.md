# SSO

Laravel package untuk memudahkan otentikasi pengguna di aplikasi Universitas Udayana.

## Prasyarat

- PHP versi >= 7.4
- Laravel versi 7 ke atas

## Instalasi

Via Composer

```bash
composer require ristekusdi/sso
```

## Konfigurasi

### Memasang nilai di Environment file

TBA

### Otentikasi

Pertama, kita akan mengubah konfigurasi yang ada di `config/auth.php`

- Ubah konfigurasi web guard driver seperti di bawah ini.

```php
'guards' => [
    'web' => [
        'driver' => 'sso-web', // bagian ini yang berubah
        'provider' => 'users',
    ],
]
```

- Ubah konfigurasi provider "users" seperti di bawah ini.

```php
'providers' => [
    'users' => [
        'driver' => 'sso-users',
        'model' => RistekUSDI\SSO\Models\User::class,
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
],
```

**Catatan**: Jika Anda ingin menggunakan User Model yang lain, silakan cek sesi Tanya Jawab *Bagaimana cara mengimplementasi User Model saya?*

## Penggunaan Dasar

Jalankan `php artisan serve` dan masukkan URL http://localhost:8000/login untuk diarahkan ke halaman login SSO.

### Data Pengguna

Package ini mengimplementasikan `Illuminate\Contracts\Auth\Guard`. Sehingga, semua method bawaan Laravel tersedia.

Contoh: 

- `Auth::user()` untuk mendapatkan data pengguna yang terotentikasi.
- `Auth::check()` untuk mengecek apakah pengguna sudah terotentikasi atau login.
- `Auth::guest()` untuk mengecek apakah pengguna adalah "tamu" (belum login atau terotentikasi).

### Data Otorisasi

Gunakan perintah `Auth::user()->permissions()` atau `auth()->user()->permissions()`. Hasil yang di dapatkan adalah daftar otorisasi dalam bentuk array.

### Mengecek Otorisasi Pengguna

Gunakan perintah `Auth::user()->hasPermission($permissions)` atau `auth()->user()->hasPermission($permissions)` dengan `$permissions` sebagai parameter. Tipe data parameter yang diterima adalah `string` dan `array`. Hasil yang diterima adalah `true` atau `false`.

Contoh:

- `auth()->user()->hasPermission('view-mahasiswa')`
- `auth()->user()->hasPermission(['view-mahasiswa', 'store-mahasiswa'])`

### Middleware

Anda bisa menggunakan middleware yang disediakan oleh package ini di `routes/web.php` atau di Controller menggunakan SSO Can Middleware.

Misal di `routes/web.php`

```php

// Contoh pertama
Route::get('/', [HomeController::class, 'index'])->middleware('sso-web');

// Contoh kedua dengan middleware parameter
Route::group(['middleware' => 'sso-web'], function () {
    // Middleware parameter
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->middleware('sso-web-can:view-mahasiswa');

    // Middleware multiple parameter
    Route::patch('/mahasiswa/1/update', [MahasiswaController::class, 'update'])->middleware('sso-web-can:view-mahasiswa,store-mahasiswa');
});
```

Misal di Controller di method `__construct`

```php

// Middleware parameter
$this->middleware('sso-web-can:view-mahasiswa');

// Middleware parameter
$this->middleware('sso-web-can:view-mahasiswa,store-mahasiswa');
```

## Tanya Jawab

### Bagaimana cara mengimplementasi User Model saya?

```php
<?php

namespace App\Models;

use RistekUSDI\SSO\Models\User as SSOUser;

class User extends SSOUser
{
    // Do something great!
}
```

Kemudian ubah provider user model seperti di bawah ini:

```php
'providers' => [
    'users' => [
        'driver' => 'sso-users',
        'model' => App\Models\User::class,
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
],
```

> SSOUser model mengextend class `Illuminate\Contracts\Auth\Authenticatable`.

## Di mana access token dan refresh token disimpan?

Di session. Panggil dengan perintah `session()->get('_sso_token.access_token')` untuk mendapatkan access token dan `session()->get('_sso_token.refresh_token')`.