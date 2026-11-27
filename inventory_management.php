<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Divisi Alat') {
  header("Location: index.php");
  exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "peminjaman_alat";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_item') {
      $name = $_POST['name'];
      $description = $_POST['description'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];

      $sql = "INSERT INTO item (name, description, price, stock) VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssds", $name, $description, $price, $stock);
      $stmt->execute();
    }

    if ($action === 'edit_item') {
      $id = $_POST['id'];
      $name = $_POST['name'];
      $description = $_POST['description'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];

      $sql = "UPDATE item SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssdsi", $name, $description, $price, $stock, $id);
      $stmt->execute();
    }

    if ($action === 'delete_item') {
      $id = $_POST['id'];
      $sql = "DELETE FROM item WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      $stmt->execute();
    }
  }
}

// Fetch items
$items_result = $conn->query("SELECT * FROM item ORDER BY id DESC");
$items = [];
$total_units = 0;
$low_stock_count = 0;
if ($items_result) {
  while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
    $qty = (int)($row['stock'] ?? 0);
    $total_units += $qty;
    if ($qty <= 2) {
      $low_stock_count++;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Inventaris — D'Tools</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css?v=<?php echo filemtime('assets/css/theme.css'); ?>">
</head>

<body class="app-body has-bottom-nav">
  <header class="app-header">
    <div class="app-header-inner">
      <a href="divisi_dashboard.php" class="brand-lockup">
        <span class="brand-logo-wrap">
          <img src="logo.png" alt="Logo D'Tools">
        </span>
        <span class="brand-text">
          <span class="brand-title">D'Tools</span>
          <span class="brand-subtitle">Operasional Divisi Alat</span>
        </span>
      </a>

      <a href="logout.php" class="header-mobile-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
      <button class="nav-toggle" id="hamburger" type="button" aria-label="Toggle Navigation" aria-expanded="false">
        <i class="fas fa-bars"></i>
      </button>

      <nav class="app-nav" id="navbar" aria-label="Navigasi Divisi Alat">
        <span class="role-pill">Divisi Alat</span>
        <a href="divisi_dashboard.php"><i class="fas fa-house"></i> Home</a>
        <a href="inventory_management.php" class="active"><i class="fas fa-boxes-stacked"></i> Inventory</a>
        <a href="order-management.php"><i class="fas fa-clipboard-check"></i> Peminjaman</a>
        <a href="logout.php" class="nav-logout"><i class="fas fa-right-from-bracket"></i> Logout</a>
      </nav>
    </div>
  </header>

  <main class="app-main">
    <div class="page-header">
      <h1 class="page-title">Inventory Management</h1>
      <div class="page-actions">
        <button type="button" class="btn-brand" onclick="togglePopup('add')">
          <i class="fas fa-plus"></i> Tambah Alat
        </button>
      </div>
    </div>

    <div class="metric-strip" aria-label="Statistik Inventaris">
      <div class="metric-chip"><span class="dot crimson"></span><strong><?php echo count($items); ?></strong> Jenis Alat</div>
      <div class="metric-chip"><span class="dot emerald"></span><strong><?php echo $total_units; ?></strong> Total Unit</div>
      <div class="metric-chip"><span class="dot amber"></span><strong><?php echo $low_stock_count; ?></strong> Stok Menipis</div>
    </div>

    <section class="surface-card">
      <div class="surface-card-body">
        <div class="dt-table-wrap">
          <table id="itemTable" class="dt-table dt-mobile-list">
            <thead>
              <tr>
                <th>Nama Alat</th>
                <th>Deskripsi</th>
                <th>Harga / Minggu</th>
                <th>Stok</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $row) : ?>
                <?php
                $stock = (int)$row['stock'];
                $stockClass = $stock <= 0 ? 'empty' : ($stock <= 2 ? 'low' : '');
                ?>
                <tr>
                  <td class="cell-strong col-primary" data-label="Nama Alat"><?= htmlspecialchars($row['name']) ?></td>
                  <td data-label="Deskripsi"><?= htmlspecialchars($row['description']) ?></td>
                  <td class="cell-mono" data-label="Harga / Minggu">Rp <?= number_format((float)$row['price'], 0, ',', '.') ?></td>
                  <td data-label="Stok"><span class="stock-pill <?= $stockClass ?>"><?= $stock ?> unit</span></td>
                  <td class="col-actions" data-label="Aksi">
                    <div class="cell-actions">
                      <button type="button"
                        class="btn-outline-subtle btn-sm-action edit-item-btn"
                        data-id="<?= (int)$row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>"
                        data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') ?>"
                        data-price="<?= htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8') ?>"
                        data-stock="<?= (int)$row['stock'] ?>">
                        <i class="fas fa-pen"></i> Edit
                      </button>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus alat ini dari inventaris?');">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button type="submit" class="btn-soft-danger btn-sm-action">
                          <i class="fas fa-trash-can"></i> Hapus
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal Tambah Alat -->
  <div class="dt-modal-backdrop" id="popup-add" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="dt-modal">
      <div class="dt-modal-header">
        <h3 id="addModalTitle"><i class="fas fa-plus-circle" style="color: var(--brand-crimson); margin-right: 8px;"></i>Tambah Alat Baru</h3>
        <button type="button" class="dt-modal-close" onclick="togglePopup('add')" aria-label="Tutup">&times;</button>
      </div>
      <div class="dt-modal-body">
        <form method="POST">
          <input type="hidden" name="action" value="add_item">
          <div class="form-group">
            <label class="form-label-dt" for="add-name">Nama Alat</label>
            <input type="text" id="add-name" name="name" class="form-control-dt" placeholder="Contoh: Pulse Meter" required>
          </div>
          <div class="form-group">
            <label class="form-label-dt" for="add-description">Deskripsi</label>
            <textarea id="add-description" name="description" class="form-control-dt" rows="3" placeholder="Kategori atau keterangan alat" required></textarea>
          </div>
          <div class="modal-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
              <label class="form-label-dt" for="add-price">Harga Sewa (Rp)</label>
              <input type="number" id="add-price" name="price" step="0.01" class="form-control-dt" required>
            </div>
            <div class="form-group">
              <label class="form-label-dt" for="add-stock">Stok Unit</label>
              <input type="number" id="add-stock" name="stock" class="form-control-dt" required>
            </div>
          </div>
          <div class="dt-modal-actions">
            <button type="button" class="btn-outline-subtle" onclick="togglePopup('add')">Batal</button>
            <button type="submit" class="btn-brand"><i class="fas fa-floppy-disk"></i> Simpan Alat</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Alat -->
  <div class="dt-modal-backdrop" id="popup-edit" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="dt-modal">
      <div class="dt-modal-header">
        <h3 id="editModalTitle"><i class="fas fa-pen-to-square" style="color: var(--brand-crimson); margin-right: 8px;"></i>Edit Data Alat</h3>
        <button type="button" class="dt-modal-close" onclick="togglePopup('edit')" aria-label="Tutup">&times;</button>
      </div>
      <div class="dt-modal-body">
        <form method="POST">
          <input type="hidden" name="action" value="edit_item">
          <input type="hidden" name="id" id="edit-id">
          <div class="form-group">
            <label class="form-label-dt" for="edit-name">Nama Alat</label>
            <input type="text" name="name" id="edit-name" class="form-control-dt" required>
          </div>
          <div class="form-group">
            <label class="form-label-dt" for="edit-description">Deskripsi</label>
            <textarea name="description" id="edit-description" class="form-control-dt" rows="3" required></textarea>
          </div>
          <div class="modal-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
              <label class="form-label-dt" for="edit-price">Harga Sewa (Rp)</label>
              <input type="number" name="price" id="edit-price" step="0.01" class="form-control-dt" required>
            </div>
            <div class="form-group">
              <label class="form-label-dt" for="edit-stock">Stok Unit</label>
              <input type="number" name="stock" id="edit-stock" class="form-control-dt" required>
            </div>
          </div>
          <div class="dt-modal-actions">
            <button type="button" class="btn-outline-subtle" onclick="togglePopup('edit')">Batal</button>
            <button type="submit" class="btn-brand"><i class="fas fa-check"></i> Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <footer class="app-footer">
    <div class="app-footer-inner">
      <p>&copy; 2024 D'Tools — Sistem Informasi Peminjaman Alat. All Rights Reserved.</p>
      <p>Portal Divisi Alat</p>
    </div>
  </footer>

  <nav class="mobile-bottom-nav" aria-label="Navigasi Cepat Divisi Alat">
    <a href="divisi_dashboard.php"><i class="fas fa-house"></i><span>Home</span></a>
    <a href="inventory_management.php" class="active"><i class="fas fa-boxes-stacked"></i><span>Inventory</span></a>
    <a href="order-management.php"><i class="fas fa-clipboard-check"></i><span>Peminjaman</span></a>
  </nav>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#itemTable').DataTable({
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ entri",
          info: "Menampilkan _START_–_END_ dari _TOTAL_ alat",
          emptyTable: "Belum ada data inventaris.",
          paginate: {
            next: "Berikutnya",
            previous: "Sebelumnya"
          }
        }
      });
    });

    function togglePopup(popupType) {
      const popup = document.getElementById(`popup-${popupType}`);
      popup.classList.toggle('active');
    }

    document.querySelectorAll('.dt-modal-backdrop').forEach(backdrop => {
      backdrop.addEventListener('click', function (e) {
        if (e.target === this) {
          this.classList.remove('active');
        }
      });
    });

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.edit-item-btn');
      if (!btn) return;
      document.getElementById('edit-id').value = btn.dataset.id;
      document.getElementById('edit-name').value = btn.dataset.name;
      document.getElementById('edit-description').value = btn.dataset.description;
      document.getElementById('edit-price').value = btn.dataset.price;
      document.getElementById('edit-stock').value = btn.dataset.stock;
      togglePopup('edit');
    });

    const hamburger = document.getElementById('hamburger');
    const navbar = document.getElementById('navbar');
    hamburger.addEventListener('click', () => {
      const isOpen = navbar.classList.toggle('active');
      hamburger.setAttribute('aria-expanded', isOpen);
    });
  </script>
</body>

</html>

<?php $conn->close(); ?>
