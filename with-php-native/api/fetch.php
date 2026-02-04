<?php
require "../config/db.php";
// // Pada baris pertama, saya mengimpor file koneksi database.
// // File ini diperlukan agar proses penyimpanan produk ke tabel
// // dapat dilakukan melalui koneksi mysqli.

$url = "https://recruitment.fastprint.co.id/tes/api_tes_programmer";
// // Berikutnya, saya mendefinisikan URL API yang akan diakses.
// // Endpoint ini sama dengan yang digunakan saat preview header,
// // dan pada file ini API benar-benar digunakan untuk menarik data produk.


// ===================================================
// 1. Ambil HEADER untuk membaca username & password
// ===================================================
// // Sama seperti file sebelumnya, langkah pertama adalah mengambil header API.
// // Karena username dan waktu server hanya bisa didapat melalui header,
// // maka saya melakukan CURL GET terlebih dahulu.
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false
]);

$response = curl_exec($ch);

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$raw_header = substr($response, 0, $header_size);
curl_close($ch);

// // Setelah header berhasil dibaca, saya akan mulai mengekstrak
// // informasi username dan waktu server.


// ---------------------------------------------------
// Ambil username dari header API
// ---------------------------------------------------
// // API mengirimkan username dinamis melalui header
// // "x-credentials-username". Saya ambil menggunakan regex,
// // kemudian saya bersihkan jika ada komentar tambahan.
preg_match('/x-credentials-username:\s*(.+)/i', $raw_header, $uMatch);
$rawUser = trim($uMatch[1]);
$username = trim(preg_replace('/\(.*/', '', $rawUser)); // bersihkan komentar


// ---------------------------------------------------
// Ambil waktu server
// ---------------------------------------------------
// // Password API wajib mengikuti format tanggal server,
// // bukan tanggal komputer lokal. Maka dari itu saya ambil
// // header "Date", lalu konversi menjadi timestamp.
preg_match('/Date:\s+(.*)\r/i', $raw_header, $dMatch);
$serverTime = strtotime($dMatch[1]);


// ---------------------------------------------------
// Buat password otomatis
// ---------------------------------------------------
// // Password API menggunakan pola: "bisacoding-dd-mm-yy".
// // Setelah string lengkap terbentuk, saya hash menjadi md5
// // sesuai yang diwajibkan API.
$raw_password = "bisacoding-" . date("d-m-y", $serverTime);
$password = md5($raw_password);



// ===================================================
// 2. Request POST ke API untuk mengambil data produk
// ===================================================
// // Setelah username dan password siap, saya mengirim request POST
// // ke API dengan autentikasi Basic Auth. Jika berhasil,
// // API akan mengembalikan data produk dalam bentuk JSON.
$postData = [
    "username" => $username,
    "password" => $password
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "$username:$password"
]);

$response = curl_exec($ch);
curl_close($ch);

// // Response kemudian saya decode ke array agar mudah digunakan.
$data = json_decode($response, true);



// ===================================================
// 3. Jika tidak ada error, simpan data produk ke database
// ===================================================
if ($data["error"] == 0) {

    // // API mengembalikan daftar produk dalam bentuk array "data".
    // // Saya melakukan looping satu per satu untuk menyimpan produk
    // // beserta kategori dan statusnya.
    foreach ($data["data"] as $p) {

        // -----------------------------------------------
        // Cek apakah kategori sudah pernah tersimpan
        // -----------------------------------------------
        // // Jika kategori sudah ada, gunakan id-nya.
        // // Jika belum, buat kategori baru lalu ambil id-nya.
        $cekKategori = mysqli_query(
            $conn,
            "SELECT * FROM kategori WHERE nama_kategori='" . $p['kategori'] . "'"
        );

        if (mysqli_num_rows($cekKategori) == 0) {
            mysqli_query(
                $conn,
                "INSERT INTO kategori(nama_kategori) VALUES('" . $p['kategori'] . "')"
            );
            $kategori_id = mysqli_insert_id($conn);
        } else {
            $kategori_id = mysqli_fetch_assoc($cekKategori)['id_kategori'];
        }


        // -----------------------------------------------
        // Cek apakah status sudah tersimpan
        // -----------------------------------------------
        // // Sama seperti kategori, jika status belum ada,
        // // maka saya insert terlebih dahulu agar relasi produk valid.
        $cekStatus = mysqli_query(
            $conn,
            "SELECT * FROM status WHERE nama_status='" . $p['status'] . "'"
        );

        if (mysqli_num_rows($cekStatus) == 0) {
            mysqli_query(
                $conn,
                "INSERT INTO status(nama_status) VALUES('" . $p['status'] . "')"
            );
            $status_id = mysqli_insert_id($conn);
        } else {
            $status_id = mysqli_fetch_assoc($cekStatus)['id_status'];
        }


        // -----------------------------------------------
        // Simpan produk ke tabel produk
        // -----------------------------------------------
        // // Setelah kategori dan status siap, barulah saya simpan produk
        // // lengkap dengan nama, harga, kategori_id, dan status_id.
        mysqli_query(
            $conn,
            "INSERT INTO produk(nama_produk, harga, kategori_id, status_id)
            VALUES(
                '" . $p['nama_produk'] . "',
                '" . $p['harga'] . "',
                '" . $kategori_id . "',
                '" . $status_id . "'
            )"
        );
    }

    // // Jika semua data berhasil dimasukkan, saya tampilkan pesan sukses.
    echo "Data produk berhasil ditarik dari API dan disimpan ke database.";

} else {
    // // Jika API mengembalikan error, saya munculkan pesan dari API.
    echo "Gagal mengambil data dari API. Pesan: " . $data["ket"];
}
