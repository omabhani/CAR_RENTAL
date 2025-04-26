<?php 
include 'db.php';
session_start();  
if (!isset($_SESSION['admin'])) {     
    header("Location: login.php");     
    exit(); 
} 

// Handle delete action
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $car_id = $_GET['delete'];
    // Check if car exists
    $check_car = mysqli_query($conn, "SELECT * FROM cars WHERE id = $car_id");
    if(mysqli_num_rows($check_car) > 0) {
        // Check if car has bookings
        $check_bookings = mysqli_query($conn, "SELECT * FROM bookings WHERE car_id = $car_id");
        if(mysqli_num_rows($check_bookings) > 0) {
            $delete_error = "Cannot delete car. It has associated bookings.";
        } else {
            // Get car photo filename
            $car_data = mysqli_fetch_assoc($check_car);
            $photo_path = $car_data['vehicle_photo'];
            
            // Delete car from database
            $delete_query = mysqli_query($conn, "DELETE FROM cars WHERE id = $car_id");
            
            if($delete_query) {
                // Delete photo from server if it exists
                if(!empty($photo_path) && file_exists("uploads/cars/" . $photo_path)) {
                    unlink("uploads/cars/" . $photo_path);
                }
                $delete_success = "Car deleted successfully!";
            } else {
                $delete_error = "Error deleting car: " . mysqli_error($conn);
            }
        }
    } else {
        $delete_error = "Car not found!";
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if(!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $search_condition = " WHERE vehicle_name LIKE '%$search%' OR vehicle_no LIKE '%$search%' OR owner_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
}

// Sorting functionality
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Valid sort columns
$valid_sort_columns = ['id', 'vehicle_name', 'price', 'owner_name', 'created_at'];
if(!in_array($sort, $valid_sort_columns)) {
    $sort = 'id';
}

// Valid order directions
$valid_orders = ['ASC', 'DESC'];
if(!in_array($order, $valid_orders)) {
    $order = 'DESC';
}

// Get total records count
$total_records_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM cars" . $search_condition);
$total_records = mysqli_fetch_assoc($total_records_query)['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get cars data with seller information
$cars_query = mysqli_query($conn, "SELECT c.*, s.name as seller_name 
                                   FROM cars c 
                                   LEFT JOIN sellers s ON c.seller_id = s.id 
                                   $search_condition 
                                   ORDER BY c.$sort $order 
                                   LIMIT $offset, $records_per_page");

// Get booking counts for each car
$car_booking_counts = [];
$booking_counts_query = mysqli_query($conn, "SELECT car_id, COUNT(*) as booking_count FROM bookings GROUP BY car_id");
while($count = mysqli_fetch_assoc($booking_counts_query)) {
    $car_booking_counts[$count['car_id']] = $count['booking_count'];
}

// Function to get opposite sort order
function getOppositeOrder($current_order) {
    return ($current_order == 'ASC') ? 'DESC' : 'ASC';
}

// Function to display sort icons
function getSortIcon($column, $current_sort, $current_order) {
    if($column == $current_sort) {
        return ($current_order == 'ASC') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
    }
    return '<i class="fas fa-sort"></i>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars Management - CarRental</title>
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
        
        .add-btn {
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
        
        .add-btn:hover {
            background-color: var(--secondary);
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .search-form {
            display: flex;
            gap: 0.5rem;
        }
        
        .search-form input {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
        }
        
        .search-form button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-form button:hover {
            background-color: var(--secondary);
        }
        
        .cars-table {
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
            background-color: #f9fafb;
            font-weight: 600;
            color: #555;
            cursor: pointer;
        }
        
        table th i {
            margin-left: 0.5rem;
            font-size: 0.8rem;
        }
        
        table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .price {
            font-weight: 600;
            color: var(--primary);
        }
        
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .view-btn {
            background-color: var(--info);
        }
        
        .view-btn:hover {
            background-color: #3a7bdb;
        }
        
        .edit-btn {
            background-color: var(--success);
        }
        
        .edit-btn:hover {
            background-color: #3bb3c3;
        }
        
        .delete-btn {
            background-color: var(--warning);
        }
        
        .delete-btn:hover {
            background-color: #e91e63;
        }
        
        .booking-count {
            font-size: 0.9rem;
            background-color: #e0f7fa;
            color: #0097a7;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .pagination a {
            background-color: white;
            color: var(--primary);
            border: 1px solid #ddd;
        }
        
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        
        .pagination span {
            background-color: var(--primary);
            color: white;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #388e3c;
            border: 1px solid #c8e6c9;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }
        
        @media screen and (max-width: 1024px) {
            .search-form input {
                width: 200px;
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
            
            .controls {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .search-form {
                width: 100%;
            }
            
            .search-form input {
                width: 100%;
            }
            
            .cars-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
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
                <li><a href="cars.php" class="active">Cars</a></li>
                <li><a href="buyers.php">Buyers</a></li>
                <li><a href="sellers.php">Sellers</a></li>
                <li><a href="bookings.php">Bookings</a></li>
                <li><a href="payments.php">Payments</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Cars Management</h1>
            </div>
            <a href="add_car.php" class="add-btn">
                <i class="fas fa-plus"></i> Add New Car
            </a>
        </div>
        
        <?php if(isset($delete_success)): ?>
        <div class="alert alert-success">
            <?php echo $delete_success; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($delete_error)): ?>
        <div class="alert alert-danger">
            <?php echo $delete_error; ?>
        </div>
        <?php endif; ?>
        
        <div class="controls">
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by car name, number, owner..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="results-info">
                Showing <?php echo min($offset + 1, $total_records); ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
            </div>
        </div>
        
        <div class="cars-table">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="?sort=id&order=<?php echo getOppositeOrder($order); ?>&search=<?php echo urlencode($search); ?>">
                                ID <?php echo getSortIcon('id', $sort, $order); ?>
                            </a>
                        </th>
                      
                        <th>
                            <a href="?sort=vehicle_name&order=<?php echo getOppositeOrder($order); ?>&search=<?php echo urlencode($search); ?>">
                                Car Details <?php echo getSortIcon('vehicle_name', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=price&order=<?php echo getOppositeOrder($order); ?>&search=<?php echo urlencode($search); ?>">
                                Price/Day <?php echo getSortIcon('price', $sort, $order); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=owner_name&order=<?php echo getOppositeOrder($order); ?>&search=<?php echo urlencode($search); ?>">
                                Owner <?php echo getSortIcon('owner_name', $sort, $order); ?>
                            </a>
                        </th>
                        <th>Bookings</th>
                        <th>
                            <a href="?sort=created_at&order=<?php echo getOppositeOrder($order); ?>&search=<?php echo urlencode($search); ?>">
                                Added On <?php echo getSortIcon('created_at', $sort, $order); ?>
                            </a>
                        </th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($cars_query) > 0): ?>
                    <?php while($car = mysqli_fetch_assoc($cars_query)): ?>
                    <tr>
                        <td>#<?php echo $car['id']; ?></td>
                       
                        <td>
                            <strong><?php echo $car['vehicle_name']; ?></strong><br>
                            <small>Reg No: <?php echo $car['vehicle_no']; ?></small>
                        </td>
                        <td class="price">₹<?php echo number_format($car['price'], 2); ?></td>
                        <td>
                            <?php echo $car['owner_name']; ?><br>
                            <small><?php echo $car['phone']; ?></small>
                        </td>
                        <td>
                            <span class="booking-count">
                                <?php echo isset($car_booking_counts[$car['id']]) ? $car_booking_counts[$car['id']] : 0; ?> bookings
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($car['created_at'])); ?></td>
                        
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No cars found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-angle-double-left"></i></a>
            <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-angle-left"></i></a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($start_page + 4, $total_pages);
            
            if($end_page - $start_page < 4 && $start_page > 1) {
                $start_page = max(1, $end_page - 4);
            }
            
            for($i = $start_page; $i <= $end_page; $i++):
            ?>
                <?php if($i == $page): ?>
                <span><?php echo $i; ?></span>
                <?php else: ?>
                <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-angle-right"></i></a>
            <a href="?page=<?php echo $total_pages; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-angle-double-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>