<?php

$url = "https://recruitment.fastprint.co.id/tes/api_tes_programmer";

// ==================================================
// // Pada bagian ini, saya memulai dengan mendefinisikan URL API
// // yang disediakan oleh FastPrint. Nantinya URL ini digunakan
// // untuk mengambil informasi header, username, dan data produk.
// ==================================================


// ==========================
// 1. GET HEADER
// ==========================
// // Berikutnya, saya melakukan inisialisasi CURL untuk mengambil
// // header dari API. Tujuannya adalah membaca username dan waktu
// // server yang dibutuhkan untuk membentuk password otomatis.
// // Di sini CURL saya set agar mengembalikan header sekaligus body,
// // namun fokus utama adalah membaca header saja.
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false,  // ambil header + body
]);

$response = curl_exec($ch);

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$raw_header = substr($response, 0, $header_size);

curl_close($ch);


// ==========================
// 2. BACA USERNAME DARI HEADER
// ==========================
// // Pada tahap ini, saya mengekstrak username yang dikirimkan oleh API
// // melalui header 'x-credentials-username'.
// // Username ini sifatnya dinamis dan berubah sesuai tanggal server.
// // Setelah itu, saya melakukan pembersihan, karena API sering
// // memberikan catatan tambahan di dalam tanda kurung.
// // Maka saya hapus bagian tersebut agar didapatkan username murni.
if (preg_match('/x-credentials-username:\s*(.+)/i', $raw_header, $uMatch)) {

    $rawUser = trim($uMatch[1]);

    // // Contohnya: "tesprogrammer020226C23 (username akan berubah...)"
    // // akan dibersihkan menjadi "tesprogrammer020226C23"
    $username = trim(preg_replace('/\(.*/', '', $rawUser));

} else {
    die("Gagal membaca username dari header!");
}


// ==========================
// 3. BACA TANGGAL SERVER (HEADER DATE)
// ==========================
// // Pada bagian ini, saya membaca waktu server melalui header "Date".
// // Ini penting, karena format password yang diminta API
// // menggunakan tanggal server, bukan tanggal lokal.
// // Setelah itu, waktu server saya konversi menjadi timestamp.
if (!preg_match('/Date:\s+(.*)\r/i', $raw_header, $dMatch)) {
    die("Tidak bisa membaca waktu server dari header!");
}

$serverTime = strtotime($dMatch[1]);


// ==========================
// 4. BENTUK PASSWORD OTOMATIS (selalu 2 digit)
// ==========================
// // Password untuk login API harus menggunakan rumus:
// // "bisacoding-dd-mm-yy" berdasarkan tanggal server.
// // Formatnya harus 2 digit hari, 2 digit bulan, dan 2 digit tahun.
// // Setelah string password jadi, saya lakukan hashing md5
// // sesuai dengan ketentuan API.
$raw_password = "bisacoding-" . date("d-m-y", $serverTime);
$password = md5($raw_password);


// ==========================
// 5. KIRIM REQUEST API
// ==========================
// // Setelah username dan password siap, saya melakukan request POST
// // ke API yang sama, menggunakan autentikasi Basic Auth.
// // Di sini API akan mengembalikan data produk jika proses autentikasi
// // berhasil.
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
    CURLOPT_USERPWD => $username . ":" . $password,
]);

$response = curl_exec($ch);


// ==========================
// 6. TAMPILKAN HASIL
// ==========================
// // Terakhir, saya menampilkan informasi hasil parsing header,
// // username otomatis, password asli dan versi MD5-nya,
// // serta response API dalam format array.
// // Ini saya tampilkan untuk debugging dan memverifikasi bahwa
// // proses login API berhasil.
echo "<h3>HEADER:</h3><pre>$raw_header</pre>";

echo "<h3>Data Login Otomatis:</h3>";
echo "Username: $username<br>";
echo "Raw Password: $raw_password<br>";
echo "Password MD5: $password<br><br>";

echo "<h3>Response API:</h3><pre>";
print_r(json_decode($response, true));
echo "</pre>";

curl_close($ch);
