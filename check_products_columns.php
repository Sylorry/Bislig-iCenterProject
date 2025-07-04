<?php
require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Get table structure
    $sql = "DESCRIBE products";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>Current table structure:\n\n";
    foreach ($columns as $column) {
        echo "{$column['Field']}: {$column['Type']} ";
        echo $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
        echo $column['Default'] ? " DEFAULT '{$column['Default']}'" : '';
        echo "\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error checking table structure: " . $e->getMessage();
}
?>
