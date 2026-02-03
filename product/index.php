<?php
require "../config/db.php";

$aksi = $_GET['aksi'] ?? 'list';
$id = $_GET['id'] ?? null;
$errors = [];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-action {
            padding: 3px 10px;
            font-size: 14px;
        }
        .alert-custom {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <?php
        /* =====================================================
           HAPUS PRODUK
        ===================================================== */
        if ($aksi == 'hapus' && $id) {
            mysqli_query($conn, "DELETE FROM produk WHERE id_produk='$id'");
            header("Location: super.php");
            exit;
        }

        /* =====================================================
           PROSES SIMPAN (TAMBAH / EDIT)
        ===================================================== */
        if (isset($_POST['submit'])) {

            $nama = trim($_POST['nama_produk']);
            $harga = trim($_POST['harga']);
            $kategori_id = $_POST['kategori_id'];
            $status_id = $_POST['status_id'];

            if ($nama == "") {
                $errors[] = "Nama produk wajib diisi";
            }
            if (!is_numeric($harga)) {
                $errors[] = "Harga harus angka";
            }

            if (empty($errors)) {
                if ($aksi == 'edit') {
                    mysqli_query($conn, "
                    UPDATE produk SET
                        nama_produk='$nama',
                        harga='$harga',
                        kategori_id='$kategori_id',
                        status_id='$status_id'
                    WHERE id_produk='$id'
                ");
                } else {
                    mysqli_query($conn, "
                    INSERT INTO produk(nama_produk, harga, kategori_id, status_id)
                    VALUES('$nama', '$harga', '$kategori_id', '$status_id')
                ");
                }
                header("Location: super.php");
                exit;
            }
        }

        /* =====================================================
           FORM TAMBAH & EDIT
        ===================================================== */
        if ($aksi == 'tambah' || $aksi == 'edit') {

            if ($aksi == 'edit') {
                $q = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='$id'");
                $produk = mysqli_fetch_assoc($q);
                if (!$produk)
                    die("Produk tidak ditemukan");
            }

            $kategori = mysqli_query($conn, "SELECT * FROM kategori");
            $status = mysqli_query($conn, "SELECT * FROM status");
            ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>
                        <?= $aksi == 'edit' ? 'Edit Produk' : 'Tambah Produk Baru' ?>
                    </h5>
                    <a href="super.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-custom">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Error Validasi</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= $e ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_produk" class="form-label">
                                    <i class="bi bi-tag me-1"></i> Nama Produk *
                                </label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                       value="<?= htmlspecialchars($produk['nama_produk'] ?? '') ?>" required>
                                <div class="invalid-feedback">
                                    Harap isi nama produk.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label">
                                    <i class="bi bi-currency-dollar me-1"></i> Harga *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga" name="harga" 
                                           value="<?= htmlspecialchars($produk['harga'] ?? '') ?>" required min="0" step="100">
                                </div>
                                <div class="invalid-feedback">
                                    Harap isi harga yang valid.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="kategori_id" class="form-label">
                                    <i class="bi bi-grid me-1"></i> Kategori *
                                </label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                        <option value="<?= $k['id_kategori'] ?>" 
                                            <?= ($produk['kategori_id'] ?? '') == $k['id_kategori'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama_kategori']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Harap pilih kategori.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status_id" class="form-label">
                                    <i class="bi bi-info-circle me-1"></i> Status *
                                </label>
                                <select class="form-select" id="status_id" name="status_id" required>
                                    <option value="">Pilih Status</option>
                                    <?php while ($s = mysqli_fetch_assoc($status)): ?>
                                        <option value="<?= $s['id_status'] ?>" 
                                            <?= ($produk['status_id'] ?? '') == $s['id_status'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['nama_status']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Harap pilih status.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Simpan
                            </button>
                            <a href="super.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            /* =====================================================
               LIST PRODUK BISA DIJUAL
            ===================================================== */
        } elseif ($aksi == 'bisa_dijual') {

            $q = mysqli_query($conn, "
            SELECT produk.*, kategori.nama_kategori
            FROM produk
            JOIN kategori ON kategori.id_kategori = produk.kategori_id
            JOIN status ON status.id_status = produk.status_id
            WHERE status.nama_status='bisa dijual'
        ");
            ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cart-check me-2"></i> Produk Bisa Dijual
                    </h5>
                    <div>
                        <a href="super.php" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($p = mysqli_fetch_assoc($q)): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                                        <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($p['nama_kategori']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php
            /* =====================================================
               LIST PRODUK (DEFAULT)
            ===================================================== */
        } else {

            $q = mysqli_query($conn, "
            SELECT produk.*, kategori.nama_kategori, status.nama_status
            FROM produk
            JOIN kategori ON kategori.id_kategori = produk.kategori_id
            JOIN status ON status.id_status = produk.status_id
            ORDER BY id_produk DESC
        ");
            ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-boxes me-2"></i> Daftar Produk
                    </h5>
                    <div>
                        <a href="super.php?aksi=bisa_dijual" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-cart-check me-1"></i> Produk Bisa Dijual
                        </a>
                        <a href="super.php?aksi=tambah" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Produk
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php 
                    $row_count = mysqli_num_rows($q);
                    if ($row_count > 0): 
                    ?>
                        <div class="alert alert-info alert-custom d-flex align-items-center">
                            <i class="bi bi-info-circle me-2 fs-5"></i>
                            <div>Menampilkan <strong><?= $row_count ?></strong> produk dalam database.</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($p = mysqli_fetch_assoc($q)): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                                        <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($p['nama_kategori']) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?= $p['nama_status'] == 'bisa dijual' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($p['nama_status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-buttons justify-content-center">
                                                <a href="super.php?aksi=edit&id=<?= $p['id_produk'] ?>" 
                                                   class="btn btn-warning btn-sm btn-action">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <a href="super.php?aksi=hapus&id=<?= $p['id_produk'] ?>" 
                                                   class="btn btn-danger btn-sm btn-action"
                                                   onclick="return confirm('Yakin ingin menghapus produk <?= htmlspecialchars(addslashes($p['nama_produk'])) ?>?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($row_count == 0): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <h5 class="mt-3 text-muted">Belum ada data produk</h5>
                                <p class="text-muted">Silakan tambah produk baru dengan menekan tombol "Tambah Produk"</p>
                                <a href="super.php?aksi=tambah" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Produk
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php } ?>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form validation -->
    <script>
        (function() {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>

</html>