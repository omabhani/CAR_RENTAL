<?php 
session_start(); 
include 'db.php'; // Make sure this is included to connect to the database

if (!isset($_SESSION['seller_id'])) {     
    header("Location: seller_login.php");     
    exit; 
} 

// Process delete car request
if(isset($_POST['delete_car'])) {
    $car_id = $_POST['car_id'];
    $seller_id = $_SESSION['seller_id']; // For security, verify the car belongs to this seller
    
    // Get vehicle photo path before deleting
    $photo_query = "SELECT vehicle_photo FROM cars WHERE id = '$car_id' AND seller_id = '$seller_id'";
    $photo_result = mysqli_query($conn, $photo_query);
    
    if(mysqli_num_rows($photo_result) > 0) {
        $photo_row = mysqli_fetch_assoc($photo_result);
        $photo_path = $photo_row['vehicle_photo'];
        
        // Delete the car record from database
        $delete_query = "DELETE FROM cars WHERE id = '$car_id' AND seller_id = '$seller_id'";
        if(mysqli_query($conn, $delete_query)) {
            // If deletion was successful and photo exists, delete the photo file
            if(!empty($photo_path) && file_exists($photo_path)) {
                unlink($photo_path);
            }
            // Set success message
            $_SESSION['message'] = "Car deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            // Set error message
            $_SESSION['message'] = "Failed to delete car: " . mysqli_error($conn);
            $_SESSION['message_type'] = "error";
        }
    } else {
        // Car not found or doesn't belong to this seller
        $_SESSION['message'] = "Car not found or you don't have permission to delete it.";
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect to refresh the page
    header("Location: seller_dashboard.php");
    exit;
}

// Fetch all cars listed by this seller
$seller_id = $_SESSION['seller_id'];
$query = "SELECT * FROM cars WHERE seller_id = '$seller_id'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html> 
<html lang="en"> 
<head>   
    <meta charset="UTF-8">   
    <title>Seller Dashboard</title>   

    <style>     
        body {       
            font-family: 'Segoe UI', sans-serif;       
            background-color: #f4f4f4;       
            margin: 0;       
            padding: 0;     
        }      
        
        .container {       
            max-width: 1000px;       
            margin: 40px auto;       
            padding: 40px;       
            background: #fff;       
            border-radius: 10px;       
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);       
            text-align: center;     
        }      
        
        .container h2 {       
            font-size: 32px;       
            color: #333;       
            margin-bottom: 20px;     
        }      
        
        .container p {       
            font-size: 18px;       
            color: #666;
            margin-bottom: 30px;
        }
          
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .car-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: left;
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .car-card:hover {
            transform: translateY(-5px);
        }
        
        .car-image {
            height: 200px;
            width: 100%;
            overflow: hidden;
        }
        
        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .car-details {
            padding: 15px;
        }
        
        .car-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .car-price {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .car-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .no-cars {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        
        .add-car-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .add-car-btn:hover {
            background-color: #0056b3;
        }
        
        /* Styles for delete button */
        .car-actions {
            display: flex;
            justify-content: flex-end;
            padding: 0 15px 15px;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
        }
        
        /* Confirm delete modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 10px;
            text-align: center;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .cancel-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .confirm-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Alert message styles */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        /* Car Carousel Styles */
.car-image-carousel {
    height: 200px;
    width: 100%;
    position: relative;
    overflow: hidden;
}

.carousel-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.carousel-slides {
    width: 100%;
    height: 100%;
    position: relative;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
    z-index: 1;
}

.carousel-slide.active {
    opacity: 1;
    z-index: 2;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 35px;
    height: 35px;
    background-color: rgba(0, 0, 0, 0.3);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s;
}

.carousel-control:hover {
    background-color: rgba(0, 0, 0, 0.6);
}

.carousel-control.prev {
    left: 10px;
}

.carousel-control.next {
    right: 10px;
}

.carousel-dots {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 8px;
    z-index: 3;
}

.carousel-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: background-color 0.3s;
}

.carousel-dot.active {
    background-color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .carousel-control {
        width: 30px;
        height: 30px;
        font-size: 16px;
    }
    
    .carousel-dots {
        bottom: 5px;
    }
    
    .carousel-dot {
        width: 8px;
        height: 8px;
    }
}
:root {
  --primary-color: #4361ee;
  --secondary-color: #3a0ca3;
  --text-color: #2b2d42;
  --light-text: #ffffff;
  --background-color: #ffffff;
  --navbar-height: 70px;
  --shadow-color: rgba(0, 0, 0, 0.1);
  --transition-speed: 0.3s;
  --border-radius: 8px;
}

.navbar {
  position: sticky;
  top: 0;
  background-color: var(--background-color);
  box-shadow: 0 4px 20px var(--shadow-color);
  z-index: 1000;
  height: var(--navbar-height);
  transition: all var(--transition-speed) ease;
}

.navbar:hover {
  box-shadow: 0 6px 25px rgba(67, 97, 238, 0.15);
}

.navbar .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  padding: 0 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.logo a {
  display: flex;
  align-items: center;
  text-decoration: none;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-color);
  transition: all var(--transition-speed) ease;
}

.logo a:hover {
  transform: scale(1.05);
}

.logo i {
  margin-right: 0.5rem;
  font-size: 1.6rem;
}

#menu-toggle {
  display: none;
}

.menu-icon {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  height: 24px;
  cursor: pointer;
}

.menu-icon span {
  display: block;
  width: 30px;
  height: 3px;
  background-color: var(--primary-color);
  border-radius: 3px;
  transition: all var(--transition-speed) ease;
}

.nav-menu {
  display: flex;
  align-items: center;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-menu li {
  position: relative;
  margin: 0 0.25rem;
}

.nav-menu li a {
  display: block;
  padding: 0.5rem 1rem;
  text-decoration: none;
  color: var(--text-color);
  font-weight: 500;
  font-size: 1rem;
  border-radius: var(--border-radius);
  transition: all var(--transition-speed) ease;
}

.nav-menu li a:hover {
  color: var(--primary-color);
  background-color: rgba(67, 97, 238, 0.08);
}

.nav-menu li a.active {
  color: var(--light-text);
  background-color: var(--primary-color);
}

.nav-menu li a.active:hover {
  background-color: var(--secondary-color);
}

/* Animated underline effect for non-active links */
.nav-menu li a:not(.active)::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 50%;
  background-color: var(--primary-color);
  transition: all var(--transition-speed) ease;
}

.nav-menu li a:not(.active):hover::after {
  width: 80%;
  left: 10%;
}

/* Responsive styles */
@media screen and (max-width: 900px) {
  .menu-icon {
    display: flex;
    z-index: 1010;
  }
  
  .nav-menu {
    position: fixed;
    top: var(--navbar-height);
    left: -100%;
    width: 100%;
    height: calc(100vh - var(--navbar-height));
    flex-direction: column;
    background-color: var(--background-color);
    box-shadow: 0 10px 20px var(--shadow-color);
    transition: all var(--transition-speed) ease;
    align-items: flex-start;
    padding: 1rem 0;
    overflow-y: auto;
  }
  
  .nav-menu li {
    width: 100%;
    margin: 0;
  }
  
  .nav-menu li a {
    padding: 1rem 2rem;
    border-radius: 0;
  }
  
  #menu-toggle:checked ~ .nav-menu {
    left: 0;
  }
  
  #menu-toggle:checked ~ .menu-icon span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 6px);
  }
  
  #menu-toggle:checked ~ .menu-icon span:nth-child(2) {
    opacity: 0;
  }
  
  #menu-toggle:checked ~ .menu-icon span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -6px);
  }
  
  .nav-menu li a:not(.active)::after {
    display: none;
  }
}

/* Animation for menu items when mobile menu opens */
@media screen and (max-width: 900px) {
  .nav-menu li {
    opacity: 0;
    transform: translateX(-20px);
    transition: all var(--transition-speed) ease;
  }
  
  #menu-toggle:checked ~ .nav-menu li {
    opacity: 1;
    transform: translateX(0);
  }
  
  /* Staggered animation for menu items */
  .nav-menu li:nth-child(1) { transition-delay: 0.1s; }
  .nav-menu li:nth-child(2) { transition-delay: 0.2s; }
  .nav-menu li:nth-child(3) { transition-delay: 0.3s; }
  .nav-menu li:nth-child(4) { transition-delay: 0.4s; }
  .nav-menu li:nth-child(5) { transition-delay: 0.5s; }
}
    </style> 
</head> 
<body>    
    <!-- Keep your existing navbar -->   
    <nav class="navbar">
  <div class="container">
    <div class="logo">
      <a href="seller_dashboard.php">
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
      <li><a href="seller_dashboard.php">Home</a></li>
      <li><a href="seller_profile.php">Profile</a></li>
      <li><a href="list_car.php">List Car</a></li>
      <li><a href="seller_booking_requests.php">Booking Request</a></li>
      <li><a href="index.php">Logout</a></li>
    </ul>
  </div>
</nav> 
    
    <!-- Styled container -->   
    <div class="container">     
        <h2>Welcome, <?php echo $_SESSION['seller_name']; ?>!</h2>     
        <p>Manage your listed cars or add new ones for rent.</p>
        
        <!-- Display messages if any -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); 
                    unset($_SESSION['message_type']); 
                ?>
            </div>
        <?php endif; ?>
        
        <a href="list_car.php" class="add-car-btn">+ Add New Car</a>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="cars-grid">
            <?php while($car = mysqli_fetch_assoc($result)): ?>
    <div class="car-card">
        <div class="car-image-carousel">
            <?php 
            // Decode the JSON array of photo paths
            $photo_paths = json_decode($car['vehicle_photo'], true); // Changed from vehicle_photo to vehicle_photos
            
            // If no photos or not an array, initialize empty array
            if (empty($photo_paths) || !is_array($photo_paths)) {
                $photo_paths = [];
            }
            
            // If we have photos
            if (!empty($photo_paths)): 
            ?>
                <div class="carousel-container">
                    <div class="carousel-slides">
                        <?php foreach($photo_paths as $index => $path): ?>
                            <?php if(file_exists($path)): ?>
                                <div class="carousel-slide <?php echo ($index === 0) ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                    <img src="<?php echo $path; ?>" alt="<?php echo $car['vehicle_name'] . ' photo ' . ($index + 1); ?>">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(count($photo_paths) > 1): ?>
                    <button class="carousel-control prev" onclick="moveSlide(-1, this.closest('.car-card'))">❮</button>
                    <button class="carousel-control next" onclick="moveSlide(1, this.closest('.car-card'))">❯</button>
                    
                    <div class="carousel-dots">
                        <?php for($i = 0; $i < count($photo_paths); $i++): ?>
                            <span class="carousel-dot <?php echo ($i === 0) ? 'active' : ''; ?>" 
                                  onclick="setSlide(<?php echo $i; ?>, this.closest('.car-card'))"></span>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <img src="images/car-placeholder.jpg" alt="No image available">
            <?php endif; ?>
        </div>
        
        <div class="car-details">
            <div class="car-name"><?php echo $car['vehicle_name']; ?></div>
            <div class="car-price">₹<?php echo $car['price']; ?> per day</div>
            <div class="car-info"><strong>Owner:</strong> <?php echo $car['owner_name']; ?></div>
            <div class="car-info"><strong>Vehicle No:</strong> <?php echo $car['vehicle_no']; ?></div>
            <div class="car-info"><strong>Contact:</strong> <?php echo $car['phone']; ?></div>
            <div class="car-info"><strong>Email:</strong> <?php echo $car['email']; ?></div>
        </div>
        
        <div class="car-actions">
            <button class="delete-btn" onclick="confirmDelete(<?php echo $car['id']; ?>, '<?php echo $car['vehicle_name']; ?>')">Delete</button>
        </div>
    </div>
<?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-cars">
                <p>You haven't listed any cars yet.</p>
                <p>Get started by clicking the "Add New Car" button above!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">Confirm Deletion</div>
            <p>Are you sure you want to delete <span id="carName"></span>?</p>
          
            <div class="modal-buttons">
                <button class="cancel-btn" onclick="closeModal()">Cancel</button>
                <form id="deleteForm" method="post" action="">
                    <input type="hidden" name="car_id" id="carIdInput">
                    <input type="hidden" name="delete_car" value="1">
                    <button type="submit" class="confirm-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Function to show delete confirmation modal
        function confirmDelete(carId, carName) {
            document.getElementById('carName').textContent = carName;
            document.getElementById('carIdInput').value = carId;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        // Function to close the modal
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        // Add this to your existing script section
function moveSlide(direction, carCard) {
    const slides = carCard.querySelectorAll('.carousel-slide');
    const dots = carCard.querySelectorAll('.carousel-dot');
    
    // Find current active slide
    let activeIndex = 0;
    slides.forEach((slide, index) => {
        if (slide.classList.contains('active')) {
            activeIndex = index;
        }
    });
    
    // Remove active class from current slide and dot
    slides[activeIndex].classList.remove('active');
    if (dots.length > 0) {
        dots[activeIndex].classList.remove('active');
    }
    
    // Calculate new index
    let newIndex = activeIndex + direction;
    if (newIndex < 0) newIndex = slides.length - 1;
    if (newIndex >= slides.length) newIndex = 0;
    
    // Add active class to new slide and dot
    slides[newIndex].classList.add('active');
    if (dots.length > 0) {
        dots[newIndex].classList.add('active');
    }
}

function setSlide(index, carCard) {
    const slides = carCard.querySelectorAll('.carousel-slide');
    const dots = carCard.querySelectorAll('.carousel-dot');
    
    // Remove active class from all slides and dots
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    // Add active class to selected slide and dot
    slides[index].classList.add('active');
    dots[index].classList.add('active');
}

// Auto-rotate carousel every 5 seconds for each car
document.addEventListener('DOMContentLoaded', function() {
    const carCards = document.querySelectorAll('.car-card');
    
    carCards.forEach(card => {
        const slides = card.querySelectorAll('.carousel-slide');
        if (slides.length > 1) {
            // Set interval for this specific card
            setInterval(() => {
                moveSlide(1, card);
            }, 5000);
        }
    });
});
    </script>
</body> 
</html>