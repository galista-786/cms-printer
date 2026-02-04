import requests
import hashlib
import re
from datetime import datetime

URL = "https://recruitment.fastprint.co.id/tes/api_tes_programmer"

# ===================================================================
# // Pada file services.py ini, saya memisahkan seluruh logic
# // untuk mengambil data dari API FastPrint.
# //
# // Dengan memisahkan logic API ke file service seperti ini,
# // struktur kode menjadi lebih rapi dan mengikuti best practice Django,
# // yaitu memisahkan business logic dari views.
# ===================================================================


def get_produk_api():
    # ============================================================
    # 1. REQUEST AWAL (AMBIL HEADER)
    # ============================================================
    # // Langkah pertama adalah melakukan request GET ke API.
    # // Tujuannya bukan untuk mengambil data produk,
    # // tetapi untuk membaca header yang berisi username dan waktu server.
    # // API mengharuskan kita mengambil username dari header
    # // dan membentuk password berdasarkan tanggal server.
    response = requests.get(URL)

    headers = response.headers  # // Simpan semua header untuk diproses.


    # ============================================================
    # 2. AMBIL USERNAME DARI HEADER
    # ============================================================
    # // API memberikan username secara dinamis melalui header
    # // 'x-credentials-username'.
    # // Di sini saya ambil header tersebut, lalu saya bersihkan
    # // jika ada tambahan informasi dalam tanda kurung.
    raw_username = headers.get("x-credentials-username")
    if not raw_username:
        # // Jika username tidak ditemukan, saya kembalikan error message.
        return {"error": 1, "ket": "Gagal mengambil username dari header"}

    # // Contoh header:
    # // tesprogrammer020226C23 (username berubah sesuai tanggal server)
    username = raw_username.split("(")[0].strip()


    # ============================================================
    # 3. AMBIL DATE SERVER
    # ============================================================
    # // Berikutnya, saya mengambil waktu server melalui header "Date".
    # // Ini penting, karena password API harus mencocokkan tanggal server,
    # // bukan tanggal lokal komputer pengguna.
    server_date = headers.get("Date")

    # // Format date: "Thu, 06 Feb 2025 07:15:22 GMT"
    server_time = datetime.strptime(server_date, "%a, %d %b %Y %H:%M:%S %Z")


    # ============================================================
    # 4. BENTUK PASSWORD
    # ============================================================
    # // Password API harus mengikuti pola:
    # // "bisacoding-dd-mm-yy"
    # // berdasarkan tanggal yang diberikan server.
    raw_password = f"bisacoding-{server_time.strftime('%d-%m-%y')}"

    # // Setelah itu password wajib diubah menjadi md5 hash
    password_md5 = hashlib.md5(raw_password.encode()).hexdigest()


    # ============================================================
    # 5. REQUEST POST LOGIN
    # ============================================================
    # // Setelah username dan password md5 yang valid terbentuk,
    # // barulah saya melakukan request POST ke API yang sama.
    # //
    # // Data dikirim dalam dua cara:
    # // 1. Sebagai payload form-data
    # // 2. Sebagai basic authentication
    # //
    payload = {
        "username": username,
        "password": password_md5
    }

    login_response = requests.post(
        URL,
        data=payload,
        auth=(username, password_md5)
    )

    # // Hasilnya akan mengembalikan JSON berisi data produk,
    # // atau error jika login gagal.
    return login_response.json()
