<?php
include 'connect.php';
header('Content-Type: text/html; charset=utf-8');
echo "<h2>AutoPartsZW — Database Check</h2>";
// Show connection info
if (isset($servername)) echo "<p><b>DB host:</b> " . htmlspecialchars($servername) . " <b>port:</b> " . htmlspecialchars($port) . "</p>";

// Check parts table exists
$res = $conn->query("SHOW TABLES LIKE 'parts'");
if ($res && $res->num_rows) {
    echo "<p style='color:green'><b>OK:</b> table <code>parts</code> exists.</p>";
} else {
    echo "<p style='color:red'><b>Missing:</b> table <code>parts</code> NOT found.</p>";
}

// Count rows
$rowCount = null;
$res = $conn->query("SELECT COUNT(*) AS c FROM parts");
if ($res) {
    $r = $res->fetch_assoc();
    $rowCount = (int)$r['c'];
    echo "<p><b>Rows in parts:</b> " . $rowCount . "</p>";
} else {
    echo "<p style='color:red'><b>Error counting rows:</b> " . htmlspecialchars($conn->error) . "</p>";
}

// Show sample rows
$res = $conn->query("SELECT id,name,make,model,category,price,stock FROM parts LIMIT 10");
if ($res) {
    echo "<h3>Sample rows</h3>";
    echo "<table border=1 cellpadding=6><tr><th>id</th><th>name</th><th>make</th><th>model</th><th>category</th><th>price</th><th>stock</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['make']) . "</td>";
        echo "<td>" . htmlspecialchars($row['model']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>" . htmlspecialchars($row['price']) . "</td>";
        echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No sample rows available or query error: " . htmlspecialchars($conn->error) . "</p>";
}

echo "<p>If rows are missing, re-import <code>database.sql</code> in phpMyAdmin or via CLI. See instructions below.</p>";

?>
