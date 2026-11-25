<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .redirect-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            text-align: center;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .spinner-border {
            color: #667eea;
            width: 50px;
            height: 50px;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .welcome-text {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .username {
            color: #667eea;
            font-weight: 600;
        }

        .message {
            color: #888;
            font-size: 13px;
            margin-top: 20px;
        }

        .fallback-link {
            margin-top: 20px;
        }

        .fallback-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .fallback-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        
        <h2>Welcome!</h2>
        <div class="welcome-text">
            Hello, <span class="username"><?= esc(session()->get('username')) ?></span>
        </div>

        <p class="message">
            Redirecting you to the <?= $role === 'admin' ? 'Admin Dashboard' : 'Point of Sale' ?>...
        </p>

        <div class="fallback-link">
            <p>If you are not redirected automatically,</p>
            <a href="<?= $redirect_to ?>">click here</a>
        </div>
    </div>

    <script>
        // Redirect after 2 seconds
        setTimeout(function() {
            window.location.href = '<?= $redirect_to ?>';
        }, 2000);
    </script>
</body>
</html>
