<?php
// Function to get products with pagination and category filter
function getProducts($category = 'all', $start = 0, $limit = 3) {
    global $pdo;
    
    try {
        if ($category === 'all') {
            $stmt = $pdo->prepare("SELECT * FROM products LIMIT :start, :limit");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :category LIMIT :start, :limit");
            $stmt->bindParam(':category', $category);
        }
        
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getProducts: " . $e->getMessage());
        return [];
    }
}

// Function to get total number of products
function getTotalProducts($category = 'all') {
    global $pdo;
    
    try {
        if ($category === 'all') {
            $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = :category");
            $stmt->bindParam(':category', $category);
            $stmt->execute();
        }
        
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        error_log("Error in getTotalProducts: " . $e->getMessage());
        return 0;
    }
}

// Function to search products
function searchProducts($searchTerm) {
    global $pdo;
    
    try {
        $searchTerm = "%$searchTerm%";
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :search OR description LIKE :search");
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in searchProducts: " . $e->getMessage());
        return [];
    }
}
?> 