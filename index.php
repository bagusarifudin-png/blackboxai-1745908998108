<?php
session_start();
require 'db.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    header("Location: index.php");
    exit();
}

// Handle checkout
if (isset($_POST['checkout'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Get product price
        $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            if ($product['stock'] >= $quantity) {
                $total_price = $product['price'] * $quantity;
                // Insert transaction
                $stmt_insert = $conn->prepare("INSERT INTO transactions (product_id, quantity, total_price) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("iid", $product_id, $quantity, $total_price);
                $stmt_insert->execute();
                // Update stock
                $new_stock = $product['stock'] - $quantity;
                $stmt_update = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $stmt_update->bind_param("ii", $new_stock, $product_id);
                $stmt_update->execute();
            }
        }
        $stmt->close();
    }
    $_SESSION['cart'] = [];
    header("Location: receipt.php");
    exit();
}

// Fetch products
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kasir Web App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-6">Aplikasi Kasir</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h2 class="text-xl font-semibold mb-4">Produk</h2>
                <form method="POST" class="space-y-4">
                    <select name="product_id" class="w-full border border-gray-300 rounded p-2" required>
                        <option value="" disabled selected>Pilih produk</option>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> - Rp <?= number_format($row['price'], 2, ',', '.') ?> (Stok: <?= $row['stock'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" name="quantity" min="1" value="1" class="w-full border border-gray-300 rounded p-2" required />
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Tambah ke Keranjang</button>
                </form>
            </div>
            <div class="md:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Keranjang</h2>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <form method="POST">
                        <table class="w-full border border-gray-300 rounded mb-4">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-2 border-b border-gray-300 text-left">Produk</th>
                                    <th class="p-2 border-b border-gray-300 text-right">Jumlah</th>
                                    <th class="p-2 border-b border-gray-300 text-right">Harga</th>
                                    <th class="p-2 border-b border-gray-300 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                foreach ($_SESSION['cart'] as $product_id => $quantity):
                                    $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
                                    $stmt->bind_param("i", $product_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $product = $result->fetch_assoc();
                                    $subtotal = $product['price'] * $quantity;
                                    $total += $subtotal;
                                    $stmt->close();
                                ?>
                                <tr>
                                    <td class="p-2 border-b border-gray-300"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="p-2 border-b border-gray-300 text-right"><?= $quantity ?></td>
                                    <td class="p-2 border-b border-gray-300 text-right">Rp <?= number_format($product['price'], 2, ',', '.') ?></td>
                                    <td class="p-2 border-b border-gray-300 text-right">Rp <?= number_format($subtotal, 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="font-bold">
                                    <td colspan="3" class="p-2 text-right">Total</td>
                                    <td class="p-2 text-right">Rp <?= number_format($total, 2, ',', '.') ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="submit" name="checkout" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">Bayar</button>
                    </form>
                <?php else: ?>
                    <p>Keranjang kosong.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
