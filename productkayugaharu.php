<?php
// Komponen khusus untuk product card kayu gaharu - FIXED PATH FOR ADMIN FOLDER
function renderKayuGaharuCard($product) {
    $whatsapp_number = '+6281554020227';
    $whatsapp_message = urlencode("Hello, I'm interested in " . $product['name'] . " (Grade: " . $product['grade'] . ", Origin: " . ($product['origin'] ?? 'Indonesia') . "). Could you please provide more information?");
    $whatsapp_url = "https://wa.me/" . str_replace(['+', '-', ' '], '', $whatsapp_number) . "?text=" . $whatsapp_message;
    
    // PRIORITAS GAMBAR DARI DATABASE DENGAN PATH ADMIN FOLDER
    $image_url = '';
    
    // Cek apakah ada gambar di database
    if (!empty($product['image_url'])) {
        // Jika path dimulai dengan 'uploads/', tambahkan prefix 'admin/'
        if (strpos($product['image_url'], 'uploads/') === 0) {
            $image_url = 'admin/' . $product['image_url'];
        }
        // Jika path sudah termasuk 'admin/uploads/', gunakan langsung
        elseif (strpos($product['image_url'], 'admin/uploads/') === 0) {
            $image_url = $product['image_url'];
        }
        // Jika path dimulai dengan 'http', gunakan langsung (URL eksternal)
        elseif (strpos($product['image_url'], 'http') === 0) {
            $image_url = $product['image_url'];
        }
        // Jika path lain, coba tambahkan prefix 'admin/'
        else {
            $image_url = 'admin/' . $product['image_url'];
        }
    }
    
    // Fallback ke placeholder jika tidak ada gambar di database
    if (empty($image_url)) {
        $image_url = 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=300&fit=crop';
    }
    
    return '
    <div class="product-card">
        <div class="product-image">
            <img src="' . htmlspecialchars($image_url) . '" 
                 alt="' . htmlspecialchars($product['name']) . '" 
                 loading="lazy"
                 onerror="this.src=\'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=300&fit=crop\'"
                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
        </div>
        <div class="product-info">
            <h4 class="product-name">' . htmlspecialchars($product['name']) . '</h4>
            <p class="product-origin">Origin: ' . htmlspecialchars($product['origin'] ?? 'Indonesia') . '</p>
            <p class="product-grade">Grade: ' . htmlspecialchars($product['grade']) . '</p>
            <p class="product-description">' . htmlspecialchars($product['description']) . '</p>
            <p class="product-price">$' . number_format($product['price']) . ' / ' . htmlspecialchars($product['unit']) . '</p>
            <a href="seedetail-kayugaharu.php?id=' . $product['id'] . '" class="btn btn-primary btn-full">See Detail</a>
        </div>
    </div>';
}
?>
