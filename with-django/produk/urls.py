from django.urls import path
from .views import import_produk, list_produk, tambah_produk, edit_produk, hapus_produk
from . import views   # 👈 INI YANG KURANG

urlpatterns = [
    path('import/', import_produk, name='import_produk'),
    path('list/', list_produk, name='list_produk'),
    path('tambah/', tambah_produk, name='tambah_produk'),
    path('edit/<int:pk>/', edit_produk, name='edit_produk'),
    path('hapus/<int:id>/', views.hapus_produk, name='hapus_produk'),
]
