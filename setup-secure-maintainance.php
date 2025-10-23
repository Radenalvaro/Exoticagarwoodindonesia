<?php
// Script setup untuk sistem maintenance yang aman
require_once 'database.php';

echo "<h2>Setup Secure Maintenance System</h2>";

try {
    // 1. Cek dan buat tabel loginmaintainance
    echo "<h3>1. Setting up loginmaintainance table...</h3>";
    
    // Drop existing table
    $pdo->exec("DROP TABLE IF EXISTS loginmaintainance");
    
    // Create new table
    $create_sql = "
        CREATE TABLE loginmaintainance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of username',
            password_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of password',
            namaayah_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of nama ayah',
            namaibu_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of nama ibu',
            tanggallahir_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of tanggal lahir',
            seedphrase_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of 12 kata unik',
            combined_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of all data combined',
            hash_timestamp INT NOT NULL COMMENT 'Timestamp for hash generation (30 second intervals)',
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($create_sql);
    echo "<p style='color: green;'>✓ Table loginmaintainance created successfully</p>";
    
    // 2. Insert initial data
    echo "<h3>2. Inserting initial data...</h3>";
    
    $original_data = [
        'username' => 'Raden Alvaro',
        'password' => 'alvo$2007*',
        'namaayah' => 'Soejono Badroen',
        'namaibu' => 'Esiria Juita',
        'tanggallahir' => '2007-01-01',
        'seedphrase' => 'apple mirror moon tiger coffee smile happy cloud tree jump water sun'
    ];
    
    // Generate initial hash
    $current_time = time();
    $timestamp_salt = floor($current_time / 30);
    
    $combined_string = $original_data['username'] . '|' . 
                      $original_data['password'] . '|' . 
                      $original_data['namaayah'] . '|' . 
                      $original_data['namaibu'] . '|' . 
                      $original_data['tanggallahir'] . '|' . 
                      $original_data['seedphrase'] . '|' . 
                      $timestamp_salt;
    
    $hashes = [
        'username_hash' => hash('sha256', $original_data['username'] . $timestamp_salt),
        'password_hash' => hash('sha256', $original_data['password'] . $timestamp_salt),
        'namaayah_hash' => hash('sha256', $original_data['namaayah'] . $timestamp_salt),
        'namaibu_hash' => hash('sha256', $original_data['namaibu'] . $timestamp_salt),
        'tanggallahir_hash' => hash('sha256', $original_data['tanggallahir'] . $timestamp_salt),
        'seedphrase_hash' => hash('sha256', $original_data['seedphrase'] . $timestamp_salt),
        'combined_hash' => hash('sha256', $combined_string),
        'hash_timestamp' => $timestamp_salt
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO loginmaintainance (
            username_hash, password_hash, namaayah_hash, namaibu_hash, 
            tanggallahir_hash, seedphrase_hash, combined_hash, hash_timestamp, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $result = $stmt->execute([
        $hashes['username_hash'],
        $hashes['password_hash'],
        $hashes['namaayah_hash'],
        $hashes['namaibu_hash'],
        $hashes['tanggallahir_hash'],
        $hashes['seedphrase_hash'],
        $hashes['combined_hash'],
        $hashes['hash_timestamp']
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Initial data inserted successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to insert initial data</p>";
    }
    
    // 3. Verify data
    echo "<h3>3. Verification:</h3>";
    $stmt = $pdo->query("SELECT * FROM loginmaintainance WHERE id = 1");
    $db_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($db_data) {
        echo "<p style='color: green;'>✓ Data verified in database</p>";
        echo "<p><strong>Hash Timestamp:</strong> " . $db_data['hash_timestamp'] . "</p>";
        echo "<p><strong>Last Updated:</strong> " . $db_data['last_updated'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Data verification failed</p>";
    }
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Access <a href='loginmaintainance.php'>loginmaintainance.php</a> to test login</li>";
    echo "<li>Use the original data shown above for login</li>";
    echo "<li>Hash will auto-update every 30 seconds</li>";
    echo "<li>Set up cron job for auto-hash-cron.php (optional)</li>";
    echo "</ol>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
