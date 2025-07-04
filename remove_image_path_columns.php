<?php
require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Check current table structure
    echo "<h3>Current Products Table Structure:</h3>";
    $structure = $pdo->query("DESCRIBE products");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Remove image_path columns
    $drop_queries = [
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path1",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path2",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path3",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path4",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path5",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path6",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path7",
        "ALTER TABLE products DROP COLUMN IF EXISTS image_path8"
    ];
    
    foreach ($drop_queries as $query) {
        try {
            $pdo->exec($query);
            echo "✓ " . $query . "<br>";
        } catch (PDOException $e) {
            echo "⚠ " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><h3>Updated Products Table Structure:</h3>";
    $structure = $pdo->query("DESCRIBE products");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><strong>✓ Image path columns have been removed from the products table!</strong>";
    
} catch (PDOException $e) {
    echo "Error removing image path columns: " . $e->getMessage();
}
?> 
