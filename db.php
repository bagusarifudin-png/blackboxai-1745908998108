<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cashier_app";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create products table if not exists
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT(11) NOT NULL DEFAULT 0
)";

if ($conn->query($sql_products) === FALSE) {
    die("Error creating products table: " . $conn->error);
}

// Create transactions table if not exists
$sql_transactions = "CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) UNSIGNED NOT NULL,
    quantity INT(11) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
)";

if ($conn->query($sql_transactions) === FALSE) {
    die("Error creating transactions table: " . $conn->error);
}
?>
