from django.urls import path
from .views import import_produk, list_produk, tambah_produk, edit_produk, hapus_produk
from . import views 

# ==========================================================================
# // Pada file urls.py ini, saya mendefinisikan seluruh routing
# // untuk fitur manajemen produk. Semua URL di sini mengarah
# // ke fungsi view yang sudah saya buat sebelumnya.
#
# // Routing ini akan menentukan:
# // - URL untuk mengimpor data dari API
# // - URL untuk menampilkan daftar produk
# // - URL untuk tambah, edit, dan hapus produk
# ==========================================================================
urlpatterns = [
    # ----------------------------------------------------------------------
    # // URL untuk proses import produk dari API FastPrint.
    # // Ketika user membuka '/import/', fungsi import_produk() akan dijalankan.
    # ----------------------------------------------------------------------
    path('import/', import_produk, name='import_produk'),

    # ----------------------------------------------------------------------
    # // URL untuk menampilkan daftar semua produk dan daftar "bisa dijual".
    # // Fungsi list_produk() akan merender template list_produk.html.
    # ----------------------------------------------------------------------
    path('list/', list_produk, name='list_produk'),

    # ----------------------------------------------------------------------
    # // URL untuk halaman tambah produk baru.
    # // Menggunakan Django Form (ProdukForm) untuk validasi input.
    # ----------------------------------------------------------------------
    path('tambah/', tambah_produk, name='tambah_produk'),

    # ----------------------------------------------------------------------
    # // URL untuk halaman edit produk.
    # // Saya menggunakan primary key (pk) sebagai parameter.
    # ----------------------------------------------------------------------
    path('edit/<int:pk>/', edit_produk, name='edit_produk'),

    # ----------------------------------------------------------------------
    # // URL untuk menghapus produk berdasarkan id.
    # // Di sini saya memanggil views.hapus_produk karena fungsi hapus
    # // menggunakan parameter id, bukan pk.
    # ----------------------------------------------------------------------
    path('hapus/<int:id>/', views.hapus_produk, name='hapus_produk'),
]
