Project ini adalah aplikasi Single Page Application (SPA) untuk mengelola proses KRS (Pengambilan Mata Kuliah).Aplikasi dibuat untuk memenuhi tes teknis Web Developer (Full Stack) dengan fitur:
1. CRUD Enrollment (KRS)
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
* Query Builder (untuk performa list data)

Database
* PostgreSQL
* Menggunakan indexing dan pg_trgm untuk optimasi search

Cara Menjalankan Project (Local)
a. clone repository
  - git clone https://github.com/username/repository-name.git
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
