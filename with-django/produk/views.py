from django.shortcuts import render
from .models import Produk, Kategori, Status
from .services import get_produk_api

# =====================================================================
# // Fungsi pertama yang saya buat adalah import_produk.
# // Fungsi ini bertanggung jawab untuk mengambil data dari API FastPrint
# // kemudian menyimpannya ke database Django dengan menggunakan ORM.
# =====================================================================
def import_produk(request):
    # -------------------------------------------------------------
    # // Pertama, saya memanggil get_produk_api() dari services.py.
    # // Fungsi tersebut menangani proses login API dan mengambil data.
    # -------------------------------------------------------------
    api_data = get_produk_api()

    # -------------------------------------------------------------
    # // Jika API mengembalikan error, maka saya tampilkan halaman
    # // error sederhana agar user mengetahui masalahnya.
    # -------------------------------------------------------------
    if api_data.get("error") != 0:
        return render(request, "import_error.html", {"message": api_data.get("ket")})

    # -------------------------------------------------------------
    # // Jika tidak ada error, saya mulai melakukan looping
    # // untuk menyimpan kategori, status, dan produk ke database.
    # -------------------------------------------------------------
    for item in api_data["data"]:
        # ---------------------------------------------------------
        # // Untuk kategori, saya gunakan get_or_create.
        # // Tujuannya adalah menghindari duplikasi kategori.
        # ---------------------------------------------------------
        kategori_obj, _ = Kategori.objects.get_or_create(
            nama_kategori=item["kategori"]
        )

        # ---------------------------------------------------------
        # // Begitu pula untuk status. Jika belum ada, Django akan
        # // membuat status baru; jika sudah ada, akan memakai yang lama.
        # ---------------------------------------------------------
        status_obj, _ = Status.objects.get_or_create(
            nama_status=item["status"]
        )

        # ---------------------------------------------------------
        # // Untuk produk, saya gunakan update_or_create.
        # // Saya memakai id_produk dari API sebagai primary identifier
        # // supaya jika API dipanggil berkali-kali, produk tidak duplikat
        # // tetapi diperbarui.
        # ---------------------------------------------------------
        Produk.objects.update_or_create(
            id=item["id_produk"],  # pakai ID dari API
            defaults={
                "nama_produk": item["nama_produk"],
                "harga": int(item["harga"]),
                "kategori": kategori_obj,
                "status": status_obj
            }
        )

    # -------------------------------------------------------------
    # // Setelah semua produk selesai diproses, saya tampilkan halaman
    # // sukses dengan informasi jumlah produk yang diimpor.
    # -------------------------------------------------------------
    return render(request, "import_sukses.html", {"total": len(api_data["data"])})



# =====================================================================
# // Fungsi berikutnya adalah list_produk.
# // Di halaman ini saya menampilkan semua produk sekaligus menampilkan
# // daftar produk yang memiliki status "bisa dijual" sesuai requirement.
# =====================================================================
def list_produk(request):
    # // Ambil semua produk dari database
    semua_produk = Produk.objects.all()

    # // Ambil hanya produk yang statusnya "bisa dijual"
    bisa_dijual = Produk.objects.filter(status__nama_status="bisa dijual")
    
    # // Render ke template list_produk.html
    return render(request, "produk/list_produk.html", {
        "semua_produk": semua_produk,
        "bisa_dijual": bisa_dijual
    })



# =====================================================================
# // Bagian berikutnya adalah fitur CRUD:
# // Tambah Produk, Edit Produk, dan Hapus Produk.
# // Semua menggunakan Django Forms agar validasi dapat berjalan otomatis.
# =====================================================================
from django.shortcuts import render, redirect, get_object_or_404
from .models import Produk
from .forms import ProdukForm



# =====================================================================
# TAMBAH PRODUK
# =====================================================================
def tambah_produk(request):
    # -------------------------------------------------------------
    # // Jika method POST, berarti user sedang mengirimkan form.
    # // Saya validasi dan simpan menggunakan Django ModelForm.
    # -------------------------------------------------------------
    if request.method == "POST":
        form = ProdukForm(request.POST)
        if form.is_valid():
            form.save()
            return redirect('list_produk')
    else:
        # // Jika GET, tampilkan form kosong.
        form = ProdukForm()

    return render(request, 'produk/form_produk.html', {
        'form': form,
        'judul': 'Tambah Produk'
    })



# =====================================================================
# EDIT PRODUK
# =====================================================================
def edit_produk(request, pk):
    # // Ambil produk berdasarkan primary key.
    produk = get_object_or_404(Produk, pk=pk)

    if request.method == "POST":
        # // Isi form dengan instance produk untuk diperbarui.
        form = ProdukForm(request.POST, instance=produk)
        if form.is_valid():
            form.save()
            return redirect('list_produk')
    else:
        # // Saat pertama dibuka, tampilkan form dengan data produk.
        form = ProdukForm(instance=produk)

    return render(request, 'produk/form_produk.html', {
        'form': form,
        'judul': 'Edit Produk'
    })



# =====================================================================
# HAPUS PRODUK
# =====================================================================
def hapus_produk(request, id):
    # // Ambil produk berdasarkan ID dan hapus dari database.
    produk = get_object_or_404(Produk, id=id)
    produk.delete()

    # // Setelah hapus, redirect kembali ke halaman list.
    return redirect('list_produk')
