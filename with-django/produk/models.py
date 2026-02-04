from django.db import models

# ============================================================
# // Pada file ini, saya mendefinisikan struktur database
# // menggunakan Django ORM. Ketiga model ini akan
# // otomatis menjadi tabel di database setelah dilakukan migrasi.
# ============================================================


class Kategori(models.Model):
    # --------------------------------------------------------
    # // Model pertama adalah Kategori.
    # // Tabel ini menyimpan daftar kategori produk.
    # // Field 'nama_kategori' menggunakan CharField
    # // dengan panjang maksimum 100 karakter.
    # --------------------------------------------------------
    nama_kategori = models.CharField(max_length=100)

    def __str__(self):
        # ----------------------------------------------------
        # // Method __str__ digunakan agar ketika data kategori
        # // ditampilkan di admin atau di konsol, yang muncul
        # // adalah nama kategorinya, bukan object ID.
        # ----------------------------------------------------
        return self.nama_kategori


class Status(models.Model):
    # --------------------------------------------------------
    # // Model berikutnya adalah Status.
    # // Tabel ini menyimpan status produk seperti
    # // "bisa dijual" atau "tidak bisa dijual".
    # // Menggunakan CharField dengan panjang maksimal 50.
    # --------------------------------------------------------
    nama_status = models.CharField(max_length=50)

    def __str__(self):
        # ----------------------------------------------------
        # // Sama seperti kategori, __str__ akan membantu
        # // menampilkan nama status secara lebih human readable.
        # ----------------------------------------------------
        return self.nama_status


class Produk(models.Model):
    # --------------------------------------------------------
    # // Model terakhir adalah Produk.
    # // Model ini memenuhi requirement utama dari tes,
    # // yaitu menyimpan data produk yang berasal dari API.
    # --------------------------------------------------------

    # // Field nama produk, maksimal 150 karakter.
    nama_produk = models.CharField(max_length=150)

    # // Field harga menggunakan IntegerField,
    # // karena API memberikan harga dalam bentuk angka.
    harga = models.IntegerField()

    # --------------------------------------------------------
    # // Field kategori dan status menggunakan ForeignKey.
    # // Ini berarti setiap produk akan memiliki relasi ke
    # // satu kategori dan satu status.
    # // on_delete=models.CASCADE digunakan agar jika kategori
    # // atau status dihapus, produk yang terkait ikut terhapus.
    # --------------------------------------------------------
    kategori = models.ForeignKey(Kategori, on_delete=models.CASCADE)
    status = models.ForeignKey(Status, on_delete=models.CASCADE)

    def __str__(self):
        # ----------------------------------------------------
        # // Mengembalikan nama produk hanya untuk memudahkan
        # // debugging dan tampilan di Django admin.
        # ----------------------------------------------------
        return self.nama_produk
