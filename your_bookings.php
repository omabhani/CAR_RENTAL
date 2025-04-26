<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['buyer_id'])) {
    echo "<script>alert('Session expired or not logged in. Please login again.'); window.location.href='buyer_login.php';</script>";
    exit;
}

$buyer_id = $_SESSION['buyer_id'];

// Fetch bookings and owner details
$query = "SELECT b.*, c.vehicle_name AS car_name, c.vehicle_photo AS car_image, 
                 c.owner_name, c.phone AS owner_phone, c.email AS owner_email,
                 p.payment_amount, p.payment_status 
          FROM bookings b 
          JOIN cars c ON b.car_id = c.id 
          LEFT JOIN payments p ON b.booking_id = p.booking_id
          WHERE b.user_id = ? 
          ORDER BY b.booking_id DESC";


$stmt = $conn->prepare($query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #adb5bd;
            --pending-color: #ff9e00;
            --confirmed-color: #38b000;
            --completed-color: #2d6a4f;
            --cancelled-color: #d90429;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .page-container {
            padding: 30px 15px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 20px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 600;
            color: var(--dark-color);
        }

        .booking-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-btn {
            padding: 10px 20px;
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .card-image-container {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .booking-card:hover .car-image {
            transform: scale(1.05);
        }

        .booking-status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
        }

        .pending {
            background-color: var(--pending-color);
        }

        .confirmed {
            background-color: var(--confirmed-color);
        }

        .completed {
            background-color: var(--completed-color);
        }

        .cancelled {
            background-color: var(--cancelled-color);
        }

        .card-content {
            padding: 20px;
        }

        .car-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }

        .booking-dates {
            display: flex;
            justify-content: space-between;
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .date-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .date-label {
            font-size: 12px;
            color: var(--gray-color);
            margin-bottom: 5px;
        }

        .date-value {
            font-weight: 500;
            font-size: 14px;
        }

        .booking-details {
            margin-bottom: 15px;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .details-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--gray-color);
            font-size: 14px;
        }

        .detail-value {
            font-weight: 500;
            font-size: 14px;
        }

        .owner-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .owner-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .owner-title i {
            color: var(--primary-color);
        }

        .owner-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .owner-detail {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .owner-detail i {
            color: var(--gray-color);
            font-size: 14px;
            width: 18px;
        }

        .owner-value {
            font-size: 14px;
        }

        .payment-summary {
            margin-top: 15px;
            padding: 15px;
            background-color: #f0f7ff;
            border-radius: 8px;
        }

        .payment-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-title i {
            color: var(--primary-color);
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .payment-label {
            color: var(--gray-color);
            font-size: 14px;
        }

        .payment-value {
            font-weight: 500;
            font-size: 14px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px dashed #d0d0d0;
            margin-top: 5px;
        }

        .total-label {
            font-weight: 600;
            font-size: 15px;
        }

        .total-value {
            font-weight: 600;
            font-size: 16px;
            color: var(--primary-color);
        }

        .payment-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            margin-left: 10px;
        }

        .paid {
            background-color: #e3fcef;
            color: #0c6b37;
        }

        .unpaid {
            background-color: #ffe8e8;
            color: #d90429;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .view-details-btn {
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        .view-details-btn:hover {
            background-color: #e9ecef;
        }

        .cancel-btn {
            background-color: #fff1f1;
            color: var(--warning-color);
        }

        .cancel-btn:hover {
            background-color: #ffe0e0;
        }

        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .no-bookings i {
            font-size: 60px;
            color: var(--gray-color);
            margin-bottom: 20px;
        }

        .no-bookings h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .no-bookings p {
            color: var(--gray-color);
            margin-bottom: 25px;
            font-size: 16px;
        }

        .browse-cars-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .browse-cars-btn:hover {
            background-color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .bookings-grid {
                grid-template-columns: 1fr;
            }
            
            .booking-filters {
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .filter-btn {
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Your Bookings</h1>
              
            </div>
            
            <div class="booking-filters">
                <button class="filter-btn active">All Bookings</button>
                <button class="filter-btn">Pending</button>
                <button class="filter-btn">Confirmed</button>
                <button class="filter-btn">Completed</button>
                <button class="filter-btn">Cancelled</button>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="bookings-grid">
                    <?php while ($row = $result->fetch_assoc()): 
                        // Decode vehicle_photo (assuming it's stored as JSON)
                        $images = json_decode($row['car_image']);
                        $firstImage = is_array($images) && !empty($images) ? $images[0] : '';
                        
                        // Format dates for better display
                        $startDate = date('d M Y', strtotime($row['start_date']));
                        $endDate = date('d M Y', strtotime($row['end_date']));
                        
                        // Payment status styling
                        $paymentStatusClass = strtolower($row['payment_status']) == 'paid' ? 'paid' : 'unpaid';
                    ?>
                        <div class="booking-card">
                            <div class="card-image-container">
                                <?php if ($firstImage): ?>
                                    <img src="<?= htmlspecialchars($firstImage) ?>" class="car-image" alt="Car Image">
                                <?php else: ?>
                                    <div class="car-image" style="background-color: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-car" style="font-size: 50px; color: #adb5bd;"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="booking-status-badge <?= strtolower($row['booking_status']) ?>">
                                    <?= ucfirst($row['booking_status']) ?>
                                </span>
                            </div>
                            
                            <div class="card-content">
                                <h3 class="car-name"><?= htmlspecialchars($row['car_name']) ?></h3>
                                
                                <div class="booking-dates">
                                    <div class="date-item">
                                        <span class="date-label">PICKUP</span>
                                        <span class="date-value"><?= $startDate ?></span>
                                    </div>
                                    <div class="date-item">
                                        <i class="fas fa-arrow-right" style="color: var(--gray-color);"></i>
                                    </div>
                                    <div class="date-item">
                                        <span class="date-label">RETURN</span>
                                        <span class="date-value"><?= $endDate ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    <div class="details-row">
                                        <span class="detail-label">Duration</span>
                                        <span class="detail-value"><?= $row['total_days'] ?> Days</span>
                                    </div>
                                    <div class="details-row">
                                        <span class="detail-label">Price Per Day</span>
                                        <span class="detail-value">₹<?= number_format($row['price_per_day'], 2) ?></span>
                                    </div>
                                </div>
                                
                                <div class="payment-summary">
                                    <div class="payment-title">
                                        <i class="fas fa-receipt"></i>
                                        <span>Payment Summary</span>
                                    </div>
                                    
                                    <div class="payment-row">
                                        <span class="payment-label">Rental Cost</span>
                                        <span class="payment-value">₹<?= number_format($row['price_per_day'] * $row['total_days'], 2) ?></span>
                                    </div>
                                    
                                    <div class="payment-row">
                                        <span class="payment-label">Service Fee</span>
                                        <span class="payment-value">₹<?= number_format($row['service_fee'], 2) ?></span>
                                    </div>
                                    
                                    <div class="total-row">
                                        <span class="total-label">Total Amount</span>
                                        <div>
                                            <span class="total-value">₹<?= number_format($row['total_amount'], 2) ?></span>
                                            <span class="payment-status <?= $paymentStatusClass ?>"><?= $row['payment_status'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="owner-info">
                                    <div class="owner-title">
                                        <i class="fas fa-user"></i>
                                        <span>Owner Information</span>
                                    </div>
                                    
                                    <div class="owner-details">
                                        <div class="owner-detail">
                                            <i class="fas fa-user-circle"></i>
                                            <span class="owner-value"><?= htmlspecialchars($row['owner_name']) ?></span>
                                        </div>
                                        <div class="owner-detail">
                                            <i class="fas fa-phone"></i>
                                            <span class="owner-value"><?= htmlspecialchars($row['owner_phone']) ?></span>
                                        </div>
                                        <div class="owner-detail" style="grid-column: span 2;">
                                            <i class="fas fa-envelope"></i>
                                            <span class="owner-value"><?= htmlspecialchars($row['owner_email']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Bookings Found</h3>
                    <p>You haven't made any car bookings yet. Start exploring our collection of cars.</p>
                    <a href="index.php" class="browse-cars-btn">Browse Cars</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // JavaScript for filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const bookingCards = document.querySelectorAll('.booking-card');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filter = this.textContent.trim().toLowerCase();
                    
                    // Show/hide cards based on filter
                    bookingCards.forEach(card => {
                        const statusBadge = card.querySelector('.booking-status-badge');
                        const status = statusBadge.textContent.trim().toLowerCase();
                        
                        if (filter === 'all bookings' || status === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>