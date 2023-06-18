<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Konwerter NBP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
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
            $zapytanie = "UPDATE tabela_kursow SET kurs = ?, data_aktualna = ? WHERE kod_waluty = ?";

            $stmt = $polaczenie->prepare($zapytanie);
            $stmt->bind_param("dss", $kurs_sredni, $dataa, $kod_waluty);
            $wynik = $stmt->execute();

            if (!$wynik) {
                echo 'Błąd podczas zapisywania kursu waluty: ' . $stmt->error;
            }

            //aktualizacja daty PLN
            $dataa = date('Y-m-d');
            $zapytanie_pln = "UPDATE tabela_kursow SET data_aktualna = ? WHERE kod_waluty = 'PLN'";

            $stmt_pln = $polaczenie->prepare($zapytanie_pln);
            $stmt_pln->bind_param("s", $dataa);
            $wynik_pln = $stmt_pln->execute();

            if (!$wynik_pln) {
                echo 'Błąd podczas zapisywania kursu waluty: ' . $stmt->error;
            }
        }

        // Zakończenie połączenia z bazą danych
        $stmt_pln->close();
        $stmt->close();
        $polaczenie->close();
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
            echo '<br> <center><b> Aktualny Kurs </b></center>
            <br><table>
                <tr>
                    <th>Kod Waluty</th>
                    <th>Nazwa Waluty</th>
                    <th>Kurs [PLN]</th>
                    <th>Data</th>
                </tr>';

            while ($row = mysqli_fetch_assoc($wynik)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['kod_waluty']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nazwa_waluty']) . '</td>';
                echo '<td>' . htmlspecialchars($row['kurs']) . '</td>';
                echo '<td>' . htmlspecialchars($row['data_aktualna']) . '</td>';
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
        echo '<br><center><b> Konwerter Walut </b></center> <br>';
        echo '<form method="POST" action="przewalutowanie.php">';
        echo '<label>Kwota:</label>';
        echo '<input type="text" name="kwota" pattern="[0-9]+([.][0-9]+)?" required oninput="this.value = this.value.replace(/[^0-9.]/g, \'\')">';
        echo '<label>Waluta źródłowa:</label>';
        echo '<select name="waluta_zrodlowa" required>';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['kod_waluty']) . '">' . htmlspecialchars($row['kod_waluty']) . '</option>';
            }
        }

        echo '</select>';
        echo '<label>Waluta docelowa:</label>';
        echo '<select name="waluta_docelowa" required>';

        if ($result->num_rows > 0) {
            while ($row = $result2->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['kod_waluty']) . '">' . htmlspecialchars($row['kod_waluty']) . '</option>';
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
            echo ' <br> <center><b>Historia Przewalutowań </b></center>
                <br> <table>
                <tr>
                    <th>Kwota Źródłowa</th>
                    <th>Waluta Źródłowa</th>
                    <th>Kwota Przewalutowana</th>
                    <th>Waluta Docelowa</th>
                    <th>Data</th>
                </tr>';

            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['kwota_zrodlowa']) . '</td>';
                echo '<td>' . htmlspecialchars($row['waluta_zrodlowa']) . '</td>';
                echo '<td>' . htmlspecialchars($row['kwota_przewalutowana']) . '</td>';
                echo '<td>' . htmlspecialchars($row['waluta_docelowa']) . '</td>';
                echo '<td>' . htmlspecialchars($row['data']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }

    generujFormularzPrzewalutowania();
    generujTabeleKursowWalut();
    pobierzIKopiujKursyWalut();
    generujHistoriePrzewalutowania();

    ?>
</body>

</html>