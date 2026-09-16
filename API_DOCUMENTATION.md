# Dokumentasi API Backend (Laravel Sanctum) & Panduan Integrasi Flutter

Dokumentasi ini menyediakan spesifikasi teknis endpoint API autentikasi dan master kategori lagu (`categories`) serta panduan lengkap dan contoh kode Dart/Flutter untuk mengintegrasikan backend ini ke dalam aplikasi Flutter Anda.

---

## 1. Konfigurasi Server & Lingkungan

Saat menjalankan backend Laravel untuk pengembangan Flutter, pastikan server dapat diakses oleh emulator atau perangkat fisik Anda:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Penentuan Base URL di Flutter
| Lingkungan / Device | Base URL |
| :--- | :--- |
| **Android Emulator** | `http://10.0.2.2:8000/api` |
| **iOS Simulator** | `http://127.0.0.1:8000/api` atau `http://localhost:8000/api` |
| **Real Device (Android/iOS via WiFi)** | `http://<IP_KOMPUTER_ANDA>:8000/api` *(contoh: `http://192.168.1.10:8000/api`)* |
| **Flutter Web / Desktop** | `http://localhost:8000/api` |

### Header Standar (Wajib)
Semua request ke API **harus menyertakan header** berikut:
```http
Accept: application/json
Content-Type: application/json
```
Untuk endpoint yang membutuhkan autentikasi (dilindungi `auth:sanctum`), tambahkan header:
```http
Authorization: Bearer <access_token>
```

---

## 2. Spesifikasi Endpoint

### A. Login
Digunakan untuk mengautentikasi pengguna menggunakan **username** dan **password**.

- **URL**: `/login`
- **Method**: `POST`
- **Autentikasi**: Tidak ada (Publik)

#### Request Body
```json
{
  "username": "testuser",
  "password": "password"
}
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Login successful",
  "access_token": "1|AbCdEf1234567890...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Test User",
    "username": "testuser",
    "email": "test@example.com",
    "role": "user",
    "email_verified_at": "2026-08-30T07:28:55.000000Z",
    "created_at": "2026-08-30T07:28:55.000000Z",
    "updated_at": "2026-08-30T07:28:55.000000Z"
  }
}
```

#### Respons Kredensial Salah (401 Unauthorized)
```json
{
  "message": "Invalid credentials"
}
```

#### Respons Validasi Gagal (422 Unprocessable Entity)
```json
{
  "message": "The username field is required. (and 1 more error)",
  "errors": {
    "username": [
      "The username field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

---

### B. Profil Pengguna (Me)
Mengambil data detail profil pengguna yang sedang login berdasarkan token akses.

- **URL**: `/me`
- **Method**: `GET`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

#### Respons Berhasil (200 OK)
```json
{
  "user": {
    "id": 1,
    "name": "Test User",
    "username": "testuser",
    "email": "test@example.com",
    "role": "user",
    "email_verified_at": "2026-08-30T07:28:55.000000Z",
    "created_at": "2026-08-30T07:28:55.000000Z",
    "updated_at": "2026-08-30T07:28:55.000000Z"
  }
}
```

#### Respons Gagal / Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

### C. Logout
Mencabut / menghapus token akses yang sedang digunakan saat ini.

- **URL**: `/logout`
- **Method**: `POST`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Successfully logged out"
}
```

#### Respons Gagal / Token Tidak Valid (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

### D. Kelola Data Profil (Update Profile)
Memperbarui informasi nama, username, dan email pengguna yang sedang login.

- **URL**: `/profile`
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

#### Request Body
```json
{
  "name": "Nama Baru",
  "username": "usernamebaru",
  "email": "emailbaru@example.com"
}
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "Nama Baru",
    "username": "usernamebaru",
    "email": "emailbaru@example.com",
    "role": "user",
    "email_verified_at": "2026-08-30T07:28:55.000000Z",
    "created_at": "2026-08-30T07:28:55.000000Z",
    "updated_at": "2026-08-30T08:15:00.000000Z"
  }
}
```

#### Respons Validasi Gagal (422 Unprocessable Entity)
*(Misalnya username atau email sudah digunakan akun lain)*
```json
{
  "message": "The username has already been taken. (and 1 more error)",
  "errors": {
    "username": [
      "The username has already been taken."
    ]
  }
}
```

---

### E. Ubah Password (Update Password)
Mengubah password pengguna dengan mewajibkan verifikasi password lama (`current_password`).

- **URL**: `/profile/password`
- **Method**: `PUT`
- **Autentikasi**: `Bearer <access_token>`

#### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

#### Request Body
```json
{
  "current_password": "passwordlama123",
  "password": "passwordbaru123",
  "password_confirmation": "passwordbaru123"
}
```

#### Respons Berhasil (200 OK)
```json
{
  "message": "Password updated successfully"
}
```

#### Respons Validasi Gagal (422 Unprocessable Entity)
*(Misalnya current_password salah atau konfirmasi tidak cocok)*
```json
{
  "message": "The password is incorrect.",
  "errors": {
    "current_password": [
      "The password is incorrect."
    ]
  }
}
```

---

### F. Master Kategori Lagu (Song Categories)

Mengelola data kategori atau genre lagu (`categories`). Endpoint pembacaan data bersifat publik, sedangkan operasi penambahan, perubahan, dan penghapusan memerlukan otentikasi Sanctum (`Bearer <access_token>`).

---

#### 1. Daftar Kategori Lagu (List Categories)
Mengambil seluruh daftar kategori lagu, mendukung pencarian nama kategori.

- **URL**: `/categories`
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)
- **Query Parameter (Opsional)**:
  - `search` (string): Mencari kategori berdasarkan kecocokan nama (case-insensitive `LIKE %search%`). Contoh: `/categories?search=Pop`

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "data": [
    {
      "songcategoryid": 1,
      "songcategoryname": "Dangdut",
      "created_at": "2026-08-30T08:23:25.000000Z",
      "updated_at": "2026-08-30T08:23:25.000000Z"
    },
    {
      "songcategoryid": 2,
      "songcategoryname": "Pop Indonesia",
      "created_at": "2026-08-30T08:23:25.000000Z",
      "updated_at": "2026-08-30T08:23:25.000000Z"
    }
  ]
}
```

---

#### 2. Detail Kategori Lagu (Show Category)
Mengambil informasi detail satu kategori lagu berdasarkan `songcategoryid`.

- **URL**: `/categories/{id}` *(contoh: `/categories/1`)*
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "data": {
    "songcategoryid": 1,
    "songcategoryname": "Dangdut",
    "created_at": "2026-08-30T08:23:25.000000Z",
    "updated_at": "2026-08-30T08:23:25.000000Z"
  }
}
```

##### Respons Data Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Category] 999"
}
```

---

#### 3. Tambah Kategori Lagu (Create Category)
Menambahkan data kategori atau genre lagu baru.

- **URL**: `/categories`
- **Method**: `POST`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

##### Request Body
| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `songcategoryname` | String | Ya | Nama kategori/genre lagu, maksimal 255 karakter, harus unik. |

```json
{
  "songcategoryname": "Jazz"
}
```

##### Respons Berhasil (201 Created)
```json
{
  "message": "Category created successfully",
  "data": {
    "songcategoryid": 3,
    "songcategoryname": "Jazz",
    "created_at": "2026-08-30T08:35:00.000000Z",
    "updated_at": "2026-08-30T08:35:00.000000Z"
  }
}
```

##### Respons Validasi Gagal (422 Unprocessable Entity)
*(Misalnya nama kategori kosong atau sudah pernah terdaftar)*
```json
{
  "message": "The songcategoryname has already been taken.",
  "errors": {
    "songcategoryname": [
      "The songcategoryname has already been taken."
    ]
  }
}
```

##### Respons Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

#### 4. Perbarui Kategori Lagu (Update Category)
Memperbarui nama kategori lagu yang sudah ada.

- **URL**: `/categories/{id}` *(contoh: `/categories/3`)*
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
Content-Type: application/json
```

##### Request Body
```json
{
  "songcategoryname": "Smooth Jazz"
}
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Category updated successfully",
  "data": {
    "songcategoryid": 3,
    "songcategoryname": "Smooth Jazz",
    "created_at": "2026-08-30T08:35:00.000000Z",
    "updated_at": "2026-08-30T08:40:00.000000Z"
  }
}
```

##### Respons Validasi Gagal (422 Unprocessable Entity)
```json
{
  "message": "The songcategoryname has already been taken.",
  "errors": {
    "songcategoryname": [
      "The songcategoryname has already been taken."
    ]
  }
}
```

##### Respons Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Category] 999"
}
```

---

#### 5. Hapus Kategori Lagu (Delete Category)
Menghapus data kategori lagu dari sistem.

- **URL**: `/categories/{id}` *(contoh: `/categories/3`)*
- **Method**: `DELETE`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Category deleted successfully"
}
```

##### Respons Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Category] 999"
}
```

##### Respons Belum Login (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

### G. Master Katalog Lagu (Songs)

Mengelola data katalog lagu karaoke (`tb_songs`). Endpoint pembacaan data bersifat publik, sedangkan operasi penambahan, perubahan, dan penghapusan memerlukan otentikasi Sanctum (`Bearer <access_token>`).

---

#### 1. Daftar Lagu (List Songs)
Mengambil daftar lagu karaoke. Mendukung pencarian judul lagu atau penyanyi, filter berdasarkan kategori, serta pagination.

- **URL**: `/songs`
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)
- **Query Parameter (Opsional)**:
  - `search` (string): Mencari lagu berdasarkan kecocokan judul (`songtitle`) atau penyanyi (`songsinger`). Contoh: `/songs?search=Separuh`
  - `songcategory` / `category_id` (integer): Memfilter lagu berdasarkan ID kategori. Contoh: `/songs?songcategory=1`
  - `page` (integer): Nomor halaman untuk mode pagination. Contoh: `/songs?page=1&per_page=10`
  - `per_page` (integer): Jumlah item per halaman jika menggunakan pagination (default: 15).

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK - Tanpa pagination)
```json
{
  "data": [
    {
      "songid": 1,
      "songtitle": "Separuh Nafas",
      "songsinger": "Dewa 19",
      "songurl": "https://storage.example.com/karaoke/separuh-nafas.mp4",
      "songcategory": 1,
      "songnada": "Am",
      "songduration": "4:30",
      "created_at": "2026-08-30T17:28:07.000000Z",
      "updated_at": "2026-08-30T17:28:07.000000Z",
      "category": {
        "songcategoryid": 1,
        "songcategoryname": "Pop",
        "created_at": "2026-08-30T08:23:25.000000Z",
        "updated_at": "2026-08-30T08:23:25.000000Z"
      }
    }
  ]
}
```

---

#### 2. Detail Lagu (Show Song)
Mengambil detail satu lagu berdasarkan ID lagu (`songid`).

- **URL**: `/songs/{id}` *(contoh: `/songs/1`)*
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "data": {
    "songid": 1,
    "songtitle": "Separuh Nafas",
    "songsinger": "Dewa 19",
    "songurl": "https://storage.example.com/karaoke/separuh-nafas.mp4",
    "songcategory": 1,
    "songnada": "Am",
    "songduration": "4:30",
    "created_at": "2026-08-30T17:28:07.000000Z",
    "updated_at": "2026-08-30T17:28:07.000000Z",
    "category": {
      "songcategoryid": 1,
      "songcategoryname": "Pop",
      "created_at": "2026-08-30T08:23:25.000000Z",
      "updated_at": "2026-08-30T08:23:25.000000Z"
    }
  }
}
```

##### Respons Tidak Ditemukan (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Song] 999"
}
```

---

#### 3. Tambah Lagu Baru (Create Song)
Menambahkan data katalog lagu baru ke dalam sistem.

- **URL**: `/songs`
- **Method**: `POST`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Content-Type: application/json
Accept: application/json
```

##### Request Body
```json
{
  "songtitle": "Hati-Hati di Jalan",
  "songsinger": "Tulus",
  "songurl": "https://storage.example.com/karaoke/hati-hati-di-jalan.mp4",
  "songcategory": 1,
  "songnada": "C",
  "songduration": "4:02"
}
```

##### Parameter Body
| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `songtitle` | string | Ya | Judul lagu (maksimal 255 karakter). |
| `songsinger` | string | Ya | Nama penyanyi/artis (maksimal 255 karakter). |
| `songurl` | string | Ya | URL file video/audio karaoke. |
| `songcategory` | integer | Ya | ID kategori (harus ada di tabel `categories`). |
| `songnada` | string | Tidak | Nada dasar lagu (maksimal 10 karakter, contoh: `C`, `Am`). |
| `songduration` | string | Tidak | Durasi lagu (maksimal 5 karakter, contoh: `4:02`). |

##### Respons Berhasil (201 Created)
```json
{
  "message": "Song created successfully",
  "data": {
    "songid": 2,
    "songtitle": "Hati-Hati di Jalan",
    "songsinger": "Tulus",
    "songurl": "https://storage.example.com/karaoke/hati-hati-di-jalan.mp4",
    "songcategory": 1,
    "songnada": "C",
    "songduration": "4:02",
    "created_at": "2026-08-30T17:35:00.000000Z",
    "updated_at": "2026-08-30T17:35:00.000000Z",
    "category": {
      "songcategoryid": 1,
      "songcategoryname": "Pop"
    }
  }
}
```

---

#### 4. Perbarui Lagu (Update Song)
Memperbarui data lagu yang sudah ada. Mendukung method `PUT` (update penuh) atau `PATCH` (update sebagian).

- **URL**: `/songs/{id}` *(contoh: `/songs/2`)*
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Content-Type: application/json
Accept: application/json
```

##### Request Body (Contoh Partial Update via PATCH)
```json
{
  "songnada": "D",
  "songduration": "4:05"
}
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Song updated successfully",
  "data": {
    "songid": 2,
    "songtitle": "Hati-Hati di Jalan",
    "songsinger": "Tulus",
    "songurl": "https://storage.example.com/karaoke/hati-hati-di-jalan.mp4",
    "songcategory": 1,
    "songnada": "D",
    "songduration": "4:05",
    "created_at": "2026-08-30T17:35:00.000000Z",
    "updated_at": "2026-08-30T17:40:00.000000Z",
    "category": {
      "songcategoryid": 1,
      "songcategoryname": "Pop"
    }
  }
}
```

---

#### 5. Hapus Lagu (Delete Song)
Menghapus lagu dari sistem berdasarkan ID.

- **URL**: `/songs/{id}` *(contoh: `/songs/2`)*
- **Method**: `DELETE`
- **Autentikasi**: `Bearer <access_token>`

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Song deleted successfully"
}
```

---

### H. Kelola Pengguna (Admin User Management)

Endpoint untuk mengelola akun pengguna (`users`). Seluruh endpoint di bawah ini dilindungi oleh middleware `auth:sanctum` dan middleware `admin`. Pengguna dengan role `user` akan ditolak dengan respons `403 Forbidden`.

---

#### 1. Daftar Pengguna (List Users)
Mengambil daftar pengguna yang terdaftar pada sistem. Mendukung pencarian berdasarkan nama, username, atau email, pemfilteran berdasarkan role, serta pagination.

- **URL**: `/admin/users`
- **Method**: `GET`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)
- **Query Parameter (Opsional)**:
  - `search` (string): Mencari user berdasarkan kecocokan nama (`name`), username (`username`), atau email (`email`). Contoh: `/admin/users?search=john`
  - `role` (string): Memfilter user berdasarkan peran (`admin` atau `user`). Contoh: `/admin/users?role=user`
  - `page` (integer): Nomor halaman untuk mode pagination. Contoh: `/admin/users?page=1&per_page=15`
  - `per_page` (integer): Jumlah item per halaman (default: 15).

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

##### Respons Berhasil (200 OK - Tanpa pagination)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Administrator",
      "username": "admin",
      "email": "admin@example.com",
      "role": "admin",
      "email_verified_at": "2026-08-31T00:00:00.000000Z",
      "created_at": "2026-08-31T00:00:00.000000Z",
      "updated_at": "2026-08-31T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "role": "user",
      "email_verified_at": null,
      "created_at": "2026-08-31T01:00:00.000000Z",
      "updated_at": "2026-08-31T01:00:00.000000Z"
    }
  ]
}
```

##### Respons Akses Ditolak Bukan Admin (403 Forbidden)
```json
{
  "message": "Forbidden. Admin access required."
}
```

---

#### 2. Tambah Pengguna Baru (Create User)
Mendaftarkan pengguna baru secara langsung oleh admin tanpa perlu proses registrasi publik.

- **URL**: `/admin/users`
- **Method**: `POST`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Content-Type: application/json
Accept: application/json
```

##### Request Body
| Field | Tipe Data | Wajib/Opsional | Keterangan |
| :--- | :--- | :--- | :--- |
| `name` | string | Wajib | Nama lengkap pengguna (maks. 255 karakter) |
| `username` | string | Wajib | Username unik untuk login (maks. 255 karakter) |
| `email` | string | Wajib | Alamat email unik dan valid |
| `password` | string | Wajib | Password pengguna (min. 8 karakter) |
| `role` | string | Wajib | Peran pengguna: `"admin"` atau `"user"` |

```json
{
  "name": "Operator Baru",
  "username": "operator1",
  "email": "operator1@example.com",
  "password": "securepassword123",
  "role": "user"
}
```

##### Respons Berhasil (201 Created)
```json
{
  "message": "User created successfully",
  "data": {
    "id": 3,
    "name": "Operator Baru",
    "username": "operator1",
    "email": "operator1@example.com",
    "role": "user",
    "created_at": "2026-08-31T02:00:00.000000Z",
    "updated_at": "2026-08-31T02:00:00.000000Z"
  }
}
```

---

#### 3. Detail Pengguna (Show User)
Mengambil informasi lengkap satu pengguna berdasarkan ID.

- **URL**: `/admin/users/{id}` *(contoh: `/admin/users/2`)*
- **Method**: `GET`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "data": {
    "id": 2,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "role": "user",
    "email_verified_at": null,
    "created_at": "2026-08-31T01:00:00.000000Z",
    "updated_at": "2026-08-31T01:00:00.000000Z"
  }
}
```

---

#### 4. Perbarui Pengguna (Update User)
Memperbarui informasi nama, username, email, role, atau mereset password pengguna.

- **URL**: `/admin/users/{id}` *(contoh: `/admin/users/2`)*
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Content-Type: application/json
Accept: application/json
```

##### Request Body
| Field | Tipe Data | Wajib/Opsional | Keterangan |
| :--- | :--- | :--- | :--- |
| `name` | string | Opsional | Nama lengkap baru pengguna |
| `username` | string | Opsional | Username baru (harus unik kecuali milik user sendiri) |
| `email` | string | Opsional | Email baru (harus unik kecuali milik user sendiri) |
| `role` | string | Opsional | Peran baru: `"admin"` atau `"user"` |
| `password` | string | Opsional | Password baru (min. 8 karakter). Kosongkan jika tidak ingin mengubah password |

```json
{
  "name": "Johnathan Doe",
  "role": "admin"
}
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "User updated successfully",
  "data": {
    "id": 2,
    "name": "Johnathan Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "role": "admin",
    "created_at": "2026-08-31T01:00:00.000000Z",
    "updated_at": "2026-08-31T02:15:00.000000Z"
  }
}
```

---

#### 5. Hapus Pengguna (Delete User)
Menghapus akun pengguna dari sistem beserta seluruh token aksesnya.

> [!CAUTION]
> Admin tidak dapat menghapus akun miliknya sendiri yang sedang digunakan (`self-deletion protection`). Jika dicoba, sistem akan mengembalikan status `403 Forbidden`.

- **URL**: `/admin/users/{id}` *(contoh: `/admin/users/2`)*
- **Method**: `DELETE`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Accept: application/json
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "User deleted successfully"
}
```

##### Respons Gagal Hapus Akun Sendiri (403 Forbidden)
```json
{
  "message": "You cannot delete your own account"
}
```

---

### I. Konfigurasi Aplikasi & Iklan (Settings / Application Config)

Mengelola informasi nama aplikasi, perusahaan, serta konfigurasi banner dan iklan (`tb_application`).
- **Endpoint Publik (`/settings`)**: Dapat diakses tanpa autentikasi agar aplikasi Flutter dapat langsung mengambil konfigurasi saat inisialisasi / splash screen.
- **Endpoint Admin (`/admin/settings`)**: Membutuhkan autentikasi `Bearer <access_token>` dan hak akses `admin` untuk membaca atau memodifikasi konfigurasi.

---

#### 1. Ambil Konfigurasi Aplikasi (Get Settings - Publik)
Mengambil data konfigurasi aplikasi dan iklan yang sedang aktif.

- **URL**: `/settings`
- **Method**: `GET`
- **Autentikasi**: Tidak ada (Publik)

##### Request Header
```http
Accept: application/json
```

##### Respons Berhasil (200 OK - Pengaturan Ditemukan)
```json
{
  "data": {
    "applicationid": 1,
    "applicationcompany": "PT Karaoke Digital Nusantara",
    "applicationname": "Karaoke Family Station",
    "applicationads1": "https://storage.example.com/ads/banner1.jpg",
    "applicationads2": "https://storage.example.com/ads/banner2.jpg",
    "applicationadsactive": "Y",
    "applicationadsbottom": "https://storage.example.com/ads/banner_bottom.jpg",
    "applicationadsbottomactive": "Y",
    "created_at": "2026-08-31T17:00:00.000000Z",
    "updated_at": "2026-08-31T17:00:00.000000Z"
  }
}
```

##### Respons Berhasil (200 OK - Belum Dikonfigurasi)
```json
{
  "data": null
}
```

---

#### 2. Simpan / Perbarui Pengaturan Aplikasi (Admin Upsert Settings)
Menyimpan konfigurasi baru atau memperbarui konfigurasi yang sedang aktif.

- **URL**: `/admin/settings`
- **Method**: `POST` atau `PUT` / `PATCH`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Request Header
```http
Authorization: Bearer 1|AbCdEf1234567890...
Content-Type: application/json
Accept: application/json
```

##### Request Body
| Field | Tipe Data | Wajib/Opsional | Keterangan |
| :--- | :--- | :--- | :--- |
| `applicationcompany` | string | Wajib (baru) / Opsional (update) | Nama perusahaan pengelola (maks. 100 karakter) |
| `applicationname` | string | Wajib (baru) / Opsional (update) | Nama aplikasi karaoke (maks. 255 karakter) |
| `applicationads1` | string | Opsional | URL gambar iklan atau banner 1 |
| `applicationads2` | string | Opsional | URL gambar iklan atau banner 2 |
| `applicationadsactive` | string | Opsional | Status aktif banner atas (`"Y"` atau `"N"`, default: `"Y"`) |
| `applicationadsbottom` | string | Opsional | URL gambar iklan bottom banner |
| `applicationadsbottomactive` | string | Opsional | Status aktif bottom banner (`"Y"` atau `"N"`, default: `"Y"`) |

```json
{
  "applicationcompany": "PT Karaoke Digital Nusantara",
  "applicationname": "Karaoke Family Station",
  "applicationads1": "https://storage.example.com/ads/banner1.jpg",
  "applicationads2": "https://storage.example.com/ads/banner2.jpg",
  "applicationadsactive": "Y",
  "applicationadsbottom": "https://storage.example.com/ads/banner_bottom.jpg",
  "applicationadsbottomactive": "Y"
}
```

##### Respons Berhasil (200 OK / 201 Created)
```json
{
  "message": "Settings updated successfully",
  "data": {
    "applicationid": 1,
    "applicationcompany": "PT Karaoke Digital Nusantara",
    "applicationname": "Karaoke Family Station",
    "applicationads1": "https://storage.example.com/ads/banner1.jpg",
    "applicationads2": "https://storage.example.com/ads/banner2.jpg",
    "applicationadsactive": "Y",
    "applicationadsbottom": "https://storage.example.com/ads/banner_bottom.jpg",
    "applicationadsbottomactive": "Y",
    "created_at": "2026-08-31T17:00:00.000000Z",
    "updated_at": "2026-08-31T17:15:00.000000Z"
  }
}
```

---

#### 3. Detail Pengaturan Berdasarkan ID (Show Setting by ID)
Mengambil data record pengaturan aplikasi spesifik berdasarkan `applicationid`.

- **URL**: `/admin/settings/{id}` *(contoh: `/admin/settings/1`)*
- **Method**: `GET`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Respons Berhasil (200 OK)
```json
{
  "data": {
    "applicationid": 1,
    "applicationcompany": "PT Karaoke Digital Nusantara",
    "applicationname": "Karaoke Family Station",
    "applicationads1": "https://storage.example.com/ads/banner1.jpg",
    "applicationads2": "https://storage.example.com/ads/banner2.jpg",
    "applicationadsactive": "Y",
    "applicationadsbottom": "https://storage.example.com/ads/banner_bottom.jpg",
    "applicationadsbottomactive": "Y",
    "created_at": "2026-08-31T17:00:00.000000Z",
    "updated_at": "2026-08-31T17:15:00.000000Z"
  }
}
```

---

#### 4. Perbarui Pengaturan Berdasarkan ID (Update Setting by ID)
Mengupdate record pengaturan aplikasi tertentu.

- **URL**: `/admin/settings/{id}` *(contoh: `/admin/settings/1`)*
- **Method**: `PUT` atau `PATCH`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Request Body
```json
{
  "applicationname": "Karaoke Super App",
  "applicationadsactive": "N"
}
```

##### Respons Berhasil (200 OK)
```json
{
  "message": "Settings updated successfully",
  "data": {
    "applicationid": 1,
    "applicationcompany": "PT Karaoke Digital Nusantara",
    "applicationname": "Karaoke Super App",
    "applicationads1": "https://storage.example.com/ads/banner1.jpg",
    "applicationads2": "https://storage.example.com/ads/banner2.jpg",
    "applicationadsactive": "N",
    "applicationadsbottom": "https://storage.example.com/ads/banner_bottom.jpg",
    "applicationadsbottomactive": "Y",
    "created_at": "2026-08-31T17:00:00.000000Z",
    "updated_at": "2026-08-31T17:20:00.000000Z"
  }
}
```

---

#### 5. Hapus Pengaturan (Delete Setting)
Menghapus konfigurasi aplikasi dari database.

- **URL**: `/admin/settings/{id}` *(contoh: `/admin/settings/1`)*
- **Method**: `DELETE`
- **Autentikasi**: `Bearer <access_token>` (Harus Role Admin)

##### Respons Berhasil (200 OK)
```json
{
  "message": "Settings deleted successfully"
}
```

---

## 3. Contoh Implementasi di Flutter (Dart)

Berikut adalah contoh implementasi lengkap yang dapat langsung Anda gunakan pada project Flutter.

### Dependensi yang Disarankan
Tambahkan di `pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.2.0
  flutter_secure_storage: ^9.0.0 # Untuk menyimpan token secara aman
```

---

### A. Model Data (`lib/models/user_model.dart`)

```dart
class UserModel {
  final int id;
  final String name;
  final String username;
  final String email;
  final String role; // 'admin' atau 'user'
  final DateTime? emailVerifiedAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  UserModel({
    required this.id,
    required this.name,
    required this.username,
    required this.email,
    required this.role,
    this.emailVerifiedAt,
    required this.createdAt,
    required this.updatedAt,
  });

  bool get isAdmin => role == 'admin';
  bool get isUser => role == 'user';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String,
      username: json['username'] as String,
      email: json['email'] as String,
      role: json['role'] as String? ?? 'user',
      emailVerifiedAt: json['email_verified_at'] != null
          ? DateTime.parse(json['email_verified_at'] as String)
          : null,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'username': username,
      'email': email,
      'role': role,
      'email_verified_at': emailVerifiedAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

class AuthResponse {
  final String message;
  final String accessToken;
  final String tokenType;
  final UserModel user;

  AuthResponse({
    required this.message,
    required this.accessToken,
    required this.tokenType,
    required this.user,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      message: json['message'] as String,
      accessToken: json['access_token'] as String,
      tokenType: json['token_type'] as String,
      user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
    );
  }
}

// File: lib/models/category_model.dart
class CategoryModel {
  final int songcategoryid;
  final String songcategoryname;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  CategoryModel({
    required this.songcategoryid,
    required this.songcategoryname,
    this.createdAt,
    this.updatedAt,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      songcategoryid: json['songcategoryid'] as int,
      songcategoryname: json['songcategoryname'] as String,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'songcategoryid': songcategoryid,
      'songcategoryname': songcategoryname,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }
}
```

---

### B. Service Autentikasi (`lib/services/auth_service.dart`)

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/user_model.dart';

class AuthService {
  // Tentukan base URL sesuai platform
  static String get baseUrl {
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api'; // Android Emulator
    } else {
      return 'http://127.0.0.1:8000/api'; // iOS Simulator / Desktop / Web
    }
    // Jika menggunakan perangkat fisik Android/iOS, ganti dengan:
    // return 'http://192.168.1.xxx:8000/api';
  }

  final _storage = const FlutterSecureStorage();
  static const _tokenKey = 'auth_token';

  // Menyimpan token ke penyimpanan aman
  Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  // Mengambil token
  Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  // Menghapus token
  Future<void> deleteToken() async {
    await _storage.delete(key: _tokenKey);
  }

  // 1. Fungsi Login (menggunakan username & password)
  Future<AuthResponse> login({
    required String username,
    required String password,
  }) async {
    final url = Uri.parse('$baseUrl/login');
    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'username': username,
        'password': password,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      final authResponse = AuthResponse.fromJson(data);
      await saveToken(authResponse.accessToken);
      return authResponse;
    } else if (response.statusCode == 401) {
      throw Exception(data['message'] ?? 'Username atau password salah.');
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Validasi gagal.';
      throw Exception(firstError);
    } else {
      throw Exception('Terjadi kesalahan pada server (${response.statusCode}).');
    }
  }

  // 2. Fungsi Get Profile (Me)
  Future<UserModel> getProfile() async {
    final token = await getToken();
    if (token == null) {
      throw Exception('Token tidak ditemukan. Silakan login kembali.');
    }

    final url = Uri.parse('$baseUrl/me');
    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return UserModel.fromJson(data['user'] as Map<String, dynamic>);
    } else if (response.statusCode == 401) {
      await deleteToken();
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal mengambil data profil.');
    }
  }

  // 3. Fungsi Logout
  Future<void> logout() async {
    final token = await getToken();
    if (token != null) {
      try {
        final url = Uri.parse('$baseUrl/logout');
        await http.post(
          url,
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      } catch (e) {
        // Tangani jika terjadi error koneksi saat logout
      } finally {
        await deleteToken();
      }
    }
  }

  // 4. Fungsi Update Profile (Nama, Username, Email)
  Future<UserModel> updateProfile({
    required String name,
    required String username,
    required String email,
  }) async {
    final token = await getToken();
    if (token == null) {
      throw Exception('Token tidak ditemukan. Silakan login kembali.');
    }

    final url = Uri.parse('$baseUrl/profile');
    final response = await http.put(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'name': name,
        'username': username,
        'email': email,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return UserModel.fromJson(data['user'] as Map<String, dynamic>);
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Gagal memperbarui profil.';
      throw Exception(firstError);
    } else if (response.statusCode == 401) {
      await deleteToken();
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal memperbarui profil (${response.statusCode}).');
    }
  }

  // 5. Fungsi Update Password
  Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    final token = await getToken();
    if (token == null) {
      throw Exception('Token tidak ditemukan. Silakan login kembali.');
    }

    final url = Uri.parse('$baseUrl/profile/password');
    final response = await http.put(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPasswordConfirmation,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return;
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Password lama salah atau konfirmasi tidak cocok.';
      throw Exception(firstError);
    } else if (response.statusCode == 401) {
      await deleteToken();
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal mengubah password (${response.statusCode}).');
    }
  }
}
```

---

### C. Service Kategori Lagu (`lib/services/category_service.dart`)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'auth_service.dart';
import '../models/category_model.dart';

class CategoryService {
  final _authService = AuthService();
  final String baseUrl = AuthService.baseUrl;

  // 1. Ambil Semua Kategori (Mendukung filter search)
  Future<List<CategoryModel>> getCategories({String? search}) async {
    final query = (search != null && search.isNotEmpty)
        ? '?search=${Uri.encodeComponent(search)}'
        : '';
    final url = Uri.parse('$baseUrl/categories$query');

    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      final list = data['data'] as List<dynamic>;
      return list
          .map((item) => CategoryModel.fromJson(item as Map<String, dynamic>))
          .toList();
    } else {
      throw Exception('Gagal memuat kategori lagu (${response.statusCode}).');
    }
  }

  // 2. Ambil Detail Kategori Berdasarkan ID
  Future<CategoryModel> getCategory(int id) async {
    final url = Uri.parse('$baseUrl/categories/$id');
    final response = await http.get(
      url,
      headers: {
        'Accept': 'application/json',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return CategoryModel.fromJson(data['data'] as Map<String, dynamic>);
    } else if (response.statusCode == 404) {
      throw Exception('Kategori tidak ditemukan.');
    } else {
      throw Exception('Gagal memuat detail kategori.');
    }
  }

  // 3. Tambah Kategori Baru (Wajib Auth)
  Future<CategoryModel> createCategory(String name) async {
    final token = await _authService.getToken();
    if (token == null) {
      throw Exception('Silakan login terlebih dahulu.');
    }

    final url = Uri.parse('$baseUrl/categories');
    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'songcategoryname': name,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 201) {
      return CategoryModel.fromJson(data['data'] as Map<String, dynamic>);
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Validasi gagal.';
      throw Exception(firstError);
    } else if (response.statusCode == 401) {
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal menambahkan kategori.');
    }
  }

  // 4. Perbarui Kategori (Wajib Auth)
  Future<CategoryModel> updateCategory(int id, String name) async {
    final token = await _authService.getToken();
    if (token == null) {
      throw Exception('Silakan login terlebih dahulu.');
    }

    final url = Uri.parse('$baseUrl/categories/$id');
    final response = await http.put(
      url,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'songcategoryname': name,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return CategoryModel.fromJson(data['data'] as Map<String, dynamic>);
    } else if (response.statusCode == 422) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError = errors?.values.first?[0] ?? 'Validasi gagal.';
      throw Exception(firstError);
    } else if (response.statusCode == 404) {
      throw Exception('Kategori tidak ditemukan.');
    } else if (response.statusCode == 401) {
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal memperbarui kategori.');
    }
  }

  // 5. Hapus Kategori (Wajib Auth)
  Future<void> deleteCategory(int id) async {
    final token = await _authService.getToken();
    if (token == null) {
      throw Exception('Silakan login terlebih dahulu.');
    }

    final url = Uri.parse('$baseUrl/categories/$id');
    final response = await http.delete(
      url,
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return;
    } else if (response.statusCode == 404) {
      throw Exception('Kategori tidak ditemukan.');
    } else if (response.statusCode == 401) {
      throw Exception('Sesi login telah kedaluwarsa.');
    } else {
      throw Exception('Gagal menghapus kategori.');
    }
  }
}
```

---

### D. Contoh Pemanggilan di Controller / UI Flutter

#### 1. Operasi Autentikasi & Akun
```dart
final authService = AuthService();

// Skenario 1: Proses Login
try {
  final result = await authService.login(
    username: usernameController.text.trim(),
    password: passwordController.text,
  );
  print('Selamat datang, ${result.user.name}!');
  // Navigasi ke halaman beranda
} catch (error) {
  print('Login gagal: $error');
}

// Skenario 2: Cek Sesi Pengguna saat App Dibuka (Splash / Init)
try {
  final user = await authService.getProfile();
  print('Sesi valid untuk user: ${user.username}');
  // Arahkan ke HomeScreen
} catch (error) {
  // Arahkan ke LoginScreen
}

// Skenario 3: Logout
await authService.logout();
// Arahkan kembali ke LoginScreen

// Skenario 4: Update Profil (Nama, Username, Email)
try {
  final updatedUser = await authService.updateProfile(
    name: 'Nama Pengguna Baru',
    username: 'usernambaru',
    email: 'emailbaru@example.com',
  );
  print('Profil berhasil diperbarui: ${updatedUser.name}');
} catch (error) {
  print('Gagal memperbarui profil: $error');
}

// Skenario 5: Ubah Password
try {
  await authService.updatePassword(
    currentPassword: 'passwordlama123',
    newPassword: 'passwordbaru123',
    newPasswordConfirmation: 'passwordbaru123',
  );
  print('Password berhasil diubah.');
} catch (error) {
  print('Gagal mengubah password: $error');
}
```

#### 2. Operasi Master Kategori Lagu
```dart
final categoryService = CategoryService();

// Skenario 1: Ambil Semua Kategori
try {
  final categories = await categoryService.getCategories();
  print('Total kategori: ${categories.length}');
} catch (error) {
  print('Error: $error');
}

// Skenario 2: Cari Kategori
try {
  final filtered = await categoryService.getCategories(search: 'Pop');
  print('Hasil pencarian: ${filtered.map((e) => e.songcategoryname).toList()}');
} catch (error) {
  print('Error: $error');
}

// Skenario 3: Tambah Kategori Baru (Perlu login)
try {
  final newCategory = await categoryService.createCategory('Dangdut Koplo');
  print('Kategori berhasil ditambahkan: ID ${newCategory.songcategoryid}');
} catch (error) {
  print('Gagal tambah: $error');
}

// Skenario 4: Update Kategori (Perlu login)
try {
  final updated = await categoryService.updateCategory(1, 'Dangdut Klasik');
  print('Nama baru: ${updated.songcategoryname}');
} catch (error) {
  print('Gagal update: $error');
}

// Skenario 5: Hapus Kategori (Perlu login)
try {
  await categoryService.deleteCategory(1);
  print('Kategori berhasil dihapus');
} catch (error) {
  print('Gagal hapus: $error');
}
```

---

## 4. Troubleshooting & Tips Integrasi

1. **Error `Connection refused`**:
   - Pastikan backend berjalan dengan opsi `--host=0.0.0.0` bukan hanya `127.0.0.1`.
   - Pastikan Anda menggunakan `10.0.2.2` jika menguji di Android Emulator.
2. **Error `Cleartext HTTP traffic not permitted` pada Android**:
   - Jika aplikasi dijalankan di Android dengan `http://` (non-HTTPS), pastikan konfigurasi `android:usesCleartextTraffic="true"` ditambahkan pada tag `<application>` di file `android/app/src/main/AndroidManifest.xml`.
3. **Pesan Validasi Berbahasa Lain**:
   - Pesan validasi default dikelola melalui Laravel Localization. Jika ingin mengubah bahasa pesan validasi, konfigurasi `APP_LOCALE` di file `.env`.
