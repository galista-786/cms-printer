<?php
require "../config/db.php";

$url = "https://recruitment.fastprint.co.id/tes/api_tes_programmer";

// 1. Ambil HEADER dulu untuk mendapatkan username & password
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

// Ambil username dari header API
preg_match('/x-credentials-username:\s*(.+)/i', $raw_header, $uMatch);
$rawUser = trim($uMatch[1]);
$username = trim(preg_replace('/\(.*/', '', $rawUser)); // bersihkan komentar

// Ambil waktu server
preg_match('/Date:\s+(.*)\r/i', $raw_header, $dMatch);
$serverTime = strtotime($dMatch[1]);

// Buat password
$raw_password = "bisacoding-" . date("d-m-y", $serverTime);
$password = md5($raw_password);

// 2. REQUEST POST ke API untukambil data produk
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

// Decode JSON
$data = json_decode($response, true);

if ($data["error"] == 0) {

    foreach ($data["data"] as $p) {

        // cek kategori
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

        // cek status
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

        // simpan produk
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

    echo "Data produk berhasil ditarik dari API dan disimpan ke database.";

} else {
    echo "Gagal mengambil data dari API. Pesan: " . $data["ket"];
}
