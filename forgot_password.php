<?php
// [Previous PHP code remains exactly the same...]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Gaming Store</title>
    <style>
        :root {
            --primary: #ef4444;
            --primary-dark: #dc2626;
            --secondary: #fca5a5;
            --dark: #1e293b;
            --light: #f8fafc;
            --container-bg: #f1f5f9; /* New lighter background color */
            --success: #10b981;
            --error: #b91c1c;
        }
        
        /* [All other CSS rules remain the same until .container] */
        
        .container {
            background: var(--container-bg); /* Changed to use the lighter color */
            border-radius: 15px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.2);
            position: relative;
            overflow: hidden;
            color: #1e293b; /* Darker text for better contrast on light background */
        }
        
        /* Remove the backdrop-filter as it doesn't work well with light backgrounds */
        .container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: -1;
        }
        
        /* Update label color for better visibility */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1e293b; /* Darker color for better contrast */
        }
        
        /* Update input field styling for light background */
        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(239, 68, 68, 0.3);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            color: #1e293b; /* Dark text */
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        /* [Rest of the CSS remains exactly the same] */
    </style>
</head>
<body>
     <div class="container">
        <div class="gaming-icon">🎮</div>
        <h1>Reset Your Password</h1>
        
        <?php if(isset($message)) echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Enter your email address</label>
                <input type="email" name="email" id="email" required placeholder="your@email.com">
            </div>
            
            <button type="submit" name="send_code">
                Send Verification Code
            </button>
        </form>
        
        <a href="login.php" class="back-link">Back to Login</a>
        
        <?php if(isset($debug_info)) echo $debug_info; ?>
    </div>
</body>
</html>