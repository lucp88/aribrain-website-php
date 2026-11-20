<?php
// Shown while a deploy runs database migrations (flag file: /.maintenance).
http_response_code(503);
header('Retry-After: 120');
header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Maintenance - ARIbrain</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
               font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
               background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); color: #1a1a2e; box-sizing: border-box; }
        .box { max-width: 440px; width: 100%; background: #fff; border-radius: 16px; padding: 40px; text-align: center;
               box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3); }
        .brand { color: #6bcf8e; font-weight: 700; font-size: 1.5rem; margin-bottom: 16px; }
        h1 { font-size: 1.25rem; margin: 0 0 12px; }
        p { color: #6b7280; line-height: 1.6; margin: 0; }
    </style>
</head>
<body>
    <div class="box">
        <div class="brand">ARIbrain</div>
        <h1>We'll be right back</h1>
        <p>The website is being updated. This usually takes a minute or two. Please refresh the page shortly.</p>
    </div>
</body>
</html>
