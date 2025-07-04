<!DOCTYPE html>
<html>
<head>
    <title>Image Access Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 20px; margin: 20px 0; }
        .success { color: green; }
        .error { color: red; }
        img { max-width: 200px; border: 1px solid #ccc; margin: 10px; }
    </style>
</head>
<body>
    <h1>Proof of Payment Image Access Test</h1>
    
    <div class="test-section">
        <h2>Directory Check</h2>
        <?php
        $uploadDir = "uploads/proof_of_payment";
        if (is_dir($uploadDir)) {
            echo "<p class='success'>✅ Directory exists: $uploadDir</p>";
            $files = scandir($uploadDir);
            $imageFiles = array_filter($files, function($file) {
                return $file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'jpg';
            });
            
            if (!empty($imageFiles)) {
                echo "<p>Found " . count($imageFiles) . " image files:</p>";
                echo "<ul>";
                foreach (array_slice($imageFiles, 0, 5) as $file) {
                    echo "<li>$file</li>";
                }
                echo "</ul>";
                
                // Test with first image
                $testFile = reset($imageFiles);
                echo "<h3>Testing with file: $testFile</h3>";
                
                $testPaths = [
                    "uploads/proof_of_payment/$testFile",
                    "/admin/uploads/proof_of_payment/$testFile",
                    "../uploads/proof_of_payment/$testFile",
                    "./uploads/proof_of_payment/$testFile"
                ];
                
                foreach ($testPaths as $path) {
                    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
                    echo "<h4>Testing path: $path</h4>";
                    
                    if (file_exists($path)) {
                        echo "<p class='success'>✅ File exists on server</p>";
                        echo "<img src='$path' alt='Test Image' onload='console.log(\"Image loaded: $path\")' onerror='console.log(\"Image failed: $path\")'>";
                        echo "<p>Image should appear above if accessible via HTTP</p>";
                    } else {
                        echo "<p class='error'>❌ File does not exist on server</p>";
                    }
                    echo "</div>";
                }
                
            } else {
                echo "<p class='error'>❌ No image files found in directory</p>";
            }
        } else {
            echo "<p class='error'>❌ Directory does not exist: $uploadDir</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>JavaScript Image Loading Test</h2>
        <div id="js-test-results"></div>
        <script>
            function testImagePath(path) {
                return new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => resolve({ path, success: true });
                    img.onerror = () => resolve({ path, success: false });
                    img.src = path;
                });
            }
            
            async function runTests() {
                const resultsDiv = document.getElementById('js-test-results');
                const testPaths = [
                    'uploads/proof_of_payment/proof_68319aa0b8486.jpg',
                    '/admin/uploads/proof_of_payment/proof_68319aa0b8486.jpg',
                    '../uploads/proof_of_payment/proof_68319aa0b8486.jpg'
                ];
                
                for (const path of testPaths) {
                    const result = await testImagePath(path);
                    const div = document.createElement('div');
                    div.style.border = '1px solid #ddd';
                    div.style.padding = '10px';
                    div.style.margin = '10px 0';
                    
                    if (result.success) {
                        div.innerHTML = `<p class='success'>✅ JavaScript loaded: ${path}</p>`;
                    } else {
                        div.innerHTML = `<p class='error'>❌ JavaScript failed: ${path}</p>`;
                    }
                    
                    resultsDiv.appendChild(div);
                }
            }
            
            runTests();
        </script>
    </div>
    
    <div class="test-section">
        <h2>Current Page Info</h2>
        <p><strong>Current URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>Script Path:</strong> <?php echo __FILE__; ?></p>
        <p><strong>Current Directory:</strong> <?php echo __DIR__; ?></p>
    </div>
</body>
</html> 