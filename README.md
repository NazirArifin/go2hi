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
go2hi::date($dateFormat, [[$calendarType], [$timestamp], [$language]]);
```
* $dateFormat = format output sama dengan di fungsi date() PHP
* $calendarType (opsional) = jenis kalender (0 / ) lihat bagian Constants
* $timestamp (opsional) = timestamp untuk tanggal tertentu, jika kosong diisi sekarang
* $language (opsional) = jenis bahasa (sementara hanya inggris dan bahasa INDONESIA) lihat bagian Constants

## Constants / Variabel
```go2hi::GO2HI_GREG``` : mengeset kalender ke kalender masehi
```go2hi::GO2HI_HIJRI``` : mengeset kalender ke kalender hijriah
```go2hi::ENGLISH```: (default) set bahasa ke bahasa inggris
```go2hi::INDONESIAN```: set bahasa ke bahasa INDONESIA

## Examples / Contoh

```php
echo \go2hi\go2hi::date('d F Y'); // output sama dengan date() di php (05 February 2017)
```

```php
echo \go2hi\go2hi::date('d F Y', \go2hi\go2hi::GO2HI_HIJRI); // 05 Jumadil Awal 1438
```

```php
use \go2hi\go2hi;
echo go2hi::date('d F Y', go2hi::GO2HI_HIJRI, strtotime('1990-05-07')); 
// menggunakan timestamp dari strtotime() (12 Syawal 1410)
```

```php
use \go2hi\go2hi;
echo go2hi::date('l k, d F Y', go2hi::GO2HI_HIJRI, strtotime('1990-05-07')); 
// hari weton (Al-Itsnayna Kliwon, 12 Syawal 1410)
```

## License / Lisensi
MIT
__Free Software__



