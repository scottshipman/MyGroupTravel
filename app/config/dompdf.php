<?php

// Allow remote stylesheets and images, floating, and HTML5
define("DOMPDF_ENABLE_REMOTE", true);
define("DOMPDF_ENABLE_CSS_FLOAT", true);
define("DOMPDF_ENABLE_HTML5PARSER", true);

// Disable DOMPDF's internal autoloader because Symfony 2 uses Composer
define('DOMPDF_ENABLE_AUTOLOAD', false);

// Make DOMPDF use the Symfony 2 cache folders
define("DOMPDF_FONT_DIR", $this->getCacheDir());
define("DOMPDF_FONT_CACHE", $this->getCacheDir());

// Include DOMPDF's default configuration
require "{$this->getRootDir()}/../vendor/dompdf/dompdf/dompdf_config.inc.php";
