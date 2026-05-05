<?php
// 1. Verbindung zur Datenbank herstellen
$conn = new mysqli("localhost", "root", "", "tiptoi");

// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// 2. Alle Bücher aus der Tabelle "buch" abrufen
$sql = "SELECT * FROM buch ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Tiptoi Sammlung</title>

    <style>
    /* 1. SCHRIFTARTEN */
    @import url('https://fonts.googleapis.com/css2?family=Fira+Sans+Extra+Condensed:wght@900&family=Fredoka:wght@400;700&display=swap');

    body { 
        font-family: 'Fredoka', sans-serif; 
        background-color: #ffdef2; /* Rosa Hintergrund */
        padding: 20px;
        margin: 0;
    }

    /* 2. ÜBERSCHRIFT: Hart, Schwarz/Gelb, kein Schatten */
    h1 { 
        font-family: 'Fira Sans Extra Condensed', sans-serif;
        color: #ffff00; 
        background-color: #000000; 
        display: block;
        padding: 20px;
        border: 10px solid #ffffff;
        text-align: center;
        font-size: 4rem;
        text-transform: uppercase;
        text-shadow: none; 
        margin: 0 0 30px 0;
    }

    /* 3. TABELLE: Verspielt & Bunt */
    table { 
        width: 100%; 
        border-collapse: separate; 
        border-spacing: 0 10px;
    }

    th { 
        background-color: #32cd32; /* Grün */
        color: white;
        padding: 15px;
        border: 4px solid #ffffff;
        text-align: left;
        font-size: 1.4rem;
        border-radius: 15px 15px 0 0;
    }

    td { 
        background-color: #fff0f5; 
        padding: 15px;
        border-top: 5px solid #ff69b4; 
        border-bottom: 5px solid #ff69b4; 
        color: #333;
        font-weight: 700;
        font-size: 1.1rem;
    }

    tr td:first-child { border-left: 5px solid #ff69b4; border-radius: 20px 0 0 20px; }
    tr td:last-child { border-right: 5px solid #ff69b4; border-radius: 0 20px 20px 0; }

    /* 4. BUTTONS IN DER TABELLE */
    /* Bearbeiten: Blau */
    button[onclick*="oeffneBearbeitenModal"] {
        background: linear-gradient(145deg, #1e90ff, #0000cd);
        color: #ffffff;
        cursor: pointer;
        border: 3px solid #ffffff;
        border-radius: 12px;
        font-family: 'Fredoka', sans-serif;
        font-weight: 700;
        padding: 8px 15px;
        box-shadow: 0 4px 0 #000080;
    }

    /* 5. HAUPT-BUTTON: Schwarz mit Lila Schrift */
    button[onclick="oeffneModal()"], .btn-neu {
        background-color: #000000; 
        color: #bf00ff;           /* Kräftiges Lila */
        cursor: pointer;
        border: 5px solid #bf00ff; 
        border-radius: 50px;       
        font-family: 'Fredoka', sans-serif;
        font-weight: 400;
        font-size: 1.0rem;
        margin: 5px auto;
        display: block;
        padding: 15px 300px;
        text-transform: uppercase;
        box-shadow: 0 6px 0 #6a0dad;
        transition: all 0.2s ease;
    }

    button[onclick="oeffneModal()"]:hover {
        transform: scale(1.05);
        background-color: #1a1a1a;
    }

    button[onclick="oeffneModal()"]:active {
        transform: translateY(4px);
        box-shadow: none;
    }

    /* 6. POPUP (MODAL) */
    .modal {
        display: none; 
        position: fixed; 
        z-index: 9999; 
        left: 0; top: 0;
        width: 100%; height: 100%; 
        background-color: rgba(0, 0, 0, 0.7); 
        overflow: hidden;
    }

    .modal-content {
        background-color: #ff80af; /* Dein Bubblegum-Rosa */
        margin: 2vh auto; 
        padding: 0; 
        border: 15px solid #ffff00; /* Fetter Gelber Rahmen */
        width: 85%; 
        height: 90vh; 
        border-radius: 40px; 
        position: relative;
        overflow: hidden; 
        display: flex;
        flex-direction: column;
    }

    /* Iframe Fix für volle Höhe */
    #modalFormularContainer {
        flex-grow: 1;
        display: block;
        height: 100%;
    }

    #modalFormularContainer iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }

    /* Schließen-Button */
    .close {
        position: absolute;
        right: 15px;
        top: 15px;
        background-color: #32cd32; 
        color: white;
        width: 50px;
        height: 50px;
        line-height: 45px;
        text-align: center;
        font-size: 35px;
        font-weight: bold;
        border: 4px solid #ffffff;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10001;
    }
</style>
</head>
<body>

    <h1>Meine Tiptoi Medien</h1>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Bearbeiten</th>
                <th>Themen</th>
                <th>Seiten/Teile</th> 
                <th>Status</th>
                <th>Bewertung</th>
                <th>Kommentar</th>
                <th>Löschen</th>
            </tr>
        </thead>
        <tbody> 
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $buch_id = $row['id']; 
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";

            echo "<td>";
            echo "<button onclick='oeffneBearbeitenModal(" . $row['id'] . ")'>Bearbeiten</button>";
            echo "</td>";
        
            // --- START THEMEN-LOGIK ---
            echo "<td>";
            $themen_sql = "SELECT thema.name, thema.ID 
                           FROM thema 
                           JOIN buch_thema ON thema.ID = buch_thema.thema_id 
                           WHERE buch_thema.buch_id = $buch_id";
            
            $themen_res = $conn->query($themen_sql);
            
            $themen_links = [];
            while($t_row = $themen_res->fetch_assoc()) {
                $themen_links[] = '<a href="index.php?thema=' . $t_row['ID'] . '">' . htmlspecialchars($t_row['name']) . '</a>';
            }
            echo implode(", ", $themen_links);
            echo "</td>";
            // --- ENDE THEMEN-LOGIK ---
        
            echo "<td>" . $row['seitenanzahl'] . "</td>";


            $status_class = ($row['ausgeliehen'] == 1) ? "status-ausgeliehen" : "status-regal";
            $status_text = ($row['ausgeliehen'] == 1) ? "Ausgeliehen (" . htmlspecialchars($row['ausgeliehen_von']) . ")" : "Im Regal";
            echo "<td><span class='$status_class'>" . $status_text . "</span></td>";
            
            echo "<td>" . htmlspecialchars($row['bewertung_eltern']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Kommentar']) . "</td>";

            echo "<td>";
            echo "<a href='loeschen.php?id=" . $row['id'] . "' onclick='return confirm(\"Wirklich löschen?\")' style='color:red; text-decoration:none;'>[X]</a>";
            echo "</tr>";

        }
    } else {
        echo "<tr><td colspan='8'>Noch keine Medien eingetragen.</td></tr>";
    }
    ?>
        </tbody>
    </table>

    <br> <br> <button onclick="oeffneModal()">+ Neues Medium</button>


<!-- Das Modal (Hintergrund) -->
<div id="meinModal" class="modal">

  <!-- Modal-Inhalt -->
  <div class="modal-content">
    <span class="close" onclick="schliesseModal()">&times;</span>
    <h2> </h2>
    
    <div id="modalFormularContainer">
    <!-- Das Iframe füllt jetzt den Container dank CSS -->
    <iframe src="tiptoi_Eingabe.php"></iframe>
</div>
  </div>

</div>

<script>
function oeffneModal(id = null) {
    var iframe = document.querySelector("#modalFormularContainer iframe");
    if (id) {
        // Wenn eine ID übergeben wurde -> Bearbeiten-Modus
        iframe.src = "bearbeiten.php?id=" + id;
    } else {
        // Keine ID -> Neu-Modus
        iframe.src = "tiptoi_Eingabe.php";
    }
    document.getElementById("meinModal").style.display = "block";
}

// Deine Bearbeiten-Funktion nutzt nun einfach die neue Logik
function oeffneBearbeitenModal(id) {
    var iframe = document.querySelector("#modalFormularContainer iframe");
    iframe.src = "bearbeiten.php?id=" + id; // Wir erstellen gleich die bearbeiten.php
    document.getElementById("meinModal").style.display = "block";
}


function schliesseModal() {
    document.getElementById("meinModal").style.display = "none";
}


function nachSpeichernAktualisieren() {
    // 1. Das Modal schließen
    schliesseModal();
    
    // 2. Die ganze Seite neu laden, um die Tabelle zu aktualisieren
    window.location.reload();
}
// Schließt das Modal auch, wenn man außerhalb des Kastens klickt
window.onclick = function(event) {
    if (event.target == document.getElementById("meinModal")) {
        schliesseModal();
    }
}

</script>
</body>
</html>