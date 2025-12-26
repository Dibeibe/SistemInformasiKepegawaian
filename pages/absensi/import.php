<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
checkLogin();
checkRole('admin'); // hanya admin yang boleh import

if (isset($_POST['upload'])) {
    $file = $_FILES['csv']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $user_id = $data[0];
            $tanggal = $data[1];
            $jam_masuk = $data[2];
            $jam_keluar = $data[3];

            $conn->query("INSERT INTO absensi (user_id, tanggal, jam_masuk, jam_keluar) 
                          VALUES ('$user_id','$tanggal','$jam_masuk','$jam_keluar')
                          ON DUPLICATE KEY UPDATE 
                            jam_masuk=VALUES(jam_masuk), 
                            jam_keluar=VALUES(jam_keluar)");
        }
        fclose($handle);
        echo "<p>Import berhasil!</p>";
    }
}
?>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="csv" accept=".csv" required>
    <button type="submit" name="upload">Import CSV</button>
</form>
