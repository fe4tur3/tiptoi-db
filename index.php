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


// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// 2. Alle Bücher aus der Tabelle "buch" abrufen
$sql = "SELECT * FROM buch ORDER BY name";                                          // Leerzeichen nicht werten (name =''),
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Meine Tiptoi Sammlung</title>

    <link rel="stylesheet" href="css/style.css">

</head>
<body>
<div class="ueberschrift-container">
    <h1>Meine Tiptoi Medien</h1>
    </div>

        <div class="such-container" style="text-align: center; margin: 20px 0;">
        <input type="text" id="suchInput" 
               placeholder="Nach Name, Thema oder Kommentar suchen..." 
               style="width: 50%; max-width: 600px; padding: 12px 15px; font-size: 16px; border-radius: 8px; border: 1px solid #ccc;">
    </div>

    <?php if ($result->num_rows > 0): ?>

        <table id="meineTabelle">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">Name</th>
                    <th onclick="sortTable(1)">Bearbeiten</th>
                    <th onclick="sortTable(2)">Themen</th>
                    <th onclick="sortTable(3)">Seiten/Teile</th>
                    <th onclick="sortTable(4)">Status</th>
                    <th onclick="sortTable(5)">Bewertung</th>
                    <th onclick="sortTable(6)">Kommentar</th>
                    <th onclick="sortTable(7)">Löschen</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while($row = $result->fetch_assoc()) {
                $buch_id = $row['id']; 
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td><button onclick='oeffneBearbeitenModal(" . $row['id'] . ")'>Bearbeiten</button></td>";

                // === THEMEN-BLOCK (unverändert) ===
                echo "<td>";
                $themen_sql = "SELECT thema.name, thema.ID 
                               FROM thema 
                               JOIN buch_thema ON thema.ID = buch_thema.thema_id 
                               WHERE buch_thema.buch_id = $buch_id";
                $themen_res = $conn->query($themen_sql);
                
                $themen_links = [];
                while($t_row = $themen_res->fetch_assoc()) {
                    $thema_name = $t_row['name'];
                    $suche_sauber = str_replace('&', '', $thema_name);
                    
                    $zusatz = "";
                    switch ($thema_name) {
                        case 'Einsatzteams': $zusatz = "Polizei Feuerwehr Rettung"; break;
                        case 'Tiere': $zusatz = "Bauernhof Zoo Safari Dschungel"; break;
                        case 'Alltag': $zusatz = "Zuhause Schule Kindergarten"; break;
                        case 'Fahrzeuge': $zusatz = "Autos Baustelle Rennwagen Züge"; break;
                        case 'Wissenschaften': $zusatz = "Weltraum Körper Experimente Technik"; break;
                        case 'Lesen lernen': $zusatz = "Erstleser Buchstaben Leselauscher"; break;
                        case 'Fantasy': $zusatz = "Drachen Elfen Einhorn Fabelwesen"; break;
                        case 'Geschichte': $zusatz = "Ritter Steinzeit Altes Rom Ägypten"; break;
                        case 'Musik': $zusatz = "Instrumente Lieder Orchester Musikschule"; break;
                        case 'Sport': $zusatz = "Fussball Turnen Tanzen Bewegung"; break;
                        case 'Kochen & Basteln': $zusatz = "Kuchen Suppen kinderleicht Schere Buntpapier Stifte"; break;
                        case 'Länder & Kulturen': $zusatz = "Weltatlas Europa Flaggen Weltreise"; break;
                        case 'Zahlen & Rechnen': $zusatz = "Zahlen Formen Geometrie Mathe"; break;
                    }
                    
                    $suchbegriff = trim("tiptoi " . $suche_sauber . " " . $zusatz);
                    $themen_links[] = '<a href="https://www.google.com/search?q=' . urlencode($suchbegriff) . '" target="_blank">' . htmlspecialchars($thema_name) . '</a>';
                }
                echo implode(", ", $themen_links);
                echo "</td>";
                // === ENDE THEMEN ===

                echo "<td>" . htmlspecialchars($row['seitenanzahl']) . "</td>";

                $status_class = ($row['ausgeliehen'] == 1) ? "status-ausgeliehen" : "status-regal";
                $status_text = ($row['ausgeliehen'] == 1) ? "Ausgeliehen (" . htmlspecialchars($row['ausgeliehen_von']) . ")" : "Im Regal";
                echo "<td><span class='$status_class'>" . $status_text . "</span></td>";
                
                echo "<td>" . htmlspecialchars($row['bewertung_eltern']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Kommentar']) . "</td>";

                echo "<td><a href='loeschen.php?id=" . $row['id'] . "' onclick='return confirm(\"Wirklich löschen?\")' style='color:red; text-decoration:none;'>[X]</a></td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>

    <?php else: ?>

        <div style="text-align: center; margin: 60px 0; font-size: 18px; color: #666;">
            <p><strong>Noch keine Medien eingetragen.</strong></p>
            <p>Klicke auf "+ Neues Medium", um deine erste Tiptoi-Eintragung zu machen.</p>
        </div>

    <?php endif; ?>

    <br> <br> <button onclick="oeffneModal()">+ Neues Medium</button>


<div id="meinModal" class="modal">

  <div class="modal-content">
    <span class="close" onclick="schliesseModal()">&times;</span>
    <h2> </h2>
    
    <div id="modalFormularContainer">
    <iframe src="tiptoi_Eingabe.php"></iframe>
</div>
  </div>

</div>

<script>
// Suche (Wieder eingefügt und abgesichert)
document.getElementById("suchInput").addEventListener("keyup", function() {
    let filter = this.value.toUpperCase();
    let table = document.getElementById("meineTabelle");
    if (!table) return;
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName("td");
        let found = false;

        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                let txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        tr[i].style.display = found ? "" : "none";
    }
});

// Sortierfunktion (Wieder eingefügt und abgesichert)
let sortDirection = {};

function sortTable(n) {
    let table = document.getElementById("meineTabelle");
    if (!table) return;
    let switching = true;
    let dir = (sortDirection[n] === "asc") ? "desc" : "asc";
    sortDirection[n] = dir;

    while (switching) {
        switching = false;
        let rows = table.rows;
        let shouldSwitch;

        for (let i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;

            let x = rows[i].getElementsByTagName("td")[n];
            let y = rows[i + 1].getElementsByTagName("td")[n];

            if (!x || !y) continue;

            let xValue = x.textContent.trim();
            let yValue = y.textContent.trim();

            if (n === 3) {
                xValue = parseFloat(xValue) || 0;
                yValue = parseFloat(yValue) || 0;
            }

            if (dir === "asc") {
                if (xValue > yValue) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (xValue < yValue) {
                    shouldSwitch = true;
                    break;
                }
            }
        }

        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
}

function oeffneModal(id = null) {
    var iframe = document.querySelector("#modalFormularContainer iframe");
    if (id) {
        iframe.src = "bearbeiten.php?id=" + id;
    } else {
        iframe.src = "tiptoi_Eingabe.php";
    }
    document.getElementById("meinModal").style.display = "block";
}

function oeffneBearbeitenModal(id) {
    var iframe = document.querySelector("#modalFormularContainer iframe");
    iframe.src = "bearbeiten.php?id=" + id;
    document.getElementById("meinModal").style.display = "block";
}

function schliesseModal() {
    document.getElementById("meinModal").style.display = "none";
}

function nachSpeichernAktualisieren() {
    schliesseModal();
    window.location.reload();
}

window.onclick = function(event) {
    if (event.target == document.getElementById("meinModal")) {
        schliesseModal();
    }
}
</script>
</body>
</html>
