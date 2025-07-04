<?php
// Simple debug script to test image paths
echo "<h1>Image Path Debug</h1>";

// Test 1: Check if uploads directory exists
$uploadDir = "uploads/proof_of_payment";
$absoluteUploadDir = __DIR__ . "/uploads/proof_of_payment";

echo "<h2>Directory Check</h2>";
echo "<p><strong>Relative Upload Dir:</strong> $uploadDir</p>";
echo "<p><strong>Absolute Upload Dir:</strong> $absoluteUploadDir</p>";
echo "<p><strong>Relative Dir Exists:</strong> " . (is_dir($uploadDir) ? 'Yes' : 'No') . "</p>";
echo "<p><strong>Absolute Dir Exists:</strong> " . (is_dir($absoluteUploadDir) ? 'Yes' : 'No') . "</p>";

if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    echo "<p><strong>Files in directory:</strong></p>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
    
    // Test 2: Try to display a sample image
    if (count($files) > 2) { // More than . and ..
        $sampleFile = $files[2]; // Get first actual file
        echo "<h2>Sample Image Test</h2>";
        echo "<p><strong>Sample file:</strong> $sampleFile</p>";
        
        // Test different path formats
        $paths = [
            "uploads/proof_of_payment/$sampleFile",
            "/admin/uploads/proof_of_payment/$sampleFile",
            __DIR__ . "/uploads/proof_of_payment/$sampleFile"
        ];
        
        foreach ($paths as $index => $path) {
            echo "<h3>Test Path $index: $path</h3>";
            echo "<p><strong>File exists:</strong> " . (file_exists($path) ? 'Yes' : 'No') . "</p>";
            echo "<p><strong>Is readable:</strong> " . (is_readable($path) ? 'Yes' : 'No') . "</p>";
            
            if (file_exists($path)) {
                echo "<img src='$path' style='max-width: 200px; border: 1px solid #ccc; margin: 10px;' alt='Test Image'>";
            }
        }
    }
}

// Test 3: Check web server configuration
echo "<h2>Web Server Info</h2>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";
echo "<p><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</p>";
echo "<p><strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

// Test 4: Check if we can access images via HTTP
echo "<h2>HTTP Access Test</h2>";
$baseUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/admin/";
echo "<p><strong>Base URL:</strong> $baseUrl</p>";

if (is_dir($uploadDir) && count(scandir($uploadDir)) > 2) {
    $sampleFile = scandir($uploadDir)[2];
    $httpPath = $baseUrl . "uploads/proof_of_payment/$sampleFile";
    echo "<p><strong>HTTP Path:</strong> $httpPath</p>";
    echo "<p><strong>HTTP Access:</strong> <a href='$httpPath' target='_blank'>Click to test</a></p>";
}
?> 