<?php 
include 'db.php';
session_start();  
if (!isset($_SESSION['admin'])) {     
    header("Location: login.php");     
    exit(); 
} 

// Database connection

// Get counts from different tables
$buyers_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM buyers"))['count'];
$sellers_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM sellers"))['count'];
$cars_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars"))['count'];
$bookings_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$payments_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments"))['count'];

// Get recent bookings
$recent_bookings = mysqli_query($conn, "SELECT b.*, c.vehicle_name, u.name as user_name 
                                       FROM bookings b 
                                       JOIN cars c ON b.car_id = c.id 
                                       JOIN buyers u ON b.user_id = u.id 
                                       ORDER BY b.created_at DESC LIMIT 5");

// Get revenue stats
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(payment_amount) as total FROM payments WHERE payment_status = 'completed'"))['total'] ?? 0;
$monthly_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(payment_amount) as total FROM payments WHERE payment_status = 'completed' AND MONTH(payment_date) = MONTH(CURRENT_DATE())"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CarRental</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #7209b7;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            height: 70px;
        }
        
        .logo a {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-menu li a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-menu li a:hover, .nav-menu li a.active {
            color: var(--primary);
        }
        
        .nav-menu li a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary);
            border-radius: 5px;
        }
        
        #menu-toggle, .menu-icon {
            display: none;
        }
        
        .main-content {
            margin-top: 90px;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .admin-title h1 {
            font-size: 1.8rem;
            color: #333;
        }
        
        .admin-title p {
            color: #777;
            margin-top: 0.3rem;
        }
        
        .logout-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn:hover {
            background-color: var(--secondary);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #777;
            font-size: 0.9rem;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }
        
        .dashboard-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-header h2 {
            font-size: 1.2rem;
            color: #333;
        }
        
        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            font-weight: 600;
            color: #555;
        }
        
        .booking-status {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff8e1;
            color: #ffa000;
        }
        
        .status-confirmed {
            background-color: #e0f7fa;
            color: #0097a7;
        }
        
        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .revenue-stat {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .revenue-stat:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .revenue-stat p {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .revenue-stat h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
        }
        
        @media screen and (max-width: 1024px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }
        
        @media screen and (max-width: 768px) {
            .navbar .container {
                padding: 0 1rem;
            }
            
            .menu-icon {
                display: block;
                cursor: pointer;
            }
            
            .menu-icon span {
                display: block;
                width: 25px;
                height: 3px;
                background-color: #333;
                margin: 5px 0;
                transition: all 0.3s ease;
            }
            
            #menu-toggle:checked ~ .menu-icon span:nth-child(1) {
                transform: rotate(45deg) translate(5px, 5px);
            }
            
            #menu-toggle:checked ~ .menu-icon span:nth-child(2) {
                opacity: 0;
            }
            
            #menu-toggle:checked ~ .menu-icon span:nth-child(3) {
                transform: rotate(-45deg) translate(7px, -6px);
            }
            
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                flex-direction: column;
                background-color: #fff;
                padding: 2rem;
                transition: all 0.3s ease;
                gap: 1rem;
            }
            
            #menu-toggle:checked ~ .nav-menu {
                left: 0;
            }
            
            .stats-cards {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <i class="fas fa-car"></i> CarRental
                </a>
            </div>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span>
                <span></span>
                <span></span>
            </label>
            <ul class="nav-menu">
                <li><a href="admin.php" class="active">Dashboard</a></li>
                <li><a href="cars.php">Cars</a></li>
                <li><a href="buyers.php">Buyers</a></li>
                <li><a href="sellers.php">Sellers</a></li>
                <li><a href="bookings.php">Bookings</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Welcome, <?php echo $_SESSION['admin']; ?></h1>
                <p>Here's what's happening with your car rental service today</p>
            </div>
            <a href="alogout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <div class="icon" style="background-color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?php echo $buyers_count; ?></h3>
                <p>Total Buyers</p>
            </div>
            
            <div class="stat-card">
                <div class="icon" style="background-color: var(--success);">
                    <i class="fas fa-car"></i>
                </div>
                <h3><?php echo $cars_count; ?></h3>
                <p>Total Cars</p>
            </div>
            
            <div class="stat-card">
                <div class="icon" style="background-color: var(--info);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3><?php echo $sellers_count; ?></h3>
                <p>Total Sellers</p>
            </div>
            
            <div class="stat-card">
                <div class="icon" style="background-color: var(--warning);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?php echo $bookings_count; ?></h3>
                <p>Total Bookings</p>
            </div>
            
            <div class="stat-card">
                <div class="icon" style="background-color: var(--danger);">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3><?php echo $payments_count; ?></h3>
                <p>Total Payments</p>
            </div>
        </div>
        
        <div class="dashboard-sections">
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <a href="bookings.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Dates</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                        <tr>
                            <td>#<?php echo $booking['booking_id']; ?></td>
                            <td><?php echo $booking['user_name']; ?></td>
                            <td><?php echo $booking['vehicle_name']; ?></td>
                            <td><?php echo date('M d', strtotime($booking['start_date'])); ?> - <?php echo date('M d', strtotime($booking['end_date'])); ?></td>
                            <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                            <td>
                                <span class="booking-status status-<?php echo strtolower($booking['booking_status']); ?>">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($recent_bookings) == 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No recent bookings found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Revenue Overview</h2>
                </div>
                <div class="revenue-stat">
                    <p>Total Revenue</p>
                    <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
                </div>
                <div class="revenue-stat">
                    <p>Monthly Revenue</p>
                    <h3>₹<?php echo number_format($monthly_revenue, 2); ?></h3>
                </div>
                <div class="revenue-stat">
                    <p>Payment Success Rate</p>
                    <h3>
                        <?php 
                        $total_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments"))['count'];
                        $successful_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE payment_status = 'completed'"))['count'];
                        $success_rate = ($total_payments > 0) ? ($successful_payments / $total_payments) * 100 : 0;
                        echo number_format($success_rate, 1) . '%';
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>
</body>
</html>