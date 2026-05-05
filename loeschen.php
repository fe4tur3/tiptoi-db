<?php
$conn = new mysqli("localhost", "root", "", "tiptoi");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Zuerst die Verknüpfungen in der Hilfstabelle löschen (wegen Fremdschlüsseln)
    $conn->query("DELETE FROM buch_thema WHERE buch_id = $id");

    // 2. Dann das Buch selbst löschen
    $conn->query("DELETE FROM buch WHERE id = $id");
}

// Zurück zur Hauptseite
header("Location: index.php");
exit;
?>