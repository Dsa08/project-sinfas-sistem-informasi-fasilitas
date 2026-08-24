# Assets Directory

Simpan gambar dan file media di folder ini.

## File yang dibutuhkan:

| File | Deskripsi | Digunakan di |
|------|-----------|--------------|
| `logo-sinfas.png` | Logo SINFAS (disarankan: transparan, min 80x80px) | Header form auth |
| `bg-auth.jpg` | Background halaman login/register (disarankan: 1920x1080px) | Background auth pages |

## Cara Mengganti Placeholder:

### Logo:
Di file `resources/views/auth/register.blade.php` dan `login.blade.php`, ganti:
```html
<div class="auth-logo-placeholder">...</div>
```
dengan:
```html
<img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="auth-logo-img">
```

### Background:
Di file `resources/views/layouts/auth.blade.php`, ganti:
```html
<div class="auth-bg-placeholder"></div>
```
dengan:
```html
<img src="{{ asset('assets/bg-auth.jpg') }}" alt="Background" class="auth-bg-img">
```
