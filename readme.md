# RistekUSDI SSO Laravel

Laravel package untuk otentikasi pengguna pada aplikasi internal Universitas Udayana berbasis Keycloak.

## Kompatibilitas Versi

| PHP      | Laravel       | sso-laravel |
|----------|---------------|-------------|
| >= 7.4   | >= 6.x        | 1.x         |

## Memasang nilai di Environment file

Salin format di bawah ini ke dalam file `.env`

```bash
SSO_BASE_URL=
SSO_REALM=
SSO_REALM_PUBLIC_KEY=
SSO_CLIENT_ID=
SSO_CLIENT_SECRET=
```

- `SSO_BASE_URL`

SSO server Url. Contoh: `https://your-sso-domain.com/auth`

- `SSO_REALM`

SSO realm. Nilai bawaan adalah `master`.

- `SSO_REALM_PUBLIC_KEY`

SSO server realm public key. Dari dashboard menuju **Realm Settings** >> **Keys** >> **RS256** >> **Public key**

- `SSO_CLIENT_ID`

Dari dashboard **klik edit Client ID yang dipilih** >> **Settings** >> **salin nama Client ID di field Client ID**

- `SSO_CLIENT_SECRET`

> Pastikan pengaturan **Access Type** adalah **confidential** agar memperoleh nilai Secret

Dari dashboard **klik edit Client ID yang dipilih** >> **Credentials** >> **salin isian Secret di field Secret**

## Langkah Instalasi dan Konfigurasi

1. Instal `ristekusdi/sso-laravel` dengan perintah
```bash 
composer require ristekusdi/sso-laravel 1.*
```

2. Untuk mengimpor aset yang ada di dalam library sso-laravel jalankan perintah berikut
```bash
php artisan vendor:publish --provider="RistekUSDI\SSO\WebGuardServiceProvider"
```

Perintah di atas akan mengimpor aset:

- File konfigurasi `sso.php` ke dalam folder `config`.

- Folder berisi halaman-halaman HTTP Error (401 dan 403) yang berkaitan dengan `Callback Exception`. Lokasi halaman-halaman HTTP Error setelah diimpor berada di folder `resources/views/sso-laravel/errors`. **Anda memiliki kebebasan untuk melakukan kustomisasi terhadap halaman error tersebut.** Untuk menghubungkan halaman-halaman HTTP Error yang berkaitan dengan `Callback Exception` ke dalam proyek Laravel, silakan menuju bagian [Mengubungkan Callback Exception Ke Handler Exception](#menghubungkan-callback-exception-ke-handler-exception).

- File route `sso.php` ke dalam folder `routes`.

- File controller `AuthController.php` ke dalam folder `app/Http/Controllers/SSO`.

3. Ubah nilai `driver` dan `model` di file `config/auth.php`

```php
'guards' => [
    'web' => [
        'driver' => 'sso-web', // bagian ini yang berubah
        'provider' => 'users',
    ],
]
```


```php
'providers' => [
    'users' => [
        'driver' => 'sso-users',
        'model' => RistekUSDI\SSO\Models\User::class,
    ],
],
```

4. Memuat route `sso.php` yang ada di dalam folder routes ke dalam file `web.php` yang ada di dalam folder routes dengan perintah berikut.

```php
require __DIR__.'/sso.php';
```

5. Untuk melindungi halaman atau URL tertentu (misal /home) dengan otentikasi SSO maka tambahkan middleware `sso-web` pada route tersebut. 

Contoh: 

```php
Route::get('/home', 'HomeController@index')->middleware('sso-web');
```

## Penggunaan Dasar

Jalankan `php artisan serve` dan masukkan URL http://localhost:8000/sso/login untuk diarahkan ke halaman login SSO.

- `/sso/login` untuk login.
- `/sso/logout` untuk logout.
- `/sso/callback` untuk callback.

## Data Pengguna

Package ini mengimplementasikan `Illuminate\Contracts\Auth\Guard`. Sehingga, semua method bawaan Laravel tersedia.

Contoh: 

- `Auth::user()` untuk mendapatkan data pengguna yang terotentikasi.
- `Auth::user()->roles()` untuk mendapatkan daftar peran user pada aplikasi yang aktif.
- `Auth::check()` untuk mengecek apakah pengguna sudah terotentikasi atau login.
- `Auth::guest()` untuk mengecek apakah pengguna adalah "tamu" (belum login atau terotentikasi).

Atribut pengguna yang tersedia antara lain:

- `sub`.
- `full_identity`. Format: `NIP Nama Pengguna`.
- `username`.
- `identifier`. `identifier` adalah NIP atau NIM.
- `name`.
- `email`.
- `roles`.

## Menghubungkan Callback Exception Ke Handler Exception

Pada file `Handler.php` di folder `app/Exceptions` impor class `CallbackException` dan gunakan class tersebut di method `render`.

```php
<?php

// ....
use RistekUSDI\SSO\Exceptions\CallbackException;

class Handler extends ExceptionHandler
{
    // ...

    public function render($request, Exception $e)
    {
        // Hubungkan CallbackException ke dalam method render
        if ($e instanceof CallbackException) {
            return $e->render($request);
        }
        return parent::render($request, $e);
    }
}
```

## Bagaimana cara mendapatkan access token dan refresh token?

Ada dua cara untuk mendapatkan access token dan refresh token:

1. Mengimpor facade `SSOWeb` dengan perintah `use RistekUSDI\SSO\Facades\SSOWeb;`, kemudian jalankan perintah `SSOWeb::retrieveToken()`.

2. Menggunakan session. Gunakan perintah `session()->get('_sso_token.access_token')` untuk mendapatkan access token dan `session()->get('_sso_token.refresh_token')`.

## Pertanyaan (Konfigurasi Tingkat Lanjut)

### Bagaimana cara saya meng-extend User model dengan User model dari RistekUSDI?

Pada User model extend class User model dari RistekUSDI dengan sintaks berikut.

```php
use RistekUSDI\SSO\Models\User as SSOUser;

class User extends SSOUser
{

}
```

Berikutnya, pada file `auth.php` ubah User model seperti berikut.

```php
'providers' => [
    'users' => [
        'driver' => 'sso-users',
        'model' => App\User::class, // sesuaikan dengan lokasi User model Anda.
    ],
],
```

### Saya ingin menyisipkan atribut lain ke dalam User model saat proses otentikasi berhasil. Bagaimana caranya?

Agar Anda bisa menyisipkan atribut lain ke dalam User model maka Anda perlu melakukan proses extend class User model dari RistekUSDI yang ada pada langkah sebelumnya. Setelah itu, Anda bisa menambahkan atribut-atribut lain pada properti `$custom_fillable`.

```php
use RistekUSDI\SSO\Models\User as SSOUser;

class User extends SSOUser
{
    public $custom_fillable = [
        'unud_identifier_id',
        'unud_user_type_id',
        'role_active',
        'role_permissions',
        // dan lain-lain...
    ]
}
```

### Bagaimana cara saya meng-extend WebGuard?

Anda bisa melakukan extend WebGuard dengan membuat file WebGuard baru dan mengubah nilai `guards.web` pada file `sso.php`.

```php
/**
 * Load guard class.
 */
'guards' => [
    'web' => RistekUSDI\SSO\Auth\Guard\WebGuard::class,
],
```

**Catatan:** Extend WebGuard berguna jika Anda ingin menyisipkan session aplikasi Anda ke dalam property user saat berhasil melakukan otentikasi.

## Bisakah Anda memberikan contoh implementasi untuk Pertanyaan (Konfigurasi Tingkat Lanjut)?

Anda bisa melihat contoh implementasi tersebut di [github.com/ristekusdi/sso-simulation-laravel6](https://github.com/ristekusdi/sso-simulation-laravel6).

**Catatan:** Repositori ini bersifat privat.

## Catatan

Package ini merupakan fork dari sumber kode [mariovalney/laravel-keycloak-web-guard](https://github.com/mariovalney/laravel-keycloak-web-guard).

Kami menggunakan kode tersebut untuk dimodifikasi sesuai kebutuhan internal kami. Kami ucapkan terima kasih kepada pengembang dari package tersebut.