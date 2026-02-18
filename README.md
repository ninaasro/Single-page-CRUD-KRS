Project ini adalah aplikasi Single Page Application (SPA) untuk mengelola proses KRS (Pengambilan Mata Kuliah).Aplikasi dibuat untuk memenuhi tes teknis Web Developer (Full Stack) dengan fitur:
1. CRUD Enrollment/KRS (Pada fitur Create, sistem melakukan upsert terhadap tabel students dan courses menggunakan firstOrCreate, lalu membuat enrollment dalam satu DB::transaction() untuk memastikan atomicity)
2. Insert 3 tabel dalam 1 transaksi
3. Server-side pagination
4. Sorting semua kolom
5. Quick filter
6. Live search (debounce)
7. Advanced filter (multi kondisi)
8. Advanced order (multi kolom)
9. Export 5.000.000+ data ke CSV
10. Seeder minimal 5 juta data

Frontend
* Vue 3 (Composition API)
* Vite
* Axios

Backend 
* Laravel
* PostgreSQL

Database
* PostgreSQL
* Menggunakan indexing dan pg_trgm untuk optimasi search

Cara Menjalankan Project (Local)

a. clone repository
  - git clone https://github.com/ninaasro/Single-page-CRUD-KRS.git
  - cd repository-name

b. Setup Backend (Laravel)
  - cd backend
  - composer install
  - cp .env.example .env
  - php artisan key:generate
  -  Atur database di file .env

c. Jalankan Migration
  - php artisan migrate

d. Untuk generate minimal 5 juta data
  - php artisan db:seed

e. Jalankan Backend
  - php artisan serve

f. Setup FrontEnd
  - cd frontend
  - npm install
  - npm run dev

note:
Saat project ini dideploy menggunakan cPanel, 5.000.000 data tidak dapat dijalankan di server online karena keterbatasan dari hosting, yaitu, Batas penggunaan memory dan waktu eksekusi PHP, Batasan ukuran query/transaksi PostgreSQL di hosting, sehingga deploy tersebut tidaklah sempurna. Namun hal itu sudah di uji di server lokal dan aplikasi tetap berjalan dengan baik<img width="1254" height="514" alt="image" src="https://github.com/user-attachments/assets/7514363c-d35e-4fa2-968b-9778a7d0f1e1" />
