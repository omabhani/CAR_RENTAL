<?php 
include 'db.php';
session_start();  
if (!isset($_SESSION['admin'])) {     
    header("Location: login.php");     
    exit(); 
} 



// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where_clause = " WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
}

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$valid_sort_columns = ['id', 'name', 'email', 'phone', 'created_at'];
$valid_order_values = ['ASC', 'DESC'];

if (!in_array($sort, $valid_sort_columns)) {
    $sort = 'id';
}
if (!in_array($order, $valid_order_values)) {
    $order = 'DESC';
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Count total buyers for pagination
$count_query = "SELECT COUNT(*) as total FROM buyers" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_buyers = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_buyers / $per_page);

// Get buyers with bookings count
$query = "SELECT b.*, 
          (SELECT COUNT(*) FROM bookings WHERE user_id = b.id) as bookings_count,
          (SELECT SUM(total_amount) FROM bookings WHERE user_id = b.id) as total_spent
          FROM buyers b" . $where_clause . 
         " ORDER BY $sort $order LIMIT $offset, $per_page";
$buyers = mysqli_query($conn, $query);

// Handle buyer deletion
if (isset($_POST['delete_buyer'])) {
    $buyer_id = (int)$_POST['buyer_id'];
    
    // Check if there are any bookings for this buyer
    $check_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $buyer_id");
    $has_bookings = mysqli_fetch_assoc($check_bookings)['count'] > 0;
    
    if ($has_bookings) {
        $delete_error = "Cannot delete buyer with existing bookings. Please delete or reassign their bookings first.";
    } else {
        mysqli_query($conn, "DELETE FROM buyers WHERE id = $buyer_id");
        header("Location: buyers.php?deleted=true");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyers - CarRental Admin</title>
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
        
        .search-bar {
            display: flex;
            gap: 1rem;
            width: 100%;
            max-width: 500px;
        }
        
        .search-bar input {
            flex-grow: 1;
            padding: 0.7rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .search-bar button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-bar button:hover {
            background-color: var(--secondary);
        }
        
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table-container {
            overflow-x: auto;
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
            position: relative;
        }
        
        table th a {
            text-decoration: none;
            color: #555;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        table th a:hover {
            color: var(--primary);
        }
        
        table th .sort-icon {
            font-size: 0.8rem;
        }
        
        table tr:hover {
            background-color: #f9f9f9;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
            border: none;
        }
        
        .btn-info:hover {
            background-color: #3d84d1;
        }
        
        .btn-danger {
            background-color: #f44336;
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            text-decoration: none;
            color: #555;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #388e3c;
            border-left: 4px solid #388e3c;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            padding: 2rem;
        }
        
        .modal h2 {
            margin-bottom: 1rem;
        }
        
        .modal p {
            margin-bottom: 1.5rem;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .badge {
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: #e3f2fd;
            color: var(--primary);
        }
        
        .stats-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            background-color: #f9f9f9;
            padding: 0.8rem;
            border-radius: 5px;
            flex: 1;
        }
        
        .stat-item .label {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 0.3rem;
        }
        
        .stat-item .value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
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
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .search-bar {
                max-width: 100%;
            }
            
            .stats-row {
                flex-direction: column;
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
                <li><a href="buyers.php" class="active">Buyers</a></li>
                <li><a href="sellers.php">Sellers</a></li>
                <li><a href="bookings.php">Bookings</a></li>
                <li><a href="payments.php">Payments</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Buyers Management</h1>
            </div>
            <form action="" method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by name, email or phone" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <?php if(isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
        <div class="alert alert-success">
            Buyer has been deleted successfully.
        </div>
        <?php endif; ?>
        
        <?php if(isset($delete_error)): ?>
        <div class="alert alert-danger">
            <?php echo $delete_error; ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="stats-row">
                <div class="stat-item">
                    <span class="label">Total Buyers</span>
                    <span class="value"><?php echo $total_buyers; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Active Buyers (with bookings)</span>
                    <span class="value">
                        <?php 
                        $active_buyers = mysqli_fetch_assoc(mysqli_query($conn, 
                            "SELECT COUNT(DISTINCT user_id) as count FROM bookings"))['count'];
                        echo $active_buyers;
                        ?>
                    </span>
                </div>
                <div class="stat-item">
                    <span class="label">Total Revenue</span>
                    <span class="value">
                        <?php 
                        $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, 
                            "SELECT SUM(total_amount) as total FROM bookings"))['total'] ?? 0;
                        echo '₹' . number_format($total_revenue, 2);
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">
                                <a href="?sort=id&order=<?php echo $sort == 'id' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                    ID
                                    <?php if($sort == 'id'): ?>
                                    <i class="fas fa-sort-<?php echo $order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                    <?php else: ?>
                                    <i class="fas fa-sort sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th width="20%">
                                <a href="?sort=name&order=<?php echo $sort == 'name' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                    Name
                                    <?php if($sort == 'name'): ?>
                                    <i class="fas fa-sort-<?php echo $order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                    <?php else: ?>
                                    <i class="fas fa-sort sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th width="20%">
                                <a href="?sort=email&order=<?php echo $sort == 'email' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                    Email
                                    <?php if($sort == 'email'): ?>
                                    <i class="fas fa-sort-<?php echo $order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                    <?php else: ?>
                                    <i class="fas fa-sort sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th width="15%">
                                <a href="?sort=phone&order=<?php echo $sort == 'phone' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                    Phone
                                    <?php if($sort == 'phone'): ?>
                                    <i class="fas fa-sort-<?php echo $order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                    <?php else: ?>
                                    <i class="fas fa-sort sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th width="10%">Bookings</th>
                            <th width="15%">Total Spent</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($buyers) > 0): ?>
                            <?php while($buyer = mysqli_fetch_assoc($buyers)): ?>
                            <tr>
                                <td><?php echo $buyer['id']; ?></td>
                                <td><?php echo htmlspecialchars($buyer['name']); ?></td>
                                <td><?php echo htmlspecialchars($buyer['email']); ?></td>
                                <td><?php echo htmlspecialchars($buyer['phone']); ?></td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo $buyer['bookings_count']; ?> bookings
                                    </span>
                                </td>
                                <td>₹<?php echo number_format($buyer['total_spent'] ?? 0, 2); ?></td>
                                <td class="actions">
                                   
                                    
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">No buyers found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <?php else: ?>
                <span class="disabled"><i class="fas fa-chevron-left"></i> Previous</span>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<a href="?page=1&sort=' . $sort . '&order=' . $order . '&search=' . urlencode($search) . '">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="disabled">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $page) {
                        echo '<span class="active">' . $i . '</span>';
                    } else {
                        echo '<a href="?page=' . $i . '&sort=' . $sort . '&order=' . $order . '&search=' . urlencode($search) . '">' . $i . '</a>';
                    }
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="disabled">...</span>';
                    }
                    echo '<a href="?page=' . $total_pages . '&sort=' . $sort . '&order=' . $order . '&search=' . urlencode($search) . '">' . $total_pages . '</a>';
                }
                ?>
                
                <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <?php else: ?>
                <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Delete Buyer</h2>
            <p>Are you sure you want to delete <strong id="buyerName"></strong>? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn" onclick="closeDeleteModal()">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="buyer_id" id="deleteBuyerId">
                    <button type="submit" name="delete_buyer" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openDeleteModal(id, name) {
            document.getElementById('deleteModal').style.display = 'flex';
            document.getElementById('deleteBuyerId').value = id;
            document.getElementById('buyerName').textContent = name;
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>