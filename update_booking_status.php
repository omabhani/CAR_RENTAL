<?php 
session_start(); 
include 'db.php';  

// Check login 
if (!isset($_SESSION['seller_id'])) {     
    echo "<script>alert('Session expired or not logged in. Please login again.'); window.location.href='seller_login.php';</script>";     
    exit; 
}  

$seller_id = $_SESSION['seller_id'];  

if (isset($_GET['booking_id']) && isset($_GET['status'])) {     
    $booking_id = $_GET['booking_id'];     
    $status = $_GET['status'];      
    
    // Map the status to the valid enum values
    $db_status = '';
    if ($status === 'accepted') {
        $db_status = 'confirmed';  // Use 'confirmed' instead of 'accepted'
    } elseif ($status === 'rejected') {
        $db_status = 'cancelled';  // Use 'cancelled' instead of 'rejected'
    } else {
        echo "<script>alert('Invalid status value.'); window.location.href='seller_booking_requests.php';</script>";
        exit;
    }
    
    // Debug: Print the values being used
    // echo "Status: $status, DB Status: $db_status, Booking ID: $booking_id, Seller ID: $seller_id";
    
    // Update the booking status using the mapped value
    $query = "UPDATE bookings SET booking_status = ? WHERE booking_id = ? AND car_id IN (SELECT id FROM cars WHERE seller_id = ?)";
    $stmt = $conn->prepare($query);
         
    if (!$stmt) {
        echo "<script>alert('Error in query preparation: " . $conn->error . "'); window.location.href='seller_booking_requests.php';</script>";
        exit;
    }
    
    $stmt->bind_param("sii", $db_status, $booking_id, $seller_id);
    
    if ($stmt->execute()) {
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Booking status updated successfully.'); window.location.href='seller_booking_requests.php';</script>";
        } else {
            echo "<script>alert('No changes made. Either the booking does not exist or you do not have permission to update it.'); window.location.href='seller_booking_requests.php';</script>";
        }
    } else {
        echo "<script>alert('Error updating booking status: " . $stmt->error . "'); window.location.href='seller_booking_requests.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='seller_booking_requests.php';</script>";
}
?>