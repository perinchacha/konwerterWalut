<head>
    <meta charset="UTF-8">
    <title>Iwtymsek logowanie</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<?php
function pobierzIKopiujKursyWalut()
{
    require 'conn.php';

    // Pobieranie kursów walut z NBP
    $url = 'http://api.nbp.pl/api/exchangerates/tables/A?format=json';
    $json = file_get_contents($url);
    $dane = json_decode($json, true);

    if ($dane === null) {
        die('Błąd podczas pobierania danych z NBP.');
    }

    // Przetwarzanie i zapisywanie kursów walut do bazy danych
    $tabela_kursow = $dane[0]['rates'];

    foreach ($tabela_kursow as $kurs) {
        $kod_waluty = $kurs['code'];
        // $nazwa_waluty = $kurs['currency'];
        $kurs_sredni = $kurs['mid'];
        $dataa = date('Y-m-d');

        // Aktualizuj kurs waluty w bazie danych na podstawie kodu waluty i daty
        $zapytanie = "UPDATE tabela_kursow 
        SET kurs = '$kurs_sredni', data_aktualna = '$dataa'
        WHERE kod_waluty = '$kod_waluty'";

        $wynik = mysqli_query($polaczenie, $zapytanie);

        if (!$wynik) {
            echo 'Błąd podczas zapisywania kursu waluty: ' . mysqli_error($polaczenie);
        }
    }

    // Zakończenie połączenia z bazą danych
    mysqli_close($polaczenie);
}


function generujTabeleKursowWalut()
{

    require 'conn.php';


    // Zapytanie SQL do pobrania danych z tabeli tabela_kursow
    $zapytanie = "SELECT * FROM tabela_kursow ORDER BY kod_waluty ASC";
    $wynik = mysqli_query($polaczenie, $zapytanie);

    if (!$wynik) {
        echo 'Błąd podczas pobierania danych: ' . mysqli_error($polaczenie);
    } else {
        // Wyświetlanie danych w formie tabeli
        echo '<table>
                <tr>
                    <th>Kod Waluty</th>
                    <th>Nazwa Waluty</th>
                    <th>Kurs</th>
                    <th>Data</th>
                </tr>';

        while ($row = mysqli_fetch_assoc($wynik)) {
            echo '<tr>';
            echo '<td>' . $row['kod_waluty'] . '</td>';
            echo '<td>' . $row['nazwa_waluty'] . '</td>';
            echo '<td>' . $row['kurs'] . '</td>';
            echo '<td>' . $row['data_aktualna'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    // Zakończenie połączenia z bazą danych
    mysqli_close($polaczenie);
}
function generujFormularzPrzewalutowania()
{

    require 'conn.php';

    $sql = "SELECT kod_waluty FROM tabela_kursow ORDER BY kod_waluty ASC";
    $result = $polaczenie->query($sql);
    $result2 = $polaczenie->query($sql);

    echo '<form method="POST" action="przewalutowanie.php">';
    echo '<label>Kwota:</label>';
    echo '<input type="text" name="kwota" required>';
    echo '<label>Waluta źródłowa:</label>';
    echo '<select name="waluta_zrodlowa" required>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['kod_waluty'] . '">' . $row['kod_waluty'] . '</option>';
        }
    }
    echo '</select>';
    echo '<label>Waluta docelowa:</label>';
    echo '<select name="waluta_docelowa" required>';

    if ($result->num_rows > 0) {
        while ($row = $result2->fetch_assoc()) {
            echo '<option value="' . $row['kod_waluty'] . '">' . $row['kod_waluty'] . '</option>';
        }
    }

    echo '</select>';
    echo '<input type="submit" value="Przewalutuj">';
    echo '</form>';
}


function generujHistoriePrzewalutowania()
{

    require 'conn.php';

    $sql = "SELECT * FROM historia ORDER BY data DESC ";
    $result = $polaczenie->query($sql);

    if (!$result) {
        echo 'Błąd podczas pobierania danych: ' . mysqli_error($polaczenie);
    } else {
        // Wyświetlanie danych w formie tabeli
        echo '<table>
                <tr>
                    <th>Kwota Źródłowa</th>
                    <th>Waluta Źródłowa</th>
                    <th>Kwota Przewalutowana</th>
                    <th>Waluta Docelowa</th>
                </tr>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['kwota_zrodlowa'] . '</td>';
            echo '<td>' . $row['waluta_zrodlowa'] . '</td>';
            echo '<td>' . $row['kwota_przewalutowana'] . '</td>';
            echo '<td>' . $row['waluta_docelowa'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }
}


generujTabeleKursowWalut();
pobierzIKopiujKursyWalut();


generujFormularzPrzewalutowania();
generujHistoriePrzewalutowania();