<?php
// index.php — CRUD Stock, Orders, Order Items (satu halaman)

// Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi DB
include 'db.php';

// --------- Helper kecil ---------
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect_self() {
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ===============================
// 1) CRUD: STOCK
// ===============================

// CREATE stock
if (isset($_POST['stock_create'])) {
    $sku  = $_POST['sku'] ?? '';
    $name = $_POST['name'] ?? '';
    $qty  = $_POST['qty'] ?? '';

    if ($sku !== '' && $name !== '' && is_numeric($qty) && $qty >= 0) {
        $q = "INSERT INTO stock (sku, name, qty) VALUES ($1, $2, $3)";
        $ok = pg_query_params($conn, $q, array($sku, $name, (int)$qty));
        if (!$ok) echo "<p style='color:red'>Gagal menambah stock: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Stock berhasil ditambahkan.</p>";
    } else {
        echo "<p style='color:red'>Input stock tidak valid.</p>";
    }
}

// UPDATE stock
if (isset($_POST['stock_update'])) {
    $sku  = $_POST['sku'] ?? '';
    $name = $_POST['name'] ?? '';
    $qty  = $_POST['qty'] ?? '';

    if ($sku !== '' && $name !== '' && is_numeric($qty) && $qty >= 0) {
        $q = "UPDATE stock SET name = $1, qty = $2 WHERE sku = $3";
        $ok = pg_query_params($conn, $q, array($name, (int)$qty, $sku));
        if (!$ok) echo "<p style='color:red'>Gagal update stock: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Stock berhasil diupdate.</p>";
    } else {
        echo "<p style='color:red'>Input update stock tidak valid.</p>";
    }
}

// DELETE stock
if (isset($_GET['stock_delete'])) {
    $sku_del = $_GET['stock_delete'];
    $q = "DELETE FROM stock WHERE sku = $1";
    $ok = pg_query_params($conn, $q, array($sku_del));
    if (!$ok) echo "<p style='color:red'>Gagal hapus stock: ".h(pg_last_error($conn))."</p>";
    else echo "<p style='color:green'>Stock SKU ".h($sku_del)." berhasil dihapus.</p>";
}

// Ambil data untuk form edit stock
$stock_edit_data = null;
if (isset($_GET['stock_edit'])) {
    $sku_edit = $_GET['stock_edit'];
    $res = pg_query_params($conn, "SELECT * FROM stock WHERE sku = $1", array($sku_edit));
    $stock_edit_data = pg_fetch_assoc($res);
}

// ===============================
// 2) CRUD: ORDERS
//   orders(id serial PK, customer text, status text CHECK IN ('NEW','PAID','CANCELLED'))
//   ON DELETE CASCADE sudah di LKP 11 untuk order_items
// ===============================

// CREATE order
if (isset($_POST['order_create'])) {
    $customer = $_POST['customer'] ?? '';
    $status   = $_POST['status'] ?? 'NEW';
    $allowed  = ['NEW','PAID','CANCELLED'];

    if ($customer !== '' && in_array($status, $allowed, true)) {
        $q = "INSERT INTO orders (customer, status) VALUES ($1, $2)";
        $ok = pg_query_params($conn, $q, array($customer, $status));
        if (!$ok) echo "<p style='color:red'>Gagal menambah order: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Order berhasil ditambahkan.</p>";
    } else {
        echo "<p style='color:red'>Input order tidak valid.</p>";
    }
}

// UPDATE order status
if (isset($_POST['order_update'])) {
    $order_id = $_POST['order_id'] ?? '';
    $status   = $_POST['status'] ?? 'NEW';
    $allowed  = ['NEW','PAID','CANCELLED'];

    if (is_numeric($order_id) && in_array($status, $allowed, true)) {
        $q = "UPDATE orders SET status = $1 WHERE id = $2";
        $ok = pg_query_params($conn, $q, array($status, (int)$order_id));
        if (!$ok) echo "<p style='color:red'>Gagal update status: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Status order #".h($order_id)." berhasil diupdate.</p>";
    } else {
        echo "<p style='color:red'>Input update order tidak valid.</p>";
    }
}

// DELETE order (cascade ke order_items)
if (isset($_GET['order_delete'])) {
    $id_del = $_GET['order_delete'];
    if (is_numeric($id_del)) {
        $q = "DELETE FROM orders WHERE id = $1";
        $ok = pg_query_params($conn, $q, array((int)$id_del));
        if (!$ok) echo "<p style='color:red'>Gagal hapus order: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Order #".h($id_del)." berhasil dihapus (items terhapus otomatis).</p>";
    }
}

// Ambil data untuk form edit order
$order_edit_data = null;
if (isset($_GET['order_edit'])) {
    $id_edit = $_GET['order_edit'];
    if (is_numeric($id_edit)) {
        $res = pg_query_params($conn, "SELECT * FROM orders WHERE id = $1", array((int)$id_edit));
        $order_edit_data = pg_fetch_assoc($res);
    }
}

// ===============================
// 3) CRUD: ORDER_ITEMS
//   order_items(id serial PK, order_id int FK, sku text FK, qty int, price numeric)
// ===============================

// CREATE order_item
if (isset($_POST['item_create'])) {
    $order_id = $_POST['order_id'] ?? '';
    $sku      = $_POST['sku'] ?? '';
    $qty      = $_POST['qty'] ?? '';
    $price    = $_POST['price'] ?? '';

    if (is_numeric($order_id) && $sku !== '' && is_numeric($qty) && $qty > 0 && is_numeric($price) && $price >= 0) {
        $q = "INSERT INTO order_items (order_id, sku, qty, price) VALUES ($1, $2, $3, $4)";
        $ok = pg_query_params($conn, $q, array((int)$order_id, $sku, (int)$qty, (float)$price));
        if (!$ok) echo "<p style='color:red'>Gagal menambah item: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Item berhasil ditambahkan ke order #".h($order_id).".</p>";
    } else {
        echo "<p style='color:red'>Input item tidak valid.</p>";
    }
}

// UPDATE order_item
if (isset($_POST['item_update'])) {
    $id    = $_POST['id'] ?? '';
    $qty   = $_POST['qty'] ?? '';
    $price = $_POST['price'] ?? '';

    if (is_numeric($id) && is_numeric($qty) && $qty > 0 && is_numeric($price) && $price >= 0) {
        $q = "UPDATE order_items SET qty = $1, price = $2 WHERE id = $3";
        $ok = pg_query_params($conn, $q, array((int)$qty, (float)$price, (int)$id));
        if (!$ok) echo "<p style='color:red'>Gagal update item: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Item #".h($id)." berhasil diupdate.</p>";
    } else {
        echo "<p style='color:red'>Input update item tidak valid.</p>";
    }
}

// DELETE order_item
if (isset($_GET['item_delete'])) {
    $id_del = $_GET['item_delete'];
    if (is_numeric($id_del)) {
        $q = "DELETE FROM order_items WHERE id = $1";
        $ok = pg_query_params($conn, $q, array((int)$id_del));
        if (!$ok) echo "<p style='color:red'>Gagal hapus item: ".h(pg_last_error($conn))."</p>";
        else echo "<p style='color:green'>Item #".h($id_del)." berhasil dihapus.</p>";
    }
}

// Ambil data untuk form edit item
$item_edit_data = null;
if (isset($_GET['item_edit'])) {
    $id_edit = $_GET['item_edit'];
    if (is_numeric($id_edit)) {
        $res = pg_query_params($conn, "SELECT * FROM order_items WHERE id = $1", array((int)$id_edit));
        $item_edit_data = pg_fetch_assoc($res);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Dashboard Data - lab_simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; }
        h1 { text-align: center; margin-bottom: 24px; }
        h2 { margin-top: 32px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        form.inline { display: inline-block; margin-right: 8px; }
        input, select, button { padding: 6px 8px; margin: 4px; }
        .small { width: 120px; }
        .num { width: 100px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; }
        .card { border: 1px solid #ccc; padding: 12px; border-radius: 6px; background: #fafafa; }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
    <h1>Dashboard Data - Skema lab_simple</h1>

    <!-- ===================== -->
    <!-- STOCK SECTION -->
    <!-- ===================== -->
    <h2>📦 Data Stock</h2>
    <?php
    // Read stock
    $stock_res = pg_query($conn, "SELECT sku, name, qty FROM stock ORDER BY sku ASC");
    echo "<table><tr><th>SKU</th><th>Nama Barang</th><th>Qty</th><th>Aksi</th></tr>";
    while ($row = pg_fetch_assoc($stock_res)) {
        echo "<tr>
            <td>".h($row['sku'])."</td>
            <td>".h($row['name'])."</td>
            <td>".h($row['qty'])."</td>
            <td class='actions'>
                <a href='?stock_edit=".urlencode($row['sku'])."'>Edit</a>
                <a href='?stock_delete=".urlencode($row['sku'])."' onclick=\"return confirm('Hapus SKU: " . h($row['sku']) . " ?')\">Hapus</a>
            </td>
        </tr>";
    }
    echo "</table>";
    ?>

    <div class="grid">
        <div class="card">
            <h3>➕ Tambah Stock</h3>
            <form method="POST" action="">
                <input class="small" type="text" name="sku" placeholder="SKU" required />
                <input type="text" name="name" placeholder="Nama Barang" required />
                <input class="num" type="number" name="qty" placeholder="Qty" min="0" required />
                <button type="submit" name="stock_create">Simpan</button>
            </form>
        </div>

        <?php if ($stock_edit_data): ?>
        <div class="card">
            <h3>✏️ Edit Stock</h3>
            <form method="POST" action="">
                <input type="hidden" name="sku" value="<?= h($stock_edit_data['sku']) ?>" />
                <input type="text" name="name" value="<?= h($stock_edit_data['name']) ?>" required />
                <input class="num" type="number" name="qty" value="<?= h($stock_edit_data['qty']) ?>" min="0" required />
                <button type="submit" name="stock_update">Update</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===================== -->
    <!-- ORDERS SECTION -->
    <!-- ===================== -->
    <h2>🧾 Data Orders</h2>
    <?php
    // Read orders
    $orders_res = pg_query($conn, "SELECT id, customer, status FROM orders ORDER BY id DESC");
    echo "<table><tr><th>ID</th><th>Customer</th><th>Status</th><th>Aksi</th></tr>";
    while ($row = pg_fetch_assoc($orders_res)) {
        echo "<tr>
            <td>#".h($row['id'])."</td>
            <td>".h($row['customer'])."</td>
            <td>".h($row['status'])."</td>
            <td class='actions'>
                <a href='?order_edit=".urlencode($row['id'])."'>Edit Status</a>
                <a href='?order_delete=".urlencode($row['id'])."' onclick=\"return confirm('Hapus Order #" . h($row['id']) . " ?')\">Hapus</a>
            </td>
        </tr>";
    }
    echo "</table>";
    ?>

    <div class="grid">
        <div class="card">
            <h3>➕ Buat Order</h3>
            <form method="POST" action="">
                <input type="text" name="customer" placeholder="Nama Customer" required />
                <select name="status" required>
                    <option value="NEW">NEW</option>
                    <option value="PAID">PAID</option>
                    <option value="CANCELLED">CANCELLED</option>
                </select>
                <button type="submit" name="order_create">Simpan</button>
            </form>
        </div>

        <?php if ($order_edit_data): ?>
        <div class="card">
            <h3>✏️ Ubah Status Order #<?= h($order_edit_data['id']) ?></h3>
            <form method="POST" action="">
                <input type="hidden" name="order_id" value="<?= h((int)$order_edit_data['id']) ?>" />
                <select name="status" required>
                    <?php
                        $opts = ['NEW','PAID','CANCELLED'];
                        foreach ($opts as $opt) {
                            $sel = ($opt === $order_edit_data['status']) ? "selected" : "";
                            echo "<option value='".h($opt)."' $sel>".h($opt)."</option>";
                        }
                    ?>
                </select>
                <button type="submit" name="order_update">Update</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===================== -->
    <!-- ORDER ITEMS SECTION -->
    <!-- ===================== -->
    <h2>🧺 Data Order Items</h2>
    <?php
    // Read items (join dengan stock untuk nama barang)
    $items_q = "
        SELECT oi.id, oi.order_id, oi.sku, s.name AS stock_name, oi.qty, oi.price
        FROM order_items oi
        LEFT JOIN stock s ON s.sku = oi.sku
        ORDER BY oi.id DESC
    ";
    $items_res = pg_query($conn, $items_q);
    echo "<table><tr><th>ID</th><th>Order</th><th>SKU</th><th>Nama Barang</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Aksi</th></tr>";
    while ($row = pg_fetch_assoc($items_res)) {
        $subtotal = ((float)$row['qty']) * ((float)$row['price']);
        echo "<tr>
            <td>#".h($row['id'])."</td>
            <td>#".h($row['order_id'])."</td>
            <td>".h($row['sku'])."</td>
            <td>".h($row['stock_name'])."</td>
            <td>".h($row['qty'])."</td>
            <td>".h($row['price'])."</td>
            <td>".number_format($subtotal, 2)."</td>
            <td class='actions'>
                <a href='?item_edit=".urlencode($row['id'])."'>Edit</a>
                <a href='?item_delete=".urlencode($row['id'])."' onclick=\"return confirm('Hapus Item #" . h($row['id']) . " ?')\">Hapus</a>
            </td>
        </tr>";
    }
    echo "</table>";
    ?>

    <div class="grid">
        <div class="card">
            <h3>➕ Tambah Item ke Order</h3>
            <form method="POST" action="">
                <input class="num" type="number" name="order_id" placeholder="Order ID" min="1" required />
                <input class="small" type="text" name="sku" placeholder="SKU" required />
                <input class="num" type="number" name="qty" placeholder="Qty" min="1" required />
                <input class="num" type="number" step="0.01" name="price" placeholder="Price" min="0" required />
                <button type="submit" name="item_create">Simpan</button>
            </form>
        </div>

        <?php if ($item_edit_data): ?>
        <div class="card">
            <h3>✏️ Edit Item #<?= h($item_edit_data['id']) ?></h3>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?= h((int)$item_edit_data['id']) ?>" />
                <input class="num" type="number" name="qty" value="<?= h($item_edit_data['qty']) ?>" min="1" required />
                <input class="num" type="number" step="0.01" name="price" value="<?= h($item_edit_data['price']) ?>" min="0" required />
                <button type="submit" name="item_update">Update</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <hr />
    <p><strong>Tips:</strong> untuk menghindari duplikat submit saat refresh, pertimbangkan menambahkan redirect setelah operasi sukses.</p>
</body>
</html>