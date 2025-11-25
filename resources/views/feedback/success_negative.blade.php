<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You for Your Feedback</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            color: #ff9800;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .google-review-button {
            display: inline-block;
            background: #f5f5f5;
            color: #333;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .google-review-button:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">📝</div>
        <h1>Thank You for Your Feedback</h1>
        <p class="message">
            Thanks — a manager will review this and follow up if requested. You can still share your experience on Google.
        </p>
        @if($googleReviewUrl)
            <a href="{{ $googleReviewUrl }}" target="_blank" class="google-review-button">
                Leave a Google Review
            </a>
        @endif
        <div class="footer-note">
            All customers may leave public reviews; private feedback helps us resolve issues faster.
        </div>
    </div>
</body>
</html>

