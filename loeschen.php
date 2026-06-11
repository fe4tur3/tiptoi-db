<?php
require_once __DIR__ . '/vendor/autoload.php';                              // Composer-Autoloader einbinden für:

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);                          // env.Datei mir Login-Daten laden
$dotenv->load();

$conn = new mysqli(                                                         // Variablen mit env-Inhalt verbinden
    $_ENV['DB_HOST']
    $_ENV['DB_USER']
    $_ENV['DB_PASS']
    $_ENV['DB_NAME']
);



if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];                                 // braucht keine Absicherung, da nur strings akzeptiert eigentlich

    $id = $conn->real_escape_string($_GET['id']);              // zur Übung und Erinnerung trotzdem eingesetzt

    /*  Zuerst die Verknüpfungen in der Hilfstabelle löschen (wegen Fremdschlüsseln) ist nicht mehr nötig, da ON DELETE CASCADE aktiviert
    $conn->query("DELETE FROM buch_thema WHERE buch_id = $id");

    // Dann das Buch selbst löschen
    $conn->query("DELETE FROM buch WHERE id = $id"); */
    
    // Um unnötigen Datenstrom zu vermeiden, bei Mehrachauswahl Befehl nur einmalig vorzubereiten
    $stmt = $conn->prepare("DELETE FROM buch WHERE id =?");     // Vorbereiten
    $stmt->bind_param("i", $id);                               // Platzhalter durch Wert ersetzen
    $stmt->execute();                                            // ausführen    
}

// Zurück zur Hauptseite
header("Location: index.php");
exit;
?>
