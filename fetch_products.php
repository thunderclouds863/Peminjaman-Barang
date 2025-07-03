<?php
// Membaca file JSON
$json = file_get_contents('product.json');

// Mengubah data JSON menjadi array
$data = json_decode($json, true);

// Mengirimkan data produk sebagai response JSON
echo json_encode($data);
?>
