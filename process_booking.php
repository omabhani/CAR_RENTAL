<?php
session_start();
include 'db.php';

// Check if buyer is logged in
if (!isset($_SESSION['buyer_id'])) {
    echo "<script>alert('Session expired or not logged in. Please login again.'); window.location.href='buyer_login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id         = $_POST['car_id'];
    $user_id        = $_SESSION['buyer_id'];
    $name           = $_POST['name'];
    $phone          = $_POST['phone'];
    $email          = $_POST['email'];
    $start_date     = $_POST['start_date'];
    $end_date       = $_POST['end_date'];
    $aadhar_number  = $_POST['aadhar_number'];
    $price_per_day  = $_POST['price_per_day'];
    $card_name      = $_POST['card_name'];
    $card_number    = $_POST['card_number'];
    $last4          = substr($card_number, -4);

    // Upload license photo
    $license_photo_name = $_FILES['license_photo']['name'];
    $license_photo_tmp  = $_FILES['license_photo']['tmp_name'];
    $license_file_name  = time() . "_" . basename($license_photo_name); // Generate unique filename
    $license_path       = "uploads/licenses/" . $license_file_name;

    if (!move_uploaded_file($license_photo_tmp, $license_path)) {
        echo "<script>alert('Failed to upload license photo.'); window.history.back();</script>";
        exit;
    }

    // Calculate total days
    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
    $days = max(1, (int)$days); // Minimum 1 day

    // Pricing logic
    $extra_charge_per_day = 100;
    $final_price_per_day = $price_per_day + $extra_charge_per_day;
    $service_fee = 0;

    $total_amount = $final_price_per_day * $days;

    // Insert booking
    $booking_sql = "INSERT INTO bookings (
        car_id, user_id, name, phone, email, start_date, end_date,
        aadhar_number, license_photo, total_days, price_per_day,
        service_fee, total_amount, booking_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($booking_sql);
    $stmt->bind_param(
        "iisssssssiddi",
        $car_id, $user_id, $name, $phone, $email, $start_date,
        $end_date, $aadhar_number, $license_file_name, $days,
        $final_price_per_day, $service_fee, $total_amount
    );

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;

        // Insert payment
        $payment_sql = "INSERT INTO payments (
            booking_id, payment_amount, card_name, card_number_last4, payment_status
        ) VALUES (?, ?, ?, ?, 'completed')";

        $stmt2 = $conn->prepare($payment_sql);
        $stmt2->bind_param("idss", $booking_id, $total_amount, $card_name, $last4);
        $stmt2->execute();

        echo "<script>alert('Booking successful!'); window.location.href='your_bookings.php';</script>";
    } else {
        echo "<script>alert('Booking failed: " . $stmt->error . "'); window.history.back();</script>";
    }
} else {
    echo "Invalid request.";
}
?>
