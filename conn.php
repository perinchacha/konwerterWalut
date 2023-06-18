<?php
$polaczenie = @mysqli_connect('localhost', 'root', '', 'perinchacha_adrespect');
if (!$polaczenie) {
    die('Nie można połączyć się z bazą danych: ' . mysqli_connect_error());
}


?>