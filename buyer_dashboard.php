<?php
include 'db.php';
$result = mysqli_query($conn, "SELECT * FROM cars");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Available Cars</title>
</head>
<body>
    <h2>Available Cars for Rent</h2>
    <?php while ($car = mysqli_fetch_assoc($result)) { ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <p><strong>Owner:</strong> <?= $car['owner_name'] ?></p>
            <p><strong>Email:</strong> <?= $car['email'] ?></p>
            <p><strong>Phone:</strong> <?= $car['phone'] ?></p>
            <p><strong>Vehicle:</strong> <?= $car['vehicle_name'] ?> (<?= $car['vehicle_no'] ?>)</p>
            <p><strong>Available From:</strong> <?= $car['start_date'] ?> to <?= $car['end_date'] ?></p>
            <p><strong>Price/Day:</strong> ₹<?= $car['price'] ?></p>
            <p><strong>Aadhar:</strong> <?= $car['aadhar'] ?></p>
            <img src="<?= $car['vehicle_photo'] ?>" width="200" alt="Vehicle"><br>
        </div>
    <?php } ?>
</body>
</html>
