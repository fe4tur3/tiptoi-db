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


if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Welche ID soll bearbeitet werden?
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Daten des Buches laden
$result = $conn->query("SELECT * FROM buch WHERE id = $id");
$buch = $result->fetch_assoc();

// Falls das Buch nicht existiert
if (!$buch) {
    die("Medium nicht gefunden.");
}

// Bereits verknüpfte Themen für dieses Buch holen (für die Checkboxen)
$aktuelle_themen_res = $conn->query("SELECT thema_id FROM buch_thema WHERE buch_id = $id");
$ausgewaehlte_themen = [];
while($t = $aktuelle_themen_res->fetch_assoc()) {
    $ausgewaehlte_themen[] = $t['thema_id'];
}

// Speicher-Logik (UPDATE)
if (isset($_POST['speichern'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $seitenanzahl = (int)$_POST['seitenanzahl'];
    $ausgeliehen = (int)$_POST['ausgeliehen'];
    $ausgeliehen_von = ($ausgeliehen == 1) ? "'" . $conn->real_escape_string($_POST['ausgeliehen_von']) . "'" : "NULL";
    $bewertung_eltern = !empty($_POST['bewertung_eltern']) ? "'" . $conn->real_escape_string($_POST['bewertung_eltern']) . "'" : "NULL";
    $kommentar = "'" . $conn->real_escape_string($_POST['kommentar']) . "'";

    $update_sql = "UPDATE buch SET 
                   name = '$name', 
                   seitenanzahl = $seitenanzahl, 
                   ausgeliehen = $ausgeliehen, 
                   ausgeliehen_von = $ausgeliehen_von, 
                   bewertung_eltern = $bewertung_eltern, 
                   Kommentar = $kommentar 
                   WHERE id = $id";

    if ($conn->query($update_sql)) {
        // Themen aktualisieren (Alte löschen, neue rein)
        $conn->query("DELETE FROM buch_thema WHERE buch_id = $id");
        if (!empty($_POST['themen'])) {
            foreach ($_POST['themen'] as $t_id) {
                $t_id = (int)$t_id;
                $conn->query("INSERT INTO buch_thema (buch_id, thema_id) VALUES ($id, $t_id)");
            }
        }

        // Signal an index.php zum Schließen und Neuladen
        echo "<script>window.parent.nachSpeichernAktualisieren();</script>";
        exit;
    }
}

$themen_liste = $conn->query("SELECT * FROM thema");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Medium bearbeiten</title>
</head>
<body>
    <h2>Medium bearbeiten</h2>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($buch['name']) ?>" required><br><br>

        <label>Seiten/Teile:</label><br>
        <input type="number" name="seitenanzahl" value="<?= $buch['seitenanzahl'] ?>"><br><br>

        <label>Ausgeliehen?</label><br>
        <select name="ausgeliehen" id="ausgeliehen">
            <option value="0" <?= $buch['ausgeliehen'] == 0 ? 'selected' : '' ?>>Nein</option>
            <option value="1" <?= $buch['ausgeliehen'] == 1 ? 'selected' : '' ?>>Ja</option>
        </select><br><br>

        <div id="ausgeliehen_von_feld" style="display: <?= $buch['ausgeliehen'] == 1 ? 'block' : 'none' ?>;">
            <label>Ausgeliehen von:</label><br>
            <select name="ausgeliehen_von">
                <option value="Bücherei" <?= $buch['ausgeliehen_von'] == 'Bücherei' ? 'selected' : '' ?>>Bücherei</option>
                <option value="privat" <?= $buch['ausgeliehen_von'] == 'privat' ? 'selected' : '' ?>>Privat</option>
            </select><br><br>
        </div>

        <label>Themen:</label><br>
        <?php while($t = $themen_liste->fetch_assoc()): ?>
            <input type="checkbox" name="themen[]" value="<?= $t['ID'] ?>" 
                <?= in_array($t['ID'], $ausgewaehlte_themen) ? 'checked' : '' ?>>
            <?= $t['name'] ?><br>
        <?php endwhile; ?><br>

        <label>Bewertung:</label><br>
        <select name="bewertung_eltern">
            <option value="">--keine--</option>
            <?php 
            $bewertungen = ["super", "ganz gut", "okay", "nicht so gut", "Das ist echt nix", "falsche Altersgruppe"];
            foreach($bewertungen as $b) {
                $sel = ($buch['bewertung_eltern'] == $b) ? "selected" : "";
                echo "<option value='$b' $sel>$b</option>";
            }
            ?>
        </select><br><br>

        <label>Kommentar:</label><br>
        <textarea name="kommentar" rows="3" cols="40"><?= htmlspecialchars($buch['Kommentar']) ?></textarea><br><br>

        <button type="submit" name="speichern">Änderungen speichern</button>
    </form>

    <script>
    document.getElementById('ausgeliehen').addEventListener('change', function() {
        document.getElementById('ausgeliehen_von_feld').style.display = (this.value == '1') ? 'block' : 'none';
    });
    </script>
</body>
</html>