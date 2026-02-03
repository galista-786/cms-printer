from django.shortcuts import render
from .models import Produk, Kategori, Status
from .services import get_produk_api

def import_produk(request):
    api_data = get_produk_api()

    if api_data.get("error") != 0:
        return render(request, "import_error.html", {"message": api_data.get("ket")})

    for item in api_data["data"]:
        # Dapatkan atau buat kategori
        kategori_obj, _ = Kategori.objects.get_or_create(
            nama_kategori=item["kategori"]
        )

        # Dapatkan atau buat status
        status_obj, _ = Status.objects.get_or_create(
            nama_status=item["status"]
        )

        # Simpan produk
        Produk.objects.update_or_create(
            id=item["id_produk"],  # pakai id dari API supaya tidak duplikat
            defaults={
                "nama_produk": item["nama_produk"],
                "harga": int(item["harga"]),
                "kategori": kategori_obj,
                "status": status_obj
            }
        )

    return render(request, "import_sukses.html", {"total": len(api_data["data"])})

def list_produk(request):
    # Tampilkan semua produk
    semua_produk = Produk.objects.all()
    # Tampilkan hanya produk "bisa dijual"
    bisa_dijual = Produk.objects.filter(status__nama_status="bisa dijual")
    
    return render(request, "produk/list_produk.html", {
        "semua_produk": semua_produk,
        "bisa_dijual": bisa_dijual
    })

from django.shortcuts import render, redirect, get_object_or_404
from .models import Produk
from .forms import ProdukForm

# Tambah Produk
def tambah_produk(request):
    if request.method == "POST":
        form = ProdukForm(request.POST)
        if form.is_valid():
            form.save()
            return redirect('list_produk')
    else:
        form = ProdukForm()
    return render(request, 'produk/form_produk.html', {'form': form, 'judul': 'Tambah Produk'})

# Edit Produk
def edit_produk(request, pk):
    produk = get_object_or_404(Produk, pk=pk)
    if request.method == "POST":
        form = ProdukForm(request.POST, instance=produk)
        if form.is_valid():
            form.save()
            return redirect('list_produk')
    else:
        form = ProdukForm(instance=produk)
    return render(request, 'produk/form_produk.html', {'form': form, 'judul': 'Edit Produk'})

# Hapus Produk
def hapus_produk(request, id):
    produk = get_object_or_404(Produk, id=id)
    produk.delete()
    return redirect('list_produk')