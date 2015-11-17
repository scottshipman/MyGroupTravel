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

function hsl_background( $hsl, $l ) {
  $h = $hsl[0];
  $s = $hsl[1];

  return array( $h, $s, $l );
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
};

function color_contrast( $colorArray ) {
  if ( rgb_brightness($colorArray) <= 125 ) {
    return 'rgb(255,255,255)';
  } else {
    return 'rgb(66,66,66)';
  }
};

$primary = preg_replace('/\s+/', '', $brand->getPrimaryColor());
$secondary = preg_replace('/\s+/', '', $brand->getSecondaryColor());
$tertiary = preg_replace('/\s+/', '', $brand->getTertiaryColor());

$primaryArray = explode(',', str_replace(array('rgb(', ')'), array('', ''), $primary) );
$secondaryArray = explode(',', str_replace(array('rgb(', ')'), array('', ''), $secondary) );
$tertiaryArray = explode(',', str_replace(array('rgb(', ')'), array('', ''), $tertiary) );

?><style>
/* MDL color overrides */
a {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.25)) ?>;
}

.mdl-color-text--primary-contrast {
  color: <?php echo color_contrast($primaryArray) ?> !important;
}

.mdl-button.mdl-button--colored {
  color: <?php echo $primary ?>;
}

.mdl-button--raised.mdl-button--colored,
.mdl-button--raised.mdl-button--colored:hover {
  background-color: <?php echo $secondary ?>;
  color: <?php echo color_contrast($secondaryArray) ?>;
}

.brand_logo_login,
.mdl-layout__header {
  background-color: <?php echo $tertiary ?>;
  color: <?php echo color_contrast($tertiaryArray) ?>;
}

.sub-header,
.mdl-mini-footer {
  background-color: <?php echo $primary ?>;
  color: <?php echo color_contrast($primaryArray) ?>;
}

.mdl-layout__drawer .mdl-navigation .active a {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.25)) ?>;
}

.mdl-textfield--floating-label.is-focused .mdl-textfield__label,
.mdl-textfield--floating-label.is-dirty .mdl-textfield__label,
.mdl-label-mimic {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($primaryArray), 0.25)) ?>;
}

.mdl-textfield__label::after {
  background-color: <?php echo $primary ?>;
}

.mdl-checkbox.is-checked .mdl-checkbox__box-outline,
.mdl-radio.is-checked .mdl-radio__outer-circle {
  border-color: <?php echo $primary ?>;
}

.mdl-checkbox.is-checked .mdl-checkbox__tick-outline,
.mdl-radio__inner-circle {
  background-color: <?php echo $primary ?>;
}

.mdl-tabs.is-upgraded .mdl-tabs__tab.is-active::after,
.mdl-tabs-no-swap .is-active::after {
  background-color: <?php echo $secondary ?>;
}

.page-title .mdl-button--raised.mdl-button--colored,
.page-title .mdl-button--raised.mdl-button--colored:hover {
  background-color: <?php echo $secondary ?>;
  color: <?php echo color_contrast($secondaryArray) ?>;
}

/* Custom MDL matching colors */
.tui-text-avatar {
  background-color: <?php echo $primary ?>;
  color: <?php echo color_contrast($primaryArray) ?>;
}

.content-block h2 {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.25)) ?>;;
}

.add-content-block {
  border-color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.25)) ?>;
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.25)) ?>;
}

.site-content-block-actions {
  background-color: <?php echo $primary ?>;
  color: <?php echo color_contrast($primaryArray) ?>;
}

.mode-edit .inner-wrapper:hover {
  outline-color: <?php echo $primary ?>;
}

.sp-replacer.sp-active,
.sp-replacer:hover,
.sp-container {
  border-color: <?php echo $primary ?>;
}

.media-placeholder-image,
#dropzone_form {
  border-color: <?php echo $primary ?>;
}

#avatar-label {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($primaryArray), 0.25)) ?>;
}

.existing-media-wrapper .existing-media-item .existing-delete {
  color: <?php echo $primary ?>;
}

.tab-reorder-modal .submit-button-container .reorder-button {
  color: <?php echo $primary ?>;
}

.alt-quote.even {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.25)) ?>;
}

.alt-quote.even:hover {
  color: <?php echo hsl_rgb(hsl_text(rgb_hsl($secondaryArray), 0.125)) ?>;
}

.profile-tour-header:before {
  background: linear-gradient(to bottom right, <?php echo hsl_rgb(hsl_background(rgb_hsl($tertiaryArray), 0.3)) ?> 0%, <?php echo hsl_rgb(hsl_background(rgb_hsl($tertiaryArray), 0.7)) ?> 100%);
}
</style>
