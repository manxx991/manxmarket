# MANXMARKET

MANXMARKET adalah aplikasi web kasir (POS) untuk penjualan ritel dan grosir. Kasir mencatat penjualan, stok berkurang saat transaksi selesai, dan aturan harga grosir berlaku berdasarkan jumlah barang. Owner dapat memantau laporan dan akun, petugas gudang mengelola persediaan, sedangkan pembeli terdaftar melihat riwayat belanja serta piutangnya.

README ini menjelaskan aplikasi Laravel dalam repositori ini. Dokumen `docs/PRD.md` memuat rancangan lebih luas, tetapi sebagian nama berkas dan alurnya berasal dari versi PHP lama; gunakan rute dan instruksi di sini untuk versi Laravel saat ini.

## Spesifikasi

| Komponen | Spesifikasi |
| --- | --- |
| Aplikasi | Laravel 13, PHP `^8.3` |
| Basis data operasional | MySQL; migrasi tersedia di `database/migrations/` |
| Antarmuka | Blade, Tailwind CSS, Vite, dan JavaScript |
| Dependensi | Composer 2 dan Node.js/npm; CI menggunakan Node.js 24 |
| Akses | Browser desktop atau seluler; tersedia tema terang dan gelap |
| Gambar produk | JPG, PNG, atau WebP, maksimal 2 MB per unggahan |
| Struk | Cetak dari browser dengan pilihan kertas thermal 58 mm atau 80 mm |

Kode aplikasi berada di `app/`, tampilan di `resources/views/`, rute di `routes/web.php`, dan aset kasir di `public/assets/js/`. Data bisnis disimpan dalam tabel pengguna, kategori, produk, aturan grosir, transaksi dan itemnya, mutasi stok, piutang, pembayaran piutang, serta pengaturan toko. Transaksi kasir dan perubahan stok terkait ditangani dalam transaksi basis data.

## Struktur folder proyek

Diagram ringkas lokasi komponen utama:

```text
MANXMARKET/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Providers/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── backups/
├── docs/
├── public/
│   └── assets/
│       ├── js/
│       └── produk/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/Feature/
├── node_modules/
├── vendor/
├── .env.example
├── artisan
├── backup.bat
├── composer.json
├── package.json
├── phpunit.xml
└── start.bat
```

`app/Http/Controllers/`, `app/Models/`, dan `app/Services/` berisi alur bisnis; migration dan seeder ada di `database/`; tampilan Blade ada di `resources/views/`. Gambar produk baru disimpan di `public/assets/produk/` dan disajikan oleh web server. `vendor/` dan `node_modules/` dibuat oleh Composer/npm, bukan source yang perlu ditambahkan ke Git. Folder `database/backups/` bersifat lokal dan dapat berisi dump dengan data sensitif; jangan mengunggahnya ke repositori.

## Fungsi aplikasi

- **Kasir (`/pos`):** Cari produk dengan nama, SKU, atau barcode; pilih kategori; susun keranjang; lihat subtotal dan potongan grosir berdasarkan kuantitas; proses pembayaran tunai, QRIS, atau tempo. Nota tampil setelah transaksi dan dapat dicetak.
- **Grosir:** Atur satu aturan diskon per produk berdasarkan jumlah minimum, berupa potongan nominal, persentase, atau harga khusus. Perhitungan akhir tetap dilakukan di server saat checkout.
- **Pembayaran:** Tunai menghitung kembalian. QRIS membentuk kode dengan nominal dari total keranjang berdasarkan payload QRIS statis merchant yang diatur Owner. Tempo mencatat uang muka, sisa piutang, dan jatuh tempo bagi pembeli terdaftar dengan plafon kredit aktif.
- **Inventaris (`/inventory`):** Kelola kategori, data produk, harga, barcode, satuan, gambar, dan ambang stok minimum; catat barang masuk, penyesuaian, hasil stok opname fisik, serta lihat riwayat mutasi per produk.
- **Transaksi (`/transactions`):** Cari dan filter nota, lihat detail, cetak ulang, serta batalkan nota melalui tindakan *void* khusus Owner. Void mengembalikan stok dan memerlukan alasan; nota piutang yang sudah menerima pembayaran tidak dapat di-void.
- **Piutang (`/receivables`):** Pantau tagihan yang belum lunas, catat cicilan atau pelunasan tunai/QRIS, dan lihat riwayat pembayaran. Daftar tagihan lunas tersedia melalui riwayat transaksi dan portal pembeli, bukan daftar piutang aktif.
- **Laporan (`/reports`):** Pantau omzet, diskon, HPP, estimasi laba kotor, metode pembayaran, penerimaan cicilan, serta produk terlaris. Laporan transaksi dapat diekspor sebagai CSV.
- **Akun dan pengaturan (`/roles`, `/settings`):** Owner membuat akun dengan plafon kredit saat pendaftaran pembeli, mengatur status aktif akun, serta mengisi identitas toko, teks struk, dan payload QRIS merchant.
- **Portal pembeli (`/portal`):** Pembeli melihat katalog, transaksi sendiri, saldo plafon, dan riwayat piutangnya. Owner dapat membuka portal untuk memantau pelanggan.

### Hak akses

| Peran | Akses utama |
| --- | --- |
| Owner | Semua modul; laporan, pengaturan, manajemen akun, dan void nota |
| Kasir | POS, riwayat transaksi, piutang, serta pendaftaran pembeli dari POS |
| Gudang | Produk, kategori, stok, dan riwayat mutasi |
| Pembeli | Portal pribadi dan riwayat pembayaran piutang sendiri |

Kasir hanya dapat mendaftarkan akun `pembeli` dari POS. Akun baru tersebut mendapat plafon kredit `0` dan tidak dapat berbelanja tempo. Owner dapat menentukan plafon saat membuat akun pembeli melalui `/roles`, tetapi **belum tersedia fungsi untuk mengubah plafon akun yang sudah dibuat**. Akses ke rute lain dibatasi berdasarkan peran di server.

## Persyaratan instalasi

- PHP 8.3 atau lebih baru, beserta ekstensi umum Laravel (`mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `xml`, `curl`). Untuk pengujian SQLite, sediakan `pdo_sqlite`.
- Composer 2, Node.js dan npm. Node.js 24 dipakai dalam konfigurasi CI proyek.
- Server MySQL dan sebuah basis data kosong khusus aplikasi. Contoh di bawah memakai MySQL 8.x.
- Web server yang mengarah ke direktori `public/`, atau server pengembangan Laravel (`php artisan serve`). Printer thermal dan payload QRIS merchant diperlukan hanya bila fitur terkait dipakai.

## Instalasi lokal dari awal

Contoh perintah shell berikut dapat dijalankan di Git Bash, macOS, atau Linux. Jika sumber sudah tersedia sebagai arsip, lewati `git clone` dan masuk ke direktori proyek.

1. Ambil sumber dan pasang dependensi sesuai lockfile.

   ```bash
   git clone https://github.com/manxx991/manxmarket.git
   cd manxmarket
   composer install
   npm ci
   ```

2. Salin contoh konfigurasi dan buat basis data MySQL **kosong** yang dikhususkan untuk instalasi ini. Contoh perintah SQL untuk dijalankan di klien MySQL dengan akun yang berwenang:

   ```bash
   cp .env.example .env
   ```

   ```sql
   CREATE DATABASE manxmarket_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Di Windows PowerShell, gunakan `Copy-Item .env.example .env` sebagai pengganti `cp`.

3. Sesuaikan nilai berikut di `.env` dengan koneksi MySQL milik Anda. Nilai dalam tanda `<...>` adalah contoh tempat mengisi, bukan kredensial bawaan.

   ```dotenv
   APP_NAME=MANXMARKET
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=manxmarket_local
   DB_USERNAME=<USER_DB>
   DB_PASSWORD=<PASSWORD_DB>
   ```

   `.env.example` awalnya memakai SQLite. Pastikan `DB_CONNECTION` telah diubah ke `mysql` sebelum migrasi. Jika sebelumnya ada konfigurasi Laravel yang tercache, `php artisan config:clear` pada langkah berikut akan menghapusnya. Jangan masukkan `.env` ke Git. Untuk server yang dapat diakses publik, gunakan `APP_ENV=production`, `APP_DEBUG=false`, dan konfigurasi HTTPS/sesi yang sesuai.

4. Buat kunci aplikasi, jalankan migrasi, dan bangun aset frontend.

   ```bash
   php artisan config:clear
   php artisan key:generate
   php artisan migrate
   npm run build
   ```

   Migrasi di atas ditujukan untuk basis data baru. Untuk basis data yang sudah berisi transaksi, buat cadangan terlebih dahulu dan periksa status migrasi; jangan jalankan `migrate:fresh` karena menghapus tabel.

5. Jika butuh akun awal untuk instalasi lokal, isi `SEED_DEFAULT_PASSWORD` di `.env` dengan kata sandi kuat pilihan Anda, lalu jalankan:

   ```bash
   php artisan db:seed
   ```

   Seeder membuat akun dengan username `owner`, `kasir`, `gudang`, dan `pembeli` bila belum ada. **Keempat akun baru memakai kata sandi seed yang sama; akun pembeli seed memiliki plafon kredit 10.000.000.** Jangan biarkan akun contoh dan kata sandi bersama ini dipakai di lingkungan nyata. Siapkan kredensial dan batas kredit yang sesuai sebelum memberi akses kepada pengguna. Seeder tidak mengubah kata sandi akun yang sudah ada.

6. Jalankan server pengembangan dan buka `http://127.0.0.1:8000/login`.

   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

   Masuk dengan akun yang telah dibuat. Bila memakai Apache/Nginx atau Laragon, arahkan document root virtual host ke folder `public/` dan sesuaikan `APP_URL` dengan alamat situs. Jangan gunakan `php artisan serve` sebagai server produksi.

### Memulihkan database dari backup

Backup database dan foto unggahan adalah data operasional yang dikelola terpisah dan tidak disertakan di repository ini. Pulihkan backup hanya ke database tujuan yang kosong dan telah diverifikasi; jangan impor ke database yang sudah berisi transaksi.

Setelah database kosong, `.env`, dan dependensi disiapkan, impor backup privat melalui phpMyAdmin atau klien MySQL. Jangan unggah dump atau kredensial ke GitHub.

#### Melalui phpMyAdmin

1. Jalankan layanan MySQL. Buka phpMyAdmin pada hosting atau server Anda dan login dengan akun database yang berwenang.
2. Di tab **Basis data**, buat database kosong `manxmarket_local` jika belum dibuat pada langkah 2 instalasi. Jika sudah ada, pilih database tersebut dari panel kiri. Pastikan database tujuan kosong dan namanya sama dengan `DB_DATABASE` di `.env`.
3. Pada halaman database tujuan, buka tab **Impor**. Pilih file backup privat dari komputer atau penyimpanan aman Anda.
4. Biarkan format **SQL**, lalu klik **Impor** atau **Kirim** di bagian bawah halaman. Tunggu pesan keberhasilan; di tab **Struktur**, pastikan tabel `users`, `products`, dan `migrations` muncul. Jika impor gagal di tengah proses, jangan langsung mengulang pada database yang sudah terisi sebagian; periksa kesalahannya dan ulangi hanya pada database tujuan baru yang kosong.

#### Melalui terminal (alternatif)

Jalankan dari root proyek. Ganti `NAMA_USER_DB` dengan username MySQL Anda; `mysql -p` meminta kata sandi tanpa menaruhnya pada baris perintah:

```bash
mysql -h DB_HOST -u DB_USER -p DB_DATABASE < /path/to/private-backup.sql
```

Setelah impor berhasil melalui salah satu cara di atas, jalankan:

```bash
php artisan config:clear
npm run build
```

Ganti placeholder perintah dengan konfigurasi server Anda. Impor dump yang memuat schema, migrations, dan data tidak perlu diikuti `migrate` atau `db:seed`. Pertahankan `APP_KEY` asal jika data terenkripsi perlu dibaca. Pulihkan foto unggahan secara terpisah; dump SQL tidak memuat file gambar. Gunakan akun database khusus aplikasi, bukan `root`.

File SQL operasional tidak dipublikasikan bersama source code.

Gambar produk baru disimpan di `public/assets/produk/`; pastikan direktori ini dapat ditulisi oleh proses PHP. Gambar dari versi lama di `storage/app/public/` memerlukan `php artisan storage:link`. Jika memindahkan instalasi, pindahkan berkas unggahan dan data MySQL secara terpisah; keduanya bukan bagian dari kode Git.

## Cara penggunaan

### Menyiapkan toko dan produk

1. Login sebagai Owner. Buka **Pengaturan** (`/settings`) dan isi identitas toko serta teks struk. Untuk transaksi QRIS nyata, masukkan payload QRIS statis yang benar-benar diterbitkan untuk merchant Anda; uji pemindaian dengan aplikasi pembayaran sebelum digunakan.
2. Buka **Akun & Hak Akses** (`/roles`) untuk menyiapkan akun staf/pembeli. Jika pembeli akan bertransaksi tempo, isi plafon saat membuat akunnya. Plafon akun yang sudah ada belum dapat diubah lewat antarmuka.
3. Login sebagai Owner atau Gudang, lalu buka **Inventaris** (`/inventory`). Tambah kategori dan produk dengan SKU, harga eceran, satuan, stok awal, serta aturan grosir bila perlu. Unggah gambar bila tersedia.
4. Gunakan mutasi stok untuk barang masuk/keluar, atau **Edit Sisa Stok** untuk mencatat hasil hitung fisik. Lihat **History Stok** untuk menelusuri perubahan tiap produk.

### Menjual di kasir

1. Login sebagai Kasir atau Owner dan buka **POS** (`/pos`). Cari produk, scan barcode ke kolom pencarian, atau pilih kategori lalu tambahkan barang ke keranjang. Sesuaikan jumlah; diskon grosir diperbarui sesuai aturan produk.
2. Pilih pembeli terdaftar jika diperlukan. Untuk tunai/QRIS, pembeli umum dapat dipakai. Untuk tempo, pembeli harus terdaftar dan memiliki plafon yang cukup. Tombol **+ Pelanggan** mendaftarkan pembeli baru tanpa meninggalkan POS, tetapi plafonnya `0` dan tidak bisa diubah melalui antarmuka saat ini.
3. Klik **Bayar & Selesaikan**. Pilih **Tunai** dan masukkan uang diterima, **QRIS Dinamis** dan tampilkan kode kepada pembeli, atau **Tempo / Kasbon** dan isi uang muka serta tanggal jatuh tempo.
4. Untuk QRIS, pastikan dana benar-benar diterima melalui kanal merchant Anda **sebelum** menekan konfirmasi transaksi. Aplikasi membentuk QR dan mencatat penjualan setelah konfirmasi kasir; kode ini tidak memverifikasi pembayaran otomatis ke penyedia QRIS. Jika payload merchant belum diisi, aplikasi dapat menampilkan QR contoh, bukan QR untuk menerima pembayaran toko.
5. Setelah transaksi berhasil, lihat nota dan cetak struk 58 mm/80 mm sesuai kertas printer. Nota lama dapat dibuka lagi di **Transaksi** (`/transactions`).

### Menagih dan memantau

- Kasir/Owner membuka **Piutang** (`/receivables`), memilih tagihan aktif, mencatat cicilan atau pelunasan dengan nominal yang benar, lalu melihat riwayat pembayaran.
- Owner membuka **Laporan** (`/reports`), memilih periode, dan mengunduh CSV bila perlu. Laporan penjualan dan penerimaan cicilan ditampilkan sebagai angka terpisah.
- Pembeli login ke **Portal** (`/portal`) untuk melihat pembelian, katalog, sisa plafon, serta tagihan miliknya. Untuk void nota, Owner memakai **Transaksi** dan menuliskan alasan pembatalan.

### Pintasan keyboard

| Tombol | Fungsi |
| --- | --- |
| `F2` | Fokus kolom pencarian POS |
| `Enter` | Tambahkan produk saat barcode yang cocok dipindai di kolom pencarian |
| `F4` | Buka dialog pembayaran POS |
| `F9` | Ganti tema terang/gelap |
| `Escape` | Tutup dialog aktif |

## Pengujian dan pengembangan

Jalankan pemeriksaan dari direktori proyek:

```bash
./vendor/bin/phpunit
npm run build
```

`phpunit.xml` mengatur pengujian memakai SQLite `:memory:` dan konfigurasi cache khusus tes. Pastikan ekstensi `pdo_sqlite` aktif; jangan mengganti target pengujian ke basis data operasional. Saat mengubah Blade, jalankan `php artisan view:clear`. Untuk pengembangan aset dengan pemantauan perubahan, jalankan `npm run dev` di terminal lain saat server Laravel berjalan.

## Cadangan data dan catatan operasional

`backup.bat` adalah contoh skrip cadangan lokal; lokasi `mysqldump`, akun database, direktori cadangan, dan tujuan offsite perlu disesuaikan dengan lingkungan Anda. Skrip tidak otomatis terjadwal. Simpan backup di luar web root dengan akses terbatas, uji pemulihan ke database terpisah, dan cadangkan gambar unggahan secara terpisah.

Jangan unggah `.env`, dump SQL, atau berkas cadangan berisi data pelanggan ke repositori. Untuk penggunaan publik, periksa konfigurasi sesi, HTTPS, hak akses berkas, dan integrasi merchant sebelum membuka aplikasi ke jaringan. Fitur pergantian role tersedia bagi Owner dan dapat membuka sesi akun aktif lain; batasi akses akun Owner.

## Jika instalasi gagal

| Gejala | Pemeriksaan awal |
| --- | --- |
| Halaman menampilkan error kunci aplikasi | Pastikan `.env` sudah dibuat dan jalankan `php artisan key:generate`. |
| Migrasi gagal tersambung ke MySQL | Pastikan MySQL aktif, basis data sudah dibuat, `pdo_mysql` aktif, dan `DB_*` di `.env` benar; lalu jalankan `php artisan config:clear`. |
| Tampilan tanpa CSS atau muncul error Vite manifest | Jalankan `npm ci` dan `npm run build`; untuk mode pengembangan, jalankan `npm run dev`. |
| Tidak bisa login setelah migrasi | Instalasi baru belum memiliki akun. Isi `SEED_DEFAULT_PASSWORD` lalu jalankan `php artisan db:seed` **hanya pada basis data yang memang ingin diberi akun contoh**. |
| Gambar produk gagal disimpan/ditampilkan | Periksa izin tulis `public/assets/produk/` dan pastikan `APP_URL` sesuai alamat situs. Untuk gambar dari versi lama di `storage/app/public/`, jalankan `php artisan storage:link`. |
