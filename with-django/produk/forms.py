from django import forms
from .models import Produk

# ======================================================================
# // Pada file forms.py ini, saya membuat Django ModelForm untuk
# // menangani input data produk. Tujuan penggunaan ModelForm adalah
# // agar proses validasi, binding data, dan penyimpanan ke database
# // menjadi lebih aman dan lebih praktis.
#
# // Selain itu, requirement dari tes FastPrint mengharuskan adanya
# // validasi form:
# // - Nama produk wajib diisi
# // - Harga harus berupa angka
# // Validasi tersebut saya tangani melalui clean methods di bawah.
# ======================================================================
class ProdukForm(forms.ModelForm):
    class Meta:
        # -------------------------------------------------------------
        # // Form ini terhubung langsung dengan model Produk,
        # // sehingga field yang tampil otomatis sesuai model.
        # -------------------------------------------------------------
        model = Produk
        fields = ['nama_produk', 'harga', 'kategori', 'status']


    # ==============================================================
    #  Validasi nama_produk
    # ==============================================================
    def clean_nama_produk(self):
        # // Ambil nilai input nama_produk dari form
        nama = self.cleaned_data.get('nama_produk')

        # // Requirement: nama produk wajib diisi
        if not nama:
            raise forms.ValidationError("Nama produk harus diisi")

        return nama


    # ==============================================================
    #  Validasi harga
    # ==============================================================
    def clean_harga(self):
        # // Ambil nilai input harga dari form
        harga = self.cleaned_data.get('harga')

        # // Jika kosong → error
        if harga is None:
            raise forms.ValidationError("Harga harus diisi")

        # // Jika bukan integer → coba convert
        # // Kalau gagal convert → error
        if not isinstance(harga, int):
            try:
                harga = int(harga)
            except ValueError:
                raise forms.ValidationError("Harga harus berupa angka")

        # // Jika lolos semua validasi, kembalikan nilainya
        return harga
