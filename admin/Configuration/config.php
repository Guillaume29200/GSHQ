<?php
// Environment setting ('development' or 'production')
define('ENV', 'production');

// Base URL of the site (adjust according to your environment)
define('BASE_URI', 'http://localhost/gshq');

// Website metadata
define('WEBSITE_NAME', 'GSHQ Standalone – The Game Server Monitor');
define('WEBSITE_SLOGAN', 'Your slogan here');
define('WEBSITE_DESC', 'Your website description here');
define('WEBSITE_KEYWORDS', 'game servers viewer, game viewer, game monitor, hosting, multiplayer, GSHQ, eSport-CMS, gameserver-hub');
define('WEBSITE_FAVICON', BASE_URI . '/assets/favicon.ico');

// Footer config
$footerLinks = [
    'eSport-CMS' => 'https://esport-cms.net/',
    'GameServer-Hub' => 'https://gameserver-hub.com/',
    'Forum Community' => 'https://esport-cms.net/forum'
];

// Database config
$dbConfig = [
    'host' => 'localhost',
    'name' => '',
    'user' => '',
    'pass' => ''
];

// Try database connection
try {
    $conn = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $ex) {
    // Always display the styled error page (regardless of ENV)
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>GSHQ - Not Installed</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', sans-serif;
                background: #f4f4f4;
                color: #333;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
            }
            .error-box {
                background: #fff;
                padding: 40px;
                max-width: 600px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .error-box h1 {
                font-size: 26px;
                margin-bottom: 20px;
                color: #d9534f;
            }
            .error-box p {
                font-size: 16px;
                margin: 10px 0;
            }
            .error-box code {
                background-color: #f1f1f1;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 14px;
            }
            .error-details {
                margin-top: 20px;
                padding-top: 15px;
                border-top: 1px solid #eee;
                color: #777;
                font-size: 13px;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>⚠️ GSHQ is not installed</h1>
            <p>Please edit the <code>config.php</code> file located at:</p>
            <p><strong>/admin/Configuration/config.php</strong></p>
            <p>Then import the <code>database.sql</code> file into your database via PhpMyAdmin.</p>
            <div class="error-details">
                <strong>Debug Info:</strong><br>
                <?php echo htmlspecialchars($ex->getMessage(), ENT_QUOTES); ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit(); // VERY IMPORTANT: stop all execution
}
