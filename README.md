# go2hi
GO2HI - Gregorian To Hijri Converter
PHP date helper to get hijriah calender from timestamp. Mendapatkan tanggal kalender Hijriah mengunakan PHP.

## Installation / Pemasangan
Using composer. Menggunakan composer dengan perintah:

```sh
composer require nazir/go2hi
```

## Usage / Penggunaan
```php
echo \go2hi\go2hi::date('d F Y'); // output sama dengan date() di php (05 February 2017)
```

```php
echo \go2hi\go2hi::date('d F Y', \go2hi\go2hi::GO2HI_HIJRI); // 05 Jumadil Awal 1438
```

```php
use \go2hi\go2hi;
echo go2hi::date('d F Y', go2hi::GO2HI_HIJRI, strtotime('1990-05-07')); // menggunakan timestamp dari strtotime() (12 Syawal 1410)
```

```php
use \go2hi\go2hi;
echo go2hi::date('l k, d F Y', go2hi::GO2HI_HIJRI, strtotime('1990-05-07')); // hari weton (Al-Itsnayna Kliwon, 12 Syawal 1410)
```

## Lisensi
MIT
__Free Software__



