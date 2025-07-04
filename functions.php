<?php
function getProductsByCategory($conn, $category = null) {
    $sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
    $params = [];
    if ($category !== null) {
        $sql .= " WHERE c.category_name = :category";
        $params[':category'] = $category;
    }
    $sql .= " ORDER BY c.category_name, p.product";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAllProducts($conn) {
    $sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAllCategories($conn) {
    $sql = "SELECT DISTINCT category_name FROM categories ORDER BY category_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getNewArrivals($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            ORDER BY p.product_id DESC 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBestSellers($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            ORDER BY p.selling_price DESC 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSpecialOffers($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.selling_price > 0 
            ORDER BY p.selling_price ASC 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecommendedProducts($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            ORDER BY RAND() 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createProductCard($product, $isSpecialOffer = false) {
    $imagePath = !empty($product['image1']) ? $product['image1'] : 'images/default.png';
    
    $html = '<div class="product-card">';
    
    // For special offers, we'll show a badge if the price is below a certain threshold
    if ($isSpecialOffer && $product['selling_price'] < 10000) {
        $html .= '<div class="discount-badge">Special Offer</div>';
    }
    
    $html .= '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product']) . '">';
    $html .= '<div class="product-title">' . htmlspecialchars($product['product']) . '</div>';
    $html .= '<div class="product-price">₱' . number_format($product['selling_price'], 2) . '</div>';
    $html .= '</div>';
    
    return $html;
}
?>
