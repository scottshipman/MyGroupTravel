<?php

function rgb_hsl( $rgb ) {
  $r = $rgb[0];
  $g = $rgb[1];
  $b = $rgb[2];

	$r /= 255;
	$g /= 255;
	$b /= 255;

  $max = max( $r, $g, $b );
	$min = min( $r, $g, $b );

	$h;
	$s;
	$l = ( $max + $min ) / 2;
	$d = $max - $min;

  if ( $d == 0 ) {
    $h = $s = 0; // achromatic
  } else {
    $s = $d / ( 1 - abs( 2 * $l - 1 ) );
		switch( $max ) {
      case $r:
        $h = 60 * fmod( ( ( $g - $b ) / $d ), 6 );
        if ($b > $g) {
          $h += 360;
        }
        break;
      case $g:
        $h = 60 * ( ( $b - $r ) / $d + 2 );
        break;
      case $b:
        $h = 60 * ( ( $r - $g ) / $d + 4 );
        break;
	  }
	};

	return array( round( $h, 3 ), round( $s, 3 ), round( $l, 3 ) );
};

function hsl_text( $hsl, $l ) {
  $h = $hsl[0];

  return array( $h, 1, $l );
};

function hsl_rgb( $hsl ) {
  $h = $hsl[0];
  $s = $hsl[1];
  $l = $hsl[2];

  $r;
  $g;
  $b;

	$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
	$x = $c * ( 1 - abs( fmod( ( $h / 60 ), 2 ) - 1 ) );
	$m = $l - ( $c / 2 );

	if ( $h < 60 ) {
		$r = $c;
		$g = $x;
		$b = 0;
	} else if ( $h < 120 ) {
		$r = $x;
		$g = $c;
		$b = 0;
	} else if ( $h < 180 ) {
		$r = 0;
		$g = $c;
		$b = $x;
	} else if ( $h < 240 ) {
		$r = 0;
		$g = $x;
		$b = $c;
	} else if ( $h < 300 ) {
		$r = $x;
		$g = 0;
		$b = $c;
	} else {
		$r = $c;
		$g = 0;
		$b = $x;
	};

	$r = floor( ( $r + $m ) * 255 );
	$g = floor( ( $g + $m ) * 255 );
	$b = floor( ( $b + $m  ) * 255 );

  return 'rgb(' . $r . ', ' . $g . ', ' . $b . ')';
};

function rgb_brightness( $rgb ) {
  $r = $rgb[0];
  $g = $rgb[1];
  $b = $rgb[2];

  return ( ($r * 299) + ($g * 587) + ($b * 114) ) / 1000; // W3C recommendation
}

$primary = preg_replace('/\s+/', '', $brand->getPrimaryColor());
$secondary = preg_replace('/\s+/', '', $brand->getSecondaryColor());

$primaryArray = explode(',', str_replace(array('rgb(', ')'), array('', ''), $primary) );
$secondaryArray = explode(',', str_replace(array('rgb(', ')'), array('', ''), $secondary) );

if ( rgb_brightness($primaryArray) <= 125 ) {
  $contrast = 'rgb(255,255,255)';
} else {
  $contrast = 'rgb(66,66,66)';
};

?><style>
/* MDL color overrides */
a {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.5)) ?>;
}

.mdl-color-text--primary-contrast {
  color: <?php echo $contrast ?> !important;
}

.mdl-button.mdl-button--colored {
  color: <?php echo $primary ?>;
}

.mdl-button--raised.mdl-button--colored,
.mdl-button--raised.mdl-button--colored:hover {
  background-color: <?php echo $primary ?>;
  color: <?php echo $contrast ?>;
}

.mdl-layout__header {
  background-color: <?php echo $primary ?>;
  color: <?php echo $contrast ?>;
}

.mdl-layout__drawer .mdl-navigation .active a {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.5)) ?>;
}

/* Custom MDL matching colors */
.tui-text-avatar {
  background-color: <?php echo $primary ?>;
  color: <?php echo $contrast ?>;
}
</style>
