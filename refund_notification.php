<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['refund'])) {
    header('Location: orders.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Notification - THE GARDEN</title>
    <style>
        .notification-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f5f5f5;
        }

        .notification-box {
            text-align: center;
            max-width: 500px;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .notification-box h2 {
            color: #2a7f2a;
            margin-bottom: 20px;
        }

        .notification-box p {
            font-size: 18px;
            color: #555;
        }
    </style>
    <script>
        // Set timeout to 5 seconds (3000 milliseconds)
        setTimeout(() => {
            window.location.href = 'orders.php';
        }, 5000); // 5 seconds
    </script>
</head>

<body>
    <div class="notification-container">
        <div class="notification-box">
            <h2>Order Cancellation Successful</h2>
            <p>Your order has been successfully cancelled.</p>
            <p>Refund for UPI or Credit/Debit Card payments will be processed within 4 days.</p>
            <p>Redirecting to your orders page...</p>
        </div>
    </div>
</body>

</html>
