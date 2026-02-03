import requests
import hashlib
import re
from datetime import datetime

URL = "https://recruitment.fastprint.co.id/tes/api_tes_programmer"

def get_produk_api():
    # =============================
    # 1. REQUEST AWAL (AMBIL HEADER)
    # =============================
    response = requests.get(URL)

    headers = response.headers

    # =============================
    # 2. AMBIL USERNAME DARI HEADER
    # =============================
    raw_username = headers.get("x-credentials-username")
    if not raw_username:
        return {"error": 1, "ket": "Gagal mengambil username dari header"}

    username = raw_username.split("(")[0].strip()

    # =============================
    # 3. AMBIL DATE SERVER
    # =============================
    server_date = headers.get("Date")
    server_time = datetime.strptime(server_date, "%a, %d %b %Y %H:%M:%S %Z")

    # =============================
    # 4. BENTUK PASSWORD
    # =============================
    raw_password = f"bisacoding-{server_time.strftime('%d-%m-%y')}"
    password_md5 = hashlib.md5(raw_password.encode()).hexdigest()

    # =============================
    # 5. REQUEST POST LOGIN
    # =============================
    payload = {
        "username": username,
        "password": password_md5
    }

    login_response = requests.post(
        URL,
        data=payload,
        auth=(username, password_md5)
    )

    return login_response.json()
