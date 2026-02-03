<?php

$url = "https://recruitment.fastprint.co.id/tes/api_tes_programmer";

// ==========================
// 1. GET HEADER
// ==========================
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

if (preg_match('/x-credentials-username:\s*(.+)/i', $raw_header, $uMatch)) {

    $rawUser = trim($uMatch[1]);

    // Hapus komentar setelah tanda "("
    // Contoh: "tesprogrammer020226C23 (username akan berubah...)"
    // menjadi: "tesprogrammer020226C23"
    $username = trim(preg_replace('/\(.*/', '', $rawUser));

} else {
    die("Gagal membaca username dari header!");
}

// ==========================
// 3. BACA TANGGAL SERVER (HEADER DATE)
// ==========================
if (!preg_match('/Date:\s+(.*)\r/i', $raw_header, $dMatch)) {
    die("Tidak bisa membaca waktu server dari header!");
}

$serverTime = strtotime($dMatch[1]);

// ==========================
// 4. BENTUK PASSWORD OTOMATIS (selalu 2 digit)
// ==========================
$raw_password = "bisacoding-" . date("d-m-y", $serverTime);
$password = md5($raw_password);

// ==========================
// 5. KIRIM REQUEST API
// ==========================
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

echo "<h3>HEADER:</h3><pre>$raw_header</pre>";

echo "<h3>Data Login Otomatis:</h3>";
echo "Username: $username<br>";
echo "Raw Password: $raw_password<br>";
echo "Password MD5: $password<br><br>";

echo "<h3>Response API:</h3><pre>";
print_r(json_decode($response, true));
echo "</pre>";

curl_close($ch);
