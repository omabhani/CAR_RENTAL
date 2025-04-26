<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['seller_id'])) {
    echo "<script>alert('Session expired or not logged in. Please login again.'); window.location.href='seller_login.php';</script>";
    exit;
}

$seller_id = $_SESSION['seller_id'];

// Fetch the booking requests for the seller's cars
$query = "SELECT b.*, c.vehicle_name AS car_name, c.owner_name, c.phone AS owner_phone, c.email AS owner_email
          FROM bookings b
          JOIN cars c ON b.car_id = c.id
          WHERE c.seller_id = ? AND b.booking_status = 'pending'
          ORDER BY b.booking_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Requests</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #4CAF50;
            --danger-color: #f44336;
            --warning-color: #ff9800;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --gray-color: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: #333;
            line-height: 1.6;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e1e5eb;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .page-subtitle {
            color: var(--gray-color);
            font-size: 1rem;
        }

        .booking-count {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px;
            padding: 2px 10px;
            margin-left: 10px;
            font-size: 0.9rem;
        }

        .booking-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 20px -5px rgba(0, 0, 0, 0.15);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .car-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
        }

        .booking-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: rgba(255, 152, 0, 0.15);
            color: var(--warning-color);
        }

        .booking-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .booking-details, .customer-details {
            padding: 15px;
            border-radius: var(--border-radius);
            background-color: #f8f9fa;
        }

        .section-title {
            font-size: 1.1rem;
            color: var(--gray-color);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .detail-item {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .detail-item i {
            margin-right: 10px;
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }

        .detail-label {
            font-weight: 600;
            margin-right: 8px;
            color: var(--dark-color);
        }

        .license-section {
            margin-top: 20px;
        }

        .license-image {
            width: 100%;
            max-width: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .license-image:hover {
            transform: scale(1.05);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-accept {
            background-color: var(--success-color);
            color: white;
        }

        .btn-accept:hover {
            background-color: #388e3c;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .btn-decline {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-decline:hover {
            background-color: #d32f2f;
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-message {
            color: var(--gray-color);
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .booking-content {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
  

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Booking Requests
                <?php if ($result->num_rows > 0): ?>
                <span class="booking-count"><?= $result->num_rows ?></span>
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">Manage your vehicle booking requests</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h2 class="car-name"><?= htmlspecialchars($row['car_name']) ?></h2>
                        <span class="booking-status status-pending">
                            <i class="fas fa-clock"></i> Pending
                        </span>
                    </div>

                    <div class="booking-content">
                        <div class="booking-details">
                            <h3 class="section-title">Booking Details</h3>
                            
                            <div class="detail-item">
                                <i class="far fa-calendar-alt"></i>
                                <span class="detail-label">Start Date:</span>
                                <span><?= $row['start_date'] ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="far fa-calendar-check"></i>
                                <span class="detail-label">End Date:</span>
                                <span><?= $row['end_date'] ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="far fa-clock"></i>
                                <span class="detail-label">Duration:</span>
                                <?php
                                    $start = new DateTime($row['start_date']);
                                    $end = new DateTime($row['end_date']);
                                    $diff = $start->diff($end);
                                    echo $diff->days + 1 . " days";
                                ?>
                            </div>
                        </div>

                        <div class="customer-details">
                            <h3 class="section-title">Customer Details</h3>
                            
                            <div class="detail-item">
                                <i class="fas fa-user"></i>
                                <span class="detail-label">Name:</span>
                                <span><?= htmlspecialchars($row['owner_name']) ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <span class="detail-label">Phone:</span>
                                <span><?= htmlspecialchars($row['owner_phone']) ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <span class="detail-label">Email:</span>
                                <span><?= htmlspecialchars($row['owner_email']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="license-section">
                        <h3 class="section-title">License Verification</h3>
                        <img src="uploads/licenses/<?= htmlspecialchars($row['license_photo']); ?>" alt="License Image" class="license-image">
                    </div>

                    <div class="action-buttons">
                        <a href="update_booking_status.php?booking_id=<?= $row['booking_id'] ?>&status=accepted" class="btn btn-accept">
                            <i class="fas fa-check"></i> Accept Request
                        </a>
                        <a href="update_booking_status.php?booking_id=<?= $row['booking_id'] ?>&status=rejected" class="btn btn-decline">
                            <i class="fas fa-times"></i> Decline Request
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="far fa-calendar-times"></i>
                </div>
                <h3 class="empty-title">No Pending Requests</h3>
                <p class="empty-message">You don't have any pending booking requests at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add any JavaScript functionality here if needed
    </script>
</body>
</html>