<?php
/*
 * Toolkit maintenance mode holding page
 *
 * This single file contains everything that is required to display a maintenance page for the Toolkit.
 * The only other files that need to be in place are logo files - the location of these can be configured below using
 * the $images variable below.
 */

// Set header to 503 just to be sure (server will probably do it).
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600');

// Get the brand name and email address based on the site the user requested.
$host = $_SERVER['HTTP_HOST'];

// An array keyed by brand URL containing important information used in the maintenance page.
$brands = [
    'edwindoran.com' => [
        'name' => 'Edwin Doran',
        'email' => 'ed@edwindoran.com',
        'logo' => 'edwin.png'
    ],
    'masterclasstours.co.uk' => [
        'name' => 'MasterClass Tours',
        'email' => 'info@masterclasstours.co.uk',
        'logo' => 'masterclass.png'

    ],
    'gulliverstravel.co.uk' => [
        'name' => 'Gullivers Travel',
        'email' => 'info@gulliverstravel.co.uk',
        'logo' => 'gulliver.png'
    ],
    'teamlink.co.uk' => [
        'name' => 'Teamlink',
        'email' => 'info@teamlink.co.uk',
        'logo' => 'teamlink.png'
    ],
    'skibound.co.uk' => [
        'name' => 'SkiBound',
        'email' => 'info@skibound.co.uk',
        'logo' => 'skibound.png'
    ],
    'htstotalski.com' => [
        'name' => 'HTS Total Ski',
        'email' => 'sales@htstotalski.com',
        'logo' => 'hts.png'
    ],
    'jca-adventure.co.uk' => [
        'name' => 'JCA',
        'email' => 'enquiries@jca-adventure.co.uk',
        'logo' => 'jca.png'
    ],
    'toolkit.vm' => [
        'name' => 'local',
        'email' => 'test@example.org',
        'logo' => 'test.png'
    ]
];

// Set the directory that any images (logos etc) should come from.
$images = 'static/maintenance/images/';

// The filename for the toolkit logo.
$toolkit_logo_file = 'toolkit.png';

// Check if the toolkit logo exists and if so, make it available for rendering.
$toolkit_logo = (file_exists($images . $toolkit_logo_file) ? $images . $toolkit_logo_file : false);

// Get the information for our brand, if it exists.
if (array_key_exists($host, $brands)) {
    $brand_name = (!empty($brands[$host]['name']) ? $brands[$host]['name'] : '');
    $brand_email = (!empty($brands[$host]['email']) ? $brands[$host]['email'] : false);
    $brand_logo = (!empty($brands[$host]['logo'] && file_exists($images . $brands[$host]['logo'])) ? $images . $brands[$host]['logo'] : false);
} else {
    die('This application is currently in maintenance mode, please try again later');
}

?>
<!DOCTYPE html>
<html lang="en">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<head>
    <meta charset="UTF-8">
    <title><?php echo $brand_name; ?> Toolkit</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        html {
            position: relative;
            min-height: 100%;
        }
        img {
            max-width: 200px;
            height: auto;
            margin-top: 20px;
        }
        .tk-logo {
            max-width: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if ($brand_logo) {
            echo '<img src="'.$brand_logo.'">';
        }
        if ($toolkit_logo) {
            echo '<img src="'.$toolkit_logo.'" class="tk-logo">';
        }
        ?>
        <h1>Site under maintenance</h1>
        <p>We're really sorry, but the <?php echo $brand_name; ?> Toolkit is currently undergoing essential maintenance.</p>
        <?php
        if ($brand_email) {
            echo '<p>You can email us <a href="mailto:'.$brand_email.'">' . $brand_email . '</a></p>';
        }
        ?>
    </div>
</body>
</html>
