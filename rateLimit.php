<?php
// Start the session to store rate limit data
session_start();

// Define the rate limit settings
$limit = 5; // Maximum number of requests allowed
$window = 60; // Time window in seconds (e.g., 60 seconds)

// Get the client's IP address
$ip = $_SERVER['REMOTE_ADDR'];

// Get the current timestamp
$now = time();

// Check if the rate limit data is already stored in the session
if (isset($_SESSION['rate_limit'][$ip])) {
    // Retrieve the stored rate limit data
    $data = $_SESSION['rate_limit'][$ip];

    // Check if the time window has elapsed
    if ($now - $data['timestamp'] > $window) {
        // Reset the rate limit data for a new time window
        $_SESSION['rate_limit'][$ip] = ['count' => 1, 'timestamp' => $now];
    } else {
        // Increment the request count
        $data['count']++;

        // Check if the request count exceeds the limit
        if ($data['count'] > $limit) {
            // Return an error response or take appropriate action
            http_response_code(429); // 429 Too Many Requests
            // exit("Rate limit exceeded. Please try again later");
            exit('<script>alert("Rate limit exceeded. Please try again later")</script>');
            
            
        } else {
            // Update the rate limit data
            $_SESSION['rate_limit'][$ip] = $data;
        }
    }
} else {
    // Initialize the rate limit data for the client's IP address
    $_SESSION['rate_limit'][$ip] = ['count' => 1, 'timestamp' => $now];
}

// Continue processing the request
// echo "Request processed successfully";

?>