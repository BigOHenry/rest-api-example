<?php

$dbPath = __DIR__ . '/test.db';

// Smaže existující databázi
if (file_exists($dbPath)) {
    unlink($dbPath);
}

// Vytvoří nové SQLite připojení
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// SQL pro vytvoření tabulek a dat
$sql = "
-- Vytvoření tabulek
CREATE TABLE appuser (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL
);

CREATE TABLE article (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES appuser (id)
);

-- Vložení testovacích uživatelů (hesla jsou 'password')
INSERT INTO appuser (id, email, password, name, role) VALUES 
(1, 'admin@test.com', '\$2y\$13\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'ROLE_ADMIN'),
(2, 'author@test.com', '\$2y\$13\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Author User', 'ROLE_AUTHOR'), 
(3, 'reader@test.com', '\$2y\$13\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Reader User', 'ROLE_READER');

-- Vložení testovacích článků
INSERT INTO article (id, title, content, author_id) VALUES
(1, 'Test Article 1', 'Content of test article 1 that is long enough for validation.', 2),
(2, 'Test Article 2', 'Content of test article 2 that is long enough for validation.', 2);
";

try {
    // Spustí SQL příkazy
    $pdo->exec($sql);

    // DŮLEŽITÉ: Nastaví správná oprávnění po vytvoření
    chmod($dbPath, 0666);

    // Nastaví oprávnění adresáře
    $dir = dirname($dbPath);
    chmod($dir, 0777);

    echo "✅ Test database created successfully: " . $dbPath . "\n";
    echo "📋 File permissions set to 666 (rw-rw-rw-)\n";
    echo "📁 Directory permissions set to 777 (rwxrwxrwx)\n";

    // Zobrazí vytvořené uživatele
    echo "\n📋 Created users:\n";
    $stmt = $pdo->query('SELECT id, email, role FROM appuser');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['id']}: {$row['email']} ({$row['role']})\n";
    }

    // Zobrazí vytvořené články
    echo "\n📰 Created articles:\n";
    $stmt = $pdo->query('SELECT id, title, author_id FROM article');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['id']}: {$row['title']} (author: {$row['author_id']})\n";
    }

    // Debug informace
    echo "\n🔧 Debug info:\n";
    echo "  File exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";
    echo "  File size: " . filesize($dbPath) . " bytes\n";
    echo "  File permissions: " . substr(sprintf('%o', fileperms($dbPath)), -4) . "\n";
    echo "  Directory permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";

} catch (PDOException $e) {
    echo "❌ Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}
?>
