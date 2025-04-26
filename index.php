
<?php
session_start();
include 'db.php'; // Make sure to create this file with your database connection

// Update the SQL query to match your actual table structure
$query = "SELECT * FROM cars";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Car Rental | Home</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Base Styles */
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3a0ca3;
      --accent-color: #f72585;
      --dark-color: #1a202c;
      --light-color: #f7fafc;
      --gray-color: #718096;
      --light-gray: #e2e8f0;
      --border-radius: 10px;
      --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: var(--dark-color);
      background-color: #f8f9fa;
    }

    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul {
      list-style: none;
    }

    img {
      max-width: 100%;
      height: auto;
    }

    /* Button Styles */
    .btn {
      display: inline-block;
      padding: 12px 24px;
      border-radius: var(--border-radius);
      font-weight: 600;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      border: none;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background-color: #3051d3;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background-color: white;
      color: var(--primary-color);
      border: 1px solid var(--primary-color);
    }

    .btn-secondary:hover {
      background-color: #f0f5ff;
      transform: translateY(-2px);
    }

    .btn-danger {
      background-color: #e53e3e;
      color: white;
    }

    .btn-danger:hover {
      background-color: #c53030;
    }

    .btn-success {
      background-color: #38b653;
      color: white;
    }

    .btn-success:hover {
      background-color: #2fa046;
      transform: translateY(-2px);
    }

    

    /* Hero Section */
    .hero {
      background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url("images/hero-bg.jpg");
      background-size: cover;
      background-position: center;
      color: white;
      padding: 120px 0;
      text-align: center;
    }

    .hero-content {
      max-width: 800px;
      margin: 0 auto;
    }

    .hero h1 {
      font-size: 48px;
      margin-bottom: 20px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero p {
      font-size: 20px;
      margin-bottom: 30px;
      opacity: 0.9;
    }

    .search-bar {
      display: flex;
      max-width: 600px;
      margin: 0 auto 30px;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }

    .search-bar input {
      flex: 1;
      padding: 15px 20px;
      border: none;
      font-size: 16px;
    }

    .search-bar button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 0 25px;
      cursor: pointer;
      transition: var(--transition);
    }

    .search-bar button:hover {
      background-color: #3051d3;
    }

    .hero-cta {
      display: flex;
      gap: 15px;
      justify-content: center;
    }

    /* Cars Section */
    .cars-section {
      padding: 80px 0;
    }

    .section-title {
      text-align: center;
      font-size: 32px;
      margin-bottom: 40px;
      position: relative;
    }

    .section-title::after {
      content: "";
      display: block;
      width: 80px;
      height: 4px;
      background-color: var(--primary-color);
      margin: 15px auto 0;
      border-radius: 2px;
    }

    .filters {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 30px;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .filter-group label {
      font-weight: 500;
    }

    .filter-group select {
      padding: 10px 15px;
      border-radius: var(--border-radius);
      border: 1px solid var(--light-gray);
      background-color: white;
      font-size: 14px;
      cursor: pointer;
    }

    .cars-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 30px;
    }

    .car-card {
      background-color: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
    }

    .car-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .car-image-carousel {
      position: relative;
      height: 220px;
      overflow: hidden;
    }

    .carousel-container {
      position: relative;
      width: 100%;
      height: 100%;
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
    }

    .carousel-slide.active {
      opacity: 1;
    }

    .carousel-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .placeholder-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .carousel-control {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 18px;
      cursor: pointer;
      z-index: 2;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .carousel-control:hover {
      background-color: rgba(0, 0, 0, 0.8);
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
    }

    .carousel-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      transition: var(--transition);
    }

    .carousel-dot.active {
      background-color: white;
    }

    .car-details {
      padding: 20px;
    }

    .car-name {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--dark-color);
    }

    .car-price {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 15px;
    }

    .car-price span {
      font-size: 14px;
      font-weight: 400;
      color: var(--gray-color);
    }

    .car-specs {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--light-gray);
    }

    .spec-item {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
      color: var(--gray-color);
    }

    .car-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 20px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .info-label {
      font-size: 12px;
      color: var(--gray-color);
    }

    .info-value {
      font-size: 14px;
      font-weight: 500;
    }

    .car-actions {
      padding: 0 20px 20px;
    }

    .car-actions .btn {
      width: 100%;
    }

    .no-cars {
      text-align: center;
      padding: 50px 0;
      color: var(--gray-color);
    }

    .no-cars i {
      font-size: 60px;
      margin-bottom: 20px;
    }

    /* Modern Booking Modal Styles */
    .booking-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      z-index: 1001;
      justify-content: center;
      align-items: center;
      overflow-y: auto;
    }

    .modal-content {
      background-color: white;
      border-radius: 16px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 25px;
      border-bottom: 1px solid #eaeaea;
    }

    .modal-header h3 {
      margin: 0;
      color: var(--dark-color);
      font-weight: 600;
      font-size: 1.5rem;
    }

    .close-modal {
      background: none;
      border: none;
      cursor: pointer;
      color: #888;
      transition: color 0.2s;
      font-size: 24px;
    }

    .close-modal:hover {
      color: #333;
    }

    .form-section {
      padding: 20px 25px;
      border-bottom: 1px solid #f0f0f0;
    }

    .form-section h4 {
      margin-top: 0;
      margin-bottom: 20px;
      color: #444;
      font-weight: 500;
      font-size: 1.2rem;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-row {
      display: flex;
      gap: 15px;
    }

    .form-row .form-group {
      flex: 1;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #555;
      font-size: 14px;
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="date"],
    input[type="file"] {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.2s;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="tel"]:focus,
    input[type="date"]:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .file-upload input[type="file"] {
      position: absolute;
      width: 0.1px;
      height: 0.1px;
      opacity: 0;
      overflow: hidden;
      z-index: -1;
    }

    .file-upload-label {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      background-color: #f8f9fa;
      border: 1px dashed #ddd;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .file-upload-label:hover {
      background-color: #eef1f8;
      border-color: var(--primary-color);
    }

    .file-upload-label i {
      color: var(--primary-color);
    }

    .payment-section {
      background-color: #fafbff;
    }

    .price-info {
      background-color: #f0f4ff;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .price-info p {
      margin: 0;
      display: flex;
      justify-content: space-between;
      font-weight: 500;
    }

    .price-info span {
      font-weight: 600;
      color: var(--primary-color);
    }

    .form-actions {
      padding: 20px 25px;
      display: flex;
      justify-content: flex-end;
      gap: 15px;
    }

    /* Footer Styles */
    footer {
      background-color: var(--dark-color);
      color: white;
      padding: 60px 0 20px;
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }

    .footer-section h3 {
      font-size: 20px;
      margin-bottom: 20px;
      position: relative;
    }

    .footer-section h3::after {
      content: "";
      display: block;
      width: 50px;
      height: 3px;
      background-color: var(--primary-color);
      margin-top: 10px;
    }

    .footer-section p {
      margin-bottom: 15px;
      opacity: 0.8;
    }

    .social-icons {
      display: flex;
      gap: 15px;
    }

    .social-icons a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      transition: var(--transition);
    }

    .social-icons a:hover {
      background-color: var(--primary-color);
      transform: translateY(-3px);
    }

    .footer-section ul li {
      margin-bottom: 10px;
    }

    .footer-section ul li a {
      opacity: 0.8;
      transition: var(--transition);
    }

    .footer-section ul li a:hover {
      opacity: 1;
      color: var(--primary-color);
      padding-left: 5px;
    }

    .footer-section i {
      margin-right: 10px;
      color: var(--primary-color);
    }

    .footer-bottom {
      text-align: center;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
      .cars-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      }
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 36px;
      }

      .hero p {
        font-size: 18px;
      }

      .menu-icon {
        display: block;
      }

      .nav-menu {
        position: fixed;
        top: 70px;
        left: -100%;
        flex-direction: column;
        background-color: white;
        width: 100%;
        padding: 20px;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
      }

      #menu-toggle:checked ~ .nav-menu {
        left: 0;
      }

      .cars-grid {
        grid-template-columns: 1fr;
      }

      .form-row {
        flex-direction: column;
        gap: 10px;
      }
    }

    @media (max-width: 576px) {
      .hero {
        padding: 70px 0;
      }

      .hero h1 {
        font-size: 28px;
      }

      .hero-cta {
        flex-direction: column;
      }

      .modal-content {
        margin: 20px;
        padding: 0;
        width: calc(100% - 40px);
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
  <header>
    <!-- index.php navbar -->
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
      <li><a href="index.php" class="active">Home</a></li>
      <?php if (!isset($_SESSION['buyer_logged_in']) && !isset($_SESSION['seller_logged_in'])): ?>
        <li><a href="buyer_login.php">Buyer Login</a></li>
        <li><a href="seller_login.php">Seller Login</a></li>
      <?php endif; ?>
      <?php if (isset($_SESSION['buyer_logged_in'])): ?>
        <li><a href="buyer_profile.php">Profile</a></li>
        <li><a href="your_bookings.php">Your Bookings</a></li>
      <?php endif; ?>
      <?php if (isset($_SESSION['seller_logged_in'])): ?>
        <li><a href="seller_profile.php">Seller Dashboard</a></li>
      <?php endif; ?>
      <?php if (isset($_SESSION['buyer_logged_in']) || isset($_SESSION['seller_logged_in'])): ?>
        <li><a href="logout.php">Logout</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
  </header>

  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1>Find Your Perfect Ride</h1>
        <p>Explore our wide range of vehicles for any occasion</p>
        <!-- <div class="search-bar">
          <input type="text" id="search-input" placeholder="Search by car name, model...">
          <button id="search-btn"><i class="fas fa-search"></i></button>
        </div> -->
        <div class="hero-cta">
          <a href="#cars-section" class="btn btn-primary">Browse Cars</a>
          <?php if (!isset($_SESSION['buyer_logged_in']) && !isset($_SESSION['seller_logged_in'])): ?>
            <a href="buyer_login.php" class="btn btn-secondary">Sign In</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section id="cars-section" class="cars-section">
    <div class="container">
      <h2 class="section-title">Available Cars</h2>
      <div class="filters">
        <div class="filter-group">
          <!-- <label for="sort-by">Sort by:</label> -->
          <!-- <select id="sort-by">
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
            <option value="name-asc">Name: A to Z</option>
            <option value="name-desc">Name: Z to A</option>
          </select> -->
        </div>
      </div>

      <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="cars-grid">
          <?php while($car = mysqli_fetch_assoc($result)): ?>
            <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
              <div class="car-image-carousel">
                <?php 
                $photo_paths = json_decode($car['vehicle_photo'], true);

                // Handle fallback if decoding fails
                if (empty($photo_paths) || !is_array($photo_paths)) {
                    $photo_paths = [];
                }

                if (!empty($photo_paths)): ?>
                  <div class="carousel-container">
                    <div class="carousel-slides">
                      <?php foreach($photo_paths as $index => $path): 
                        // Correct the relative path, assuming images are stored in 'uploads/' folder.
                        $relative_path = $path; // The path already includes 'uploads/'
                        
                        // Check if the file exists on the server
                        if (file_exists($relative_path)): ?>
                          <div class="carousel-slide <?php echo ($index === 0) ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                            <img src="<?php echo $relative_path; ?>" alt="Car image <?php echo $index + 1; ?>">
                          </div>
                        <?php else: ?>
                          <div class="carousel-slide <?php echo ($index === 0) ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                            <img src="images/car-placeholder.jpg" alt="Image not available">
                          </div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>

                    <?php if(count($photo_paths) > 1): ?>
                      <button class="carousel-control prev" onclick="moveSlide(-1, this.closest('.car-card'))">
                        <i class="fas fa-chevron-left"></i>
                      </button>
                      <button class="carousel-control next" onclick="moveSlide(1, this.closest('.car-card'))">
                        <i class="fas fa-chevron-right"></i>
                      </button>

                      <div class="carousel-dots">
                        <?php for($i = 0; $i < count($photo_paths); $i++): ?>
                          <span class="carousel-dot <?php echo ($i === 0) ? 'active' : ''; ?>" 
                                onclick="setSlide(<?php echo $i; ?>, this.closest('.car-card'))"></span>
                        <?php endfor; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <img src="images/car-placeholder.jpg" alt="No image available" class="placeholder-img">
                <?php endif; ?>
              </div>
              
              <div class="car-details">
                <h3 class="car-name"><?php echo $car['vehicle_name']; ?></h3>
                <div class="car-price">₹<?php echo number_format($car['price']); ?> <span>per day</span></div>
                
                <div class="car-specs">

                </div>
                
                <div class="car-info-grid">
                  <div class="info-item">
                    <span class="info-label">Owner:</span>
                    <span class="info-value"><?php echo $car['owner_name']; ?></span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Vehicle No:</span>
                    <span class="info-value"><?php echo $car['vehicle_no']; ?></span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Contact:</span>
                    <span class="info-value"><?php echo $car['phone']; ?></span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo $car['email']; ?></span>
                  </div>
                </div>
              </div>
              
              <div class="car-actions">
                <?php if (isset($_SESSION['buyer_logged_in'])): ?>
                  <button class="btn btn-primary book-btn" data-car-id="<?php echo $car['id']; ?>">Book Now</button>
                <?php elseif (isset($_SESSION['seller_logged_in']) && $_SESSION['user_id'] == $car['seller_id']): ?>
                  <button class="btn btn-danger delete-btn"  == $car['seller_id']): ?>
                  <button class="btn btn-danger delete-btn" onclick="confirmDelete(<?php echo $car['id']; ?>, '<?php echo $car['vehicle_name']; ?>')">Delete</button>
                <?php else: ?>
                  <a href="buyer_login.php" class="btn btn-secondary">Login to Book</a>
                <?php endif; ?>
              </div>
            </div>

            <!-- Booking Modal for each car -->
            <div class="booking-modal" id="booking-modal-<?php echo $car['id']; ?>">
              <div class="modal-content">
                <div class="modal-header">
                  <h3>Book Your Ride</h3>
                  <button class="close-modal" onclick="closeModal(<?php echo $car['id']; ?>)">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                
                <form action="process_booking.php" method="POST" enctype="multipart/form-data" id="booking-form-<?php echo $car['id']; ?>">
                  <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                  <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
                  <input type="hidden" name="price_per_day" value="<?php echo $car['price']; ?>">
                  
                  <div class="form-section">
                    <h4>Personal Details</h4>
                    <div class="form-group">
                      <label for="name-<?php echo $car['id']; ?>">Full Name</label>
                      <input type="text" id="name-<?php echo $car['id']; ?>" name="name" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-row">
                      <div class="form-group">
                        <label for="phone-<?php echo $car['id']; ?>">Phone Number</label>
                        <input type="tel" id="phone-<?php echo $car['id']; ?>" name="phone" placeholder="Your contact number" required>
                      </div>
                      <div class="form-group">
                        <label for="email-<?php echo $car['id']; ?>">Email Address</label>
                        <input type="email" id="email-<?php echo $car['id']; ?>" name="email" placeholder="Your email address" required>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-section">
                    <h4>Rental Period</h4>
                    <div class="form-row">
                      <div class="form-group">
                        <label for="start_date-<?php echo $car['id']; ?>">Pick-up Date</label>
                        <input type="date" id="start_date-<?php echo $car['id']; ?>" name="start_date" required onchange="calculateTotal(<?php echo $car['id']; ?>)">
                      </div>
                      <div class="form-group">
                        <label for="end_date-<?php echo $car['id']; ?>">Return Date</label>
                        <input type="date" id="end_date-<?php echo $car['id']; ?>" name="end_date" required onchange="calculateTotal(<?php echo $car['id']; ?>)">
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-section">
                    <h4>Verification</h4>
                    <div class="form-group">
                      <label for="aadhar-<?php echo $car['id']; ?>">Aadhar Number</label>
                      <input type="text" id="aadhar-<?php echo $car['id']; ?>" name="aadhar_number" placeholder="12-digit Aadhar number" required>
                    </div>
                    <div class="form-group file-upload">
                      <label for="license-<?php echo $car['id']; ?>">Driver's License</label>
                      <input type="file" id="license-<?php echo $car['id']; ?>" name="license_photo" accept="image/*" required>
                      <label for="license-<?php echo $car['id']; ?>" class="file-upload-label">
                        <i class="fas fa-upload"></i>
                        <span>Upload License Photo</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="form-section payment-section">
                    <h4>Payment Details</h4>
                    <div class="price-info">
                    <input type="hidden" name="price_per_day" value="<?php echo $car['price']; ?>">

                      <p>Price per day: <span>₹<?php echo number_format($car['price']); ?></span></p>
                      <p>Total days: <span id="total-days-<?php echo $car['id']; ?>">0</span></p>
                      <p>Total amount: <span id="total-amount-<?php echo $car['id']; ?>">₹0</span></p>
                    </div>
                    <div class="form-group">
                      <label for="card_name-<?php echo $car['id']; ?>">Cardholder Name</label>
                      <input type="text" id="card_name-<?php echo $car['id']; ?>" name="card_name" placeholder="Name on card" required>
                    </div>
                    <div class="form-group">
                      <label for="card_number-<?php echo $car['id']; ?>">Card Number</label>
                      <input type="text" id="card_number-<?php echo $car['id']; ?>" name="card_number" maxlength="16" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                    </div>
                    <div class="form-row">
                      <div class="form-group">
                        <label for="expiry-<?php echo $car['id']; ?>">Expiry Date</label>
                        <input type="text" id="expiry-<?php echo $car['id']; ?>" name="card_expiry" placeholder="MM/YY" required>
                      </div>
                      <div class="form-group">
                        <label for="cvv-<?php echo $car['id']; ?>">CVV</label>
                        <input type="text" id="cvv-<?php echo $car['id']; ?>" name="card_cvv" maxlength="3" placeholder="XXX" required>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal(<?php echo $car['id']; ?>)">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm & Pay Now</button>
                  </div>
                </form>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="no-cars">
          <i class="fas fa-car-side"></i>
          <h3>No cars available at the moment</h3>
          <p>Please check back later or contact us for more information.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>CarRental</h3>
          <p>Find the perfect car for your journey with our wide selection of rental vehicles.</p>
          <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        
       
        
        <div class="footer-section">
          <h3>Contact Us</h3>
          <p><i class="fas fa-map-marker-alt"></i> 123 Car Street, Gandhinagar</p>
          <p><i class="fas fa-phone"></i> +91 1234567890</p>
          <p><i class="fas fa-envelope"></i> info@carrental.com</p>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> CarRental. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Show modal when book now button is clicked
      const bookBtns = document.querySelectorAll('.book-btn');
      
      bookBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const carId = this.getAttribute('data-car-id');
          document.getElementById(`booking-modal-${carId}`).style.display = 'flex';
        });
      });
      
      // File upload label update
      const fileInputs = document.querySelectorAll('input[type="file"]');
      
      fileInputs.forEach(input => {
        input.addEventListener('change', function() {
          if (this.files.length > 0) {
            const label = this.nextElementSibling;
            const span = label.querySelector('span');
            span.textContent = this.files[0].name;
          }
        });
      });

      // Initialize carousels
      initCarousels();

      // Set minimum date for date inputs to today
      const today = new Date().toISOString().split('T')[0];
      document.querySelectorAll('input[type="date"]').forEach(input => {
        input.min = today;
      });

      // Format card inputs
      setupCardInputs();
    });

    // Close modal function
    function closeModal(carId) {
      document.getElementById(`booking-modal-${carId}`).style.display = 'none';
    }

    // Calculate total price
    function calculateTotal(carId) {
  const startDate = document.getElementById(`start_date-${carId}`).value;
  const endDate = document.getElementById(`end_date-${carId}`).value;

  const basePricePerDay = parseFloat(document.querySelector(`input[name="price_per_day"][value]`).value);
  const extraChargePerDay = 100; // Added per day extra charge
  const pricePerDay = basePricePerDay + extraChargePerDay;

  if (startDate && endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Validate end date is after start date
    if (end <= start) {
      alert('Return date must be after pick-up date');
      document.getElementById(`end_date-${carId}`).value = '';
      return;
    }

    // Calculate days difference
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    // Update UI
    document.getElementById(`total-days-${carId}`).textContent = diffDays;
    document.getElementById(`total-amount-${carId}`).textContent = `₹${(diffDays * pricePerDay).toLocaleString()}`;
  }
}

    // Carousel Functions
    function initCarousels() {
      // Auto-rotate carousels every 5 seconds
      const carCards = document.querySelectorAll(".car-card");

      carCards.forEach((card) => {
        const slides = card.querySelectorAll(".carousel-slide");
        if (slides.length > 1) {
          let currentIndex = 0;
          setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            setSlide(currentIndex, card);
          }, 5000);
        }
      });
    }

    function moveSlide(direction, carCard) {
      const slides = carCard.querySelectorAll(".carousel-slide");
      const dots = carCard.querySelectorAll(".carousel-dot");

      if (slides.length === 0) return;

      let currentIndex = 0;
      slides.forEach((slide, index) => {
        if (slide.classList.contains("active")) {
          currentIndex = index;
          slide.classList.remove("active");
          if (dots[index]) dots[index].classList.remove("active");
        }
      });

      const newIndex = (currentIndex + direction + slides.length) % slides.length;
      slides[newIndex].classList.add("active");
      if (dots[newIndex]) dots[newIndex].classList.add("active");
    }

    function setSlide(index, carCard) {
      const slides = carCard.querySelectorAll(".carousel-slide");
      const dots = carCard.querySelectorAll(".carousel-dot");

      slides.forEach((slide, i) => {
        slide.classList.remove("active");
        if (dots[i]) dots[i].classList.remove("active");
      });

      slides[index].classList.add("active");
      if (dots[index]) dots[index].classList.add("active");
    }

    // Format card inputs
    function setupCardInputs() {
      // Format card number with spaces
      document.querySelectorAll('input[name="card_number"]').forEach(input => {
        input.addEventListener('input', function(e) {
          let value = this.value.replace(/\D/g, '');
          if (value.length > 16) value = value.slice(0, 16);
          
          // Format with spaces
          let formattedValue = '';
          for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formattedValue += '-';
            formattedValue += value[i];
          }
          
          this.value = formattedValue;
        });
      });
      
      // Format expiry date
      document.querySelectorAll('input[name="card_expiry"]').forEach(input => {
        input.addEventListener('input', function(e) {
          let value = this.value.replace(/\D/g, '');
          if (value.length > 4) value = value.slice(0, 4);
          
          if (value.length > 2) {
            this.value = value.slice(0, 2) + '/' + value.slice(2);
          } else {
            this.value = value;
          }
        });
      });
    }

    // Delete car function
    function confirmDelete(carId, carName) {
      if (confirm(`Are you sure you want to delete ${carName}?`)) {
        window.location.href = `delete_car.php?id=${carId}`;
      }
    }

    // Search functionality
    document.getElementById('search-btn').addEventListener('click', function() {
      const searchTerm = document.getElementById('search-input').value.toLowerCase();
      const carCards = document.querySelectorAll('.car-card');
      
      carCards.forEach(card => {
        const carName = card.querySelector('.car-name').textContent.toLowerCase();
        if (carName.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });

    // Sort functionality
    document.getElementById('sort-by').addEventListener('change', function() {
      const sortValue = this.value;
      const carGrid = document.querySelector('.cars-grid');
      const carCards = Array.from(document.querySelectorAll('.car-card'));
      
      carCards.sort((a, b) => {
        const aName = a.querySelector('.car-name').textContent;
        const bName = b.querySelector('.car-name').textContent;
        const aPrice = parseFloat(a.querySelector('.car-price').textContent.replace(/[^0-9.]/g, ''));
        const bPrice = parseFloat(b.querySelector('.car-price').textContent.replace(/[^0-9.]/g, ''));
        
        if (sortValue === 'price-asc') {
          return aPrice - bPrice;
        } else if (sortValue === 'price-desc') {
          return bPrice - aPrice;
        } else if (sortValue === 'name-asc') {
          return aName.localeCompare(bName);
        } else if (sortValue === 'name-desc') {
          return bName.localeCompare(aName);
        }
      });
      
      // Remove all current cards
      while (carGrid.firstChild) {
        carGrid.removeChild(carGrid.firstChild);
      }
      
      // Add sorted cards
      carCards.forEach(card => {
        carGrid.appendChild(card);
      });
    });
  </script>
</body>
</html>
