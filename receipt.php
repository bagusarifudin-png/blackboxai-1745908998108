<?php
require 'db.php';

// Get last transaction date
$result = $conn->query("SELECT MAX(transaction_date) as last_date FROM transactions");
$row = $result->fetch_assoc();
$last_date = $row['last_date'] ?? null;

$transactions = [];
$total = 0;

if ($last_date) {
    $stmt = $conn->prepare("SELECT t.quantity, t.total_price, p.name FROM transactions t JOIN products p ON t.product_id = p.id WHERE t.transaction_date = ?");
    $stmt->bind_param("s", $last_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
        $total += $row['total_price'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Struk Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-6">Struk Pembayaran</h1>
        <?php if (!empty($transactions)): ?>
            <table class="w-full border border-gray-300 rounded mb-4">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 border-b border-gray-300 text-left">Produk</th>
                        <th class="p-2 border-b border-gray-300 text-right">Jumlah</th>
                        <th class="p-2 border-b border-gray-300 text-right">Harga Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $item): ?>
                    <tr>
                        <td class="p-2 border-b border-gray-300"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="p-2 border-b border-gray-300 text-right"><?= $item['quantity'] ?></td>
                        <td class="p-2 border-b border-gray-300 text-right">Rp <?= number_format($item['total_price'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="font-bold">
                        <td colspan="2" class="p-2 text-right">Total Bayar</td>
                        <td class="p-2 text-right">Rp <?= number_format($total, 2, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
            <a href="index.php" class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Kembali ke Kasir</a>
        <?php else: ?>
            <p>Tidak ada transaksi terbaru.</p>
            <a href="index.php" class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Kembali ke Kasir</a>
        <?php endif; ?>
    </div>
</body>
</html>
