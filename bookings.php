<?php 
include 'db.php';
session_start();  
if (!isset($_SESSION['admin'])) {     
    header("Location: login.php");     
    exit(); 
} 

// Handle status update if form is submitted
if(isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE bookings SET booking_status = '$new_status' WHERE booking_id = $booking_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to avoid form resubmission
    header("Location: bookings.php");
    exit();
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if(!empty($search)) {
    $search_condition = " AND (b.booking_id LIKE '%$search%' OR u.name LIKE '%$search%' OR c.vehicle_name LIKE '%$search%' OR b.booking_status LIKE '%$search%' OR b.email LIKE '%$search%')";
}

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_condition = '';
if(!empty($status_filter)) {
    $status_condition = " AND b.booking_status = '$status_filter'";
}

// Get total bookings count for pagination
$total_query = "SELECT COUNT(*) as total FROM bookings b 
                JOIN cars c ON b.car_id = c.id 
                JOIN buyers u ON b.user_id = u.id 
                WHERE 1=1 $search_condition $status_condition";
$total_result = mysqli_query($conn, $total_query);
$total_bookings = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_bookings / $records_per_page);

// Get bookings with pagination
$bookings_query = "SELECT b.*, c.vehicle_name, u.name as user_name 
                  FROM bookings b 
                  JOIN cars c ON b.car_id = c.id 
                  JOIN buyers u ON b.user_id = u.id 
                  WHERE 1=1 $search_condition $status_condition
                  ORDER BY b.created_at DESC 
                  LIMIT $offset, $records_per_page";
$bookings = mysqli_query($conn, $bookings_query);

// Get booking status counts
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status='pending'"))['count'];
$confirmed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status='confirmed'"))['count'];
$completed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status='completed'"))['count'];
$cancelled_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status='cancelled'"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - CarRental</title>
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
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            font-size: 1.8rem;
            color: #333;
        }
        
        .page-title p {
            color: #777;
            margin-top: 0.3rem;
        }
        
        .status-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .status-filter {
            background-color: #fff;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            flex: 1;
            min-width: 150px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .status-filter.active, .status-filter:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .status-filter h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .status-filter p {
            color: #777;
            font-size: 0.9rem;
        }
        
        .status-filter.status-all.active {
            border-bottom: 3px solid var(--primary);
        }
        
        .status-filter.status-pending.active {
            border-bottom: 3px solid #ffa000;
        }
        
        .status-filter.status-confirmed.active {
            border-bottom: 3px solid #0097a7;
        }
        
        .status-filter.status-completed.active {
            border-bottom: 3px solid #388e3c;
        }
        
        .status-filter.status-cancelled.active {
            border-bottom: 3px solid #d32f2f;
        }
        
        .search-container {
            display: flex;
            margin-bottom: 2rem;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            outline: none;
            font-size: 1rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }
        
        .bookings-table {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            font-weight: 600;
            color: #555;
            background-color: #f9f9f9;
        }
        
        .car-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .car-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .booking-status {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
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
        
        .action-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-btn {
            background-color: #f5f5f5;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1;
            border-radius: 5px;
        }
        
        .action-dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-content a, .dropdown-content form button {
            color: #333;
            padding: 0.8rem 1rem;
            text-decoration: none;
            display: block;
            text-align: left;
            border: none;
            background: none;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
        }
        
        .dropdown-content a:hover, .dropdown-content form button:hover {
            background-color: #f5f5f5;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            gap: 0.5rem;
        }
        
        .pagination li a {
            display: block;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            color: #555;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .pagination li a:hover, .pagination li a.active {
            background-color: var(--primary);
            color: #fff;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #777;
            max-width: 500px;
            margin: 0 auto;
        }
        
        @media screen and (max-width: 1024px) {
            .status-filters {
                grid-template-columns: repeat(2, 1fr);
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
            
            .status-filters {
                grid-template-columns: 1fr;
            }
            
            .bookings-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
        
        @media screen and (max-width: 480px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="cars.php">Cars</a></li>
                <li><a href="buyers.php">Buyers</a></li>
                <li><a href="sellers.php">Sellers</a></li>
                <li><a href="bookings.php" class="active">Bookings</a></li>
                <li><a href="payments.php">Payments</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Bookings Management</h1>
                <p>Manage all car rental bookings</p>
            </div>
        </div>
        
        <div class="status-filters">
            <a href="bookings.php" class="status-filter status-all <?php echo empty($status_filter) ? 'active' : ''; ?>">
                <h3><?php echo $total_bookings; ?></h3>
                <p>All Bookings</p>
            </a>
            <a href="bookings.php?status=pending" class="status-filter status-pending <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                <h3><?php echo $pending_count; ?></h3>
                <p>Pending</p>
            </a>
            <a href="bookings.php?status=confirmed" class="status-filter status-confirmed <?php echo $status_filter == 'confirmed' ? 'active' : ''; ?>">
                <h3><?php echo $confirmed_count; ?></h3>
                <p>Confirmed</p>
            </a>
            
            <a href="bookings.php?status=cancelled" class="status-filter status-cancelled <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">
                <h3><?php echo $cancelled_count; ?></h3>
                <p>Cancelled</p>
            </a>
        </div>
        
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <form action="bookings.php" method="GET">
                    <?php if(!empty($status_filter)): ?>
                    <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Search by booking ID, customer name, car, or status..." value="<?php echo $search; ?>">
                </form>
            </div>
        </div>
        
        <div class="bookings-table">
            <?php if(mysqli_num_rows($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Car</th>
                        <th>Dates</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                        <td>#<?php echo $booking['booking_id']; ?></td>
                        <td>
                            <div>
                                <div><?php echo $booking['user_name']; ?></div>
                                <div style="font-size: 0.8rem; color: #777;"><?php echo $booking['email']; ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="car-info">
                                <?php if(!empty($booking['vehicle_image'])): ?>
                                <img src="uploads/cars/<?php echo $booking['vehicle_image']; ?>" alt="<?php echo $booking['vehicle_name']; ?>" class="car-image">
                                <?php else: ?>
                                <div style="width: 60px; height: 60px; border-radius: 5px; background-color: #eee; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-car" style="color: #ccc; font-size: 1.5rem;"></i>
                                </div>
                                <?php endif; ?>
                                <div><?php echo $booking['vehicle_name']; ?></div>
                            </div>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($booking['start_date'])); ?> - 
                            <?php echo date('M d, Y', strtotime($booking['end_date'])); ?>
                            <div style="font-size: 0.8rem; color: #777;"><?php echo $booking['total_days']; ?> days</div>
                        </td>
                        <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                        <td>
                            <span class="booking-status status-<?php echo strtolower($booking['booking_status']); ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                      
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-xmark"></i>
                <h3>No bookings found</h3>
                <p>
                    <?php if(!empty($search)): ?>
                    No bookings match your search criteria. Try a different search term.
                    <?php elseif(!empty($status_filter)): ?>
                    No bookings with status "<?php echo ucfirst($status_filter); ?>" found.
                    <?php else: ?>
                    There are no bookings in the system yet.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if($total_pages > 1): ?>
        <ul class="pagination">
            <?php if($page > 1): ?>
            <li>
                <a href="bookings.php?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search='.$search : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li>
                <a href="bookings.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.$search : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
            <li>
                <a href="bookings.php?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search='.$search : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</body>
</html>