# Implementasi Fitur Get Current User API

**Tujuan**: Mengimplementasikan endpoint API untuk mengambil profil data user yang sedang login saat ini menggunakan mekanisme *Bearer Token* pada header. Sistem ini dibangun pada framework CodeIgniter 3.

## 1. Spesifikasi API Get Current User

- **Endpoint**: `GET /api/users/current`
- **Fungsi**: Membaca token dari header `Authorization`, memvalidasi keberadaan token tersebut di tabel `sessions`, kemudian mengambil dan mengembalikan profil pengguna (dari tabel `users`) yang terkait dengan token tersebut.

### Request Headers
- `Authorization`: `Bearer <token>`
  *(Keterangan: `<token>` adalah token UUID yang sebelumnya didapatkan dari proses API Login dan tersimpan di tabel `sessions`)*

### Response Body (Success - 200 OK)
```json
{
    "data": {
        "id": 1,
        "name": "Hendra",
        "email": "hendra@localhost",
        "created_at": "2026-08-06 04:38:47"
    }
}
```

### Response Body (Error - 401 Unauthorized)
Jika header tidak ada, format token salah, atau token tidak ditemukan di database:
```json
{
    "error" : "Unauthorized"
}
```

## 2. Struktur Folder dan File (Standar CodeIgniter 3)
Ikuti standar struktur file MVC CodeIgniter 3:

*   **Controller (Logic Bisnis)**: `application/controllers/Auth/User.php`.
*   **Model (Interaksi Database)**: `application/models/User_model.php`.

## 3. Tahapan Implementasi (Step-by-Step)

Silakan ikuti panduan berikut secara berurutan:

### Langkah 1: Update Model (`User_model.php`)
1. Buka file `application/models/User_model.php`.
2. Buat method baru bernama `get_user_by_token($token)`.
3. **Logic di dalam method get_user_by_token**:
   - Lakukan query JOIN antara tabel `sessions` dan tabel `users` berdasarkan `sessions.user_id = users.id`, dengan kondisi `sessions.token = $token`.
   - Kembalikan object data user (`id`, `name`, `email`, `created_at`). Jika tidak ditemukan, kembalikan `null`.

### Langkah 2: Konfigurasi Routing (`routes.php`)
1. Buka file `application/config/routes.php`.
2. Tambahkan rule routing baru untuk endpoint ini:
   `$route['api/users/current']['get'] = 'auth/user/current';`

### Langkah 3: Update Controller (`Auth/User.php`)
1. Buka file controller `application/controllers/Auth/User.php`.
2. Buat method baru bernama `current()` untuk memproses request GET.
3. **Logic di dalam method current:**
   - Ambil nilai dari header `Authorization` menggunakan `$this->input->get_request_header('Authorization', TRUE)`.
   - Pastikan header tersebut ada dan nilainya diawali dengan string `"Bearer "`.
   - Ekstrak token dengan menghapus prefix `"Bearer "` dari string header.
   - Jika format token tidak valid atau kosong, kembalikan HTTP 401 dengan JSON `{"error": "Unauthorized"}`.
   - Panggil method `User_model->get_user_by_token($token)`.
   - Jika model mengembalikan hasil kosong (token tidak ada di database), kembalikan HTTP 401 `{"error": "Unauthorized"}`.
   - Jika user ditemukan, buat struktur array response hanya dengan atribut yang diizinkan: `id`, `name`, `email`, `created_at`. **Penting: Jangan menyertakan hash password di dalam response!**
   - Kembalikan response JSON dengan HTTP 200 OK: `{"data": { ... }}`.

### Langkah 4: Pengujian (Testing)
1. Gunakan Postman atau cURL.
2. Buat request `POST /api/users/login` terlebih dahulu untuk mendapatkan token valid jika belum memilikinya.
3. Buat request ke `GET /api/users/current` **tanpa** header Authorization. Pastikan merespon HTTP 401 `{"error": "Unauthorized"}`.
4. Buat request `GET /api/users/current` **dengan** header `Authorization: Bearer <token-sembarang-yang-salah>`. Pastikan juga merespon HTTP 401 `{"error": "Unauthorized"}`.
5. Buat request `GET /api/users/current` **dengan** header `Authorization: Bearer <token-asli>`. Pastikan mendapatkan response profil user yang bersangkutan.
