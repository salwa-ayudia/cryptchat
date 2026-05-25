# CryptChat

## 1. Deskripsi Proyek

CryptChat adalah aplikasi chat sederhana dengan fitur end-to-end encryption. Semua pesan yang dikirim antar pengguna dienkripsi menggunakan algoritma modern sehingga server tidak dapat membaca isi pesan.


## 2. Persyaratan Sistem

- PHP >= 8.0
- MySQL / MariaDB
- Web browser modern (support WebCrypto API)
- Web server lokal (XAMPP)


## 3. Instalasi dan Setup

1. Salin folder 'cryptchat' ke web server lokal (htdocs untuk XAMPP) --> xampp/htdocs/cryptchat
2. Jalankan Apache dan MySQL XAMPP
3. Buat database MySQL baru 'cryptchat_db'
4. Import file database dari folder database (cryptchat_db.sql) ke database yang baru dibuat
5. Periksa konfigurasi database di config/koneksi.php sesuai (username, password MySQL lokal dan nama database)
6. Buka browser : http://localhost/cryptchat/


## 4. Cara Menjalankan Aplikasi
1. REGISTER : buka views/register.php, buat akun baru (masukkan username dan password)
2. LOGIN : buka views/login.php, masukkan username dan password yang telah dibuat
3. DASHBOARD : buka views/dashboard.php, pilih lawan bicara untuk mengirim dan melihat pesan.
4. LOGOUT : User klik logout yang ada di dropdown dan views/logout.php akan langsung berjalan dan mengembalikan ke halaman login 


## 5. Fitur Keamanan

- Semua pesan dienkripsi di browser menggunakan AES-256-GCM sebelum dikirim ke server, server hanya menyimpan ciphertext dan iv.
- Shared secret antar user dihitung menggunakan ECDH P-256, lalu diderivasi dengan HKDF SHA-256 menjadi session key AES.
- Setiap conversation menampilkan fingerprint session key untuk verifikasi manual.
- Private key user di enkripsi di browser dengan password menggunakan PBKDF2 + Salt + AES-GCM.
- Password tidak pernah disimpan ke server dalam bentuk plaintext, Server menyimpan password dalam bentuk password yang sudah di hash.


## 6. Catatan

- Pastikan browser benar-benar mendukung Web Crypto API agar proses enkripsi dan dekripsi berjalan
- Kode siap dijalankan secara lokal tanpa harus deploy ke server publik
- Disarankan menggunakan browser modern (Chrome, Edge) untuk performa maksimal
- Jangan menutup tab browser saat sesi chat berlangsung, karena sessionStorage akan hilang dan user harus login ulang