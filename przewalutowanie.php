<?php 
//6574654

    require'conn.php';    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kwota = $_POST['kwota'];
    $walutaZrodlowa = $_POST['waluta_zrodlowa'];
    $walutaDocelowa = $_POST['waluta_docelowa'];

    $zapytanie = "SELECT `kurs` FROM `tabela_kursow` WHERE `kod_waluty`= '$walutaZrodlowa'";
    $kursWalutyZrodlowej = mysqli_query($polaczenie, $zapytanie);
             
    $zapytanie = "SELECT `kurs` FROM `tabela_kursow` WHERE `kod_waluty`= '$walutaDocelowa'";
    $kursWalutyDocelowej = mysqli_query($polaczenie, $zapytanie);

    $wynikPrzewalutwania = przewalutujKwote($kwota, $kursWalutyZrodlowej, $kursWalutyDocelowej);
    zapiszKwote($kwota, $walutaZrodlowa, $walutaDocelowa, $wynikPrzewalutwania);
    
    echo $kwota . " " . $walutaZrodlowa . " po przewalutwaniu to : ". $wynikPrzewalutwania . " ". $walutaDocelowa;

    echo '<br> </br><br></br> <a href="index.php"><button>Przejdź do strony głównej</button></a>';

}

  

function przewalutujKwote($kwota, $kursWalutyZrodlowej, $kursWalutyDocelowej) {

        $wiersz = $kursWalutyZrodlowej->fetch_assoc();
        $kursWalutyZrodlowejLiczba = $wiersz['kurs'];

        $wiersz2 = $kursWalutyDocelowej->fetch_assoc();
        $kursWalutyDocelowejLiczba = $wiersz2['kurs'];

    $wynikPrzewalutwania = $kwota * $kursWalutyZrodlowejLiczba / $kursWalutyDocelowejLiczba;
    return $wynikPrzewalutwania;

}


function zapiszKwote($kwota, $walutaZrodlowa, $walutaDocelowa, $wynikPrzewalutwania){}

    $sql = "INSERT INTO historia (waluta_zrodlowa, waluta_docelowa, kwota_zrodlowa, kwota_przewalutowana) VALUES ('$walutaZrodlowa', '$walutaDocelowa', '$kwota', '$wynikPrzewalutwania')";
    mysqli_query($polaczenie, $sql);


?>

