<?php
require 'db_connection.php';

try {
    $result = $db->exec("DROP TABLE IF EXISTS admins");
    if ($result) {
        echo "Table 'admins' removed successfully.";
    } else {
        echo "Table 'admins' does not exist or removal failed.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
