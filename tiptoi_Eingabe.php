<?php

$conn = new mysqli("localhost", "root", "", "tiptoi");                  // Datenbankverbindung

if ($conn->connect_error) {                                             // Verbindung prüfen
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);        // Abbrechen, wenn keine Verbindung
}

// Speichern-Logik
if (isset($_POST['speichern'])) {                                       // exisiter der "Speichern"-Wert gerade, sprich wurde er gedrückt?, wenn ja, dann...
    
    //Formulardaten abholen und absichern
    $name = $conn->real_escape_string($_POST['name']);                   // real_escape_string = Schutz vor SQL-Injection (backslash vor potentiellen SQL-Codes im Eingabefeld)
    $seitenanzahl = !empty($_POST['seitenanzahl']) ? (int)$_POST['seitenanzahl'] : 0;             
    $ausgeliehen = (int)$_POST['ausgeliehen'];                           // 0 oder 1
    $ausgeliehen_von = $ausgeliehen == 1
    ? $conn->real_escape_string($_POST['ausgeliehen_von'])              // wenn 1, dann Wert holen
    : NULL;                                                             // sonst leer
//   $vielfaeltigkeit = $_POST['vielfaeltigkeit'] ?: NULL;                // optional
    $bewertung_eltern = !empty($_POST['bewertung_eltern']) 
    ? $conn->real_escape_string($_POST['bewertung_eltern']) 
    : NULL;                                                             // optional
    $kommentar = $conn->real_escape_string($_POST['kommentar'] ?? '');

    // in Tabelle "buch" eintragen, zukünftig "Medium"

    $sql = "INSERT INTO buch (name, seitenanzahl, ausgeliehen, ausgeliehen_von, bewertung_eltern, kommentar)
    VALUES ('$name', $seitenanzahl, $ausgeliehen,
    " . ($ausgeliehen_von ? "'$ausgeliehen_von'" : "NULL") . ",
    " . ($bewertung_eltern ? "'$bewertung_eltern'" : "NULL") . ",
    " . ($kommentar ? "'$kommentar'" : "NULL") . ")";

    if ($conn->query($sql)){                                             // wenn Eintrag erfolgreich...
        $buch_id = $conn->insert_id;                                    // neu vergebene ID des Buches holen

        // Themen verbinden, Hilfstabelle nutzen
        if (!empty($_POST['themen'])) {                                 // wurden Themen angehakt?, wenn ja:
            foreach ($_POST['themen'] as $thema_id) {                    // jedes angehakte Thema durchgehen
                $thema_id = (int)$thema_id;                             // Sicherheit: ID in Zahl umwandeln
                $conn->query("INSERT INTO buch_thema (buch_id, thema_id) 
                                VALUES ($buch_id, $thema_id)");           // Verknüpfung eintragen
            }
        }

        // >>> GEÄNDERT: JavaScript-Befehl optimiert und exit hinzugefügt, damit das Fenster schließt
        echo "<script>
                if(window.parent && window.parent.nachSpeichernAktualisieren) {
                    window.parent.nachSpeichernAktualisieren();
                }
              </script>";
        exit; // Stoppt das weitere Laden der Seite hier
        // >>> ENDE ÄNDERUNG

    } else {
        echo"<p style = 'color: red'> Fehler: " .$conn->error . "</p>";
    }
}

//Themen aus Db holen (Position nach unten verschoben, damit sie beim Neuladen immer frisch sind)
$themen = $conn->query("SELECT * FROM thema");                          // alle Themen laden 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tiptoi media</title>
</head>
<body>
    <h1>Tiptoi Medienverwaltung </h1>
   <!--<?php echo "Verbindung erfolgreich!"; ?>-->

   <h2>Neues Medium:</h2>
   <form method = "POST">                                               <!--Post = Daten unsichbar senden -->

   <label>Name: *</label><br>
   <input type="text" name="name" required> <br> <br>

   
   <label>Seiten/Teile: *</label><br>
   <input type="number" name="seitenanzahl" value="0" required> <br> <br>

   
  
   <label>Ausgeliehen?</label><br>
        <select name="ausgeliehen" id="ausgeliehen">    <!-- id="ausgeliehen" braucht JS unten -->
            <option value="0">Nein</option>
            <option value="1">Ja</option>
        </select><br><br>
  
  
   <div id="ausgeliehen_von_feld" style="display:none" >                <!-- erstmal versteckt -->
   <label>Ausgeliehen von</label><br>
   <select name="ausgeliehen_von">
        <option value="Bücherei">Bücherei</option>
        <option value="privat">Privat</option>
   </select> <br> <br>
</div>


<label>Themen:</label><br>
<?php while($thema = $themen->fetch_assoc()): ?>                        <!-- PHP-Schleife durch alle Themen -->
   <input type="checkbox" name="themen[]" value="<?= $thema['ID'] ?>">    <!-- [] = Array -->
   <?= $thema['name'] ?><br>                                             <!-- Themenname anzeigen -->
   <?php endwhile; ?> <br>
  
  <!-- <label>Vielfältigkeit (1-10):</label><br>
   <input type="number" name="vielfaeltigkeit" min="1" max="10"><br><br> -->

   <label>Bewertung: </label> <br>
   <select name="bewertung_eltern">                                         <!-- war anfänglich als getrennte Bewertung Eltern und Kind geplant, zu komplex -->
    <option value= ""> --keine-- </option>
    <option value= "super">super</option>
    <option value= "ganz gut"> ganz gut </option>
    <option value= "okay"> okay </option>
    <option value= "nicht so gut"> nicht so gut </option>
    <option value= "Das ist echt nix"> Das ist echt nix </option>
    <option value= "falsche Altersgruppe"> falsche Altersgruppe </option>
</select> <br> <br>

<label>Kommentar:</label><br>
<textarea name="kommentar" rows="3" cols="40"></textarea><br><br>

<button type = "submit" name="speichern"> Speichern </button>
</form>

<script>                                                        // Ausgeliehen-Feld nur anzeigen, wenn "Ja" gewählt
document.getElementById('ausgeliehen').addEventListener('change', function() {
    document.getElementById('ausgeliehen_von_feld').style.display
    = this.value == '1' ? 'block' : 'none'; 
})
</script>

</body>
</html>