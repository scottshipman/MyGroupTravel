<?php

namespace TUI\Toolkit\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TUIToolkitUserBundle extends Bundle
{
  public function getParent()
  {
    return 'FOSUserBundle';

/* By returning the name of the bundle in the getParent method of your bundle class,
 * you are telling the Symfony2 framework that your bundle is a child of the FOSUserBundle.
 *
 * Now that you have declared your bundle as a child of the FOSUserBundle,
 * you can override the parent bundle's templates. To override the layout template,
 * simply create a new file in the src/TUI/Tooolkit/UserBundle/Resources/views directory named layout.html.twig.
 * Notice how this file resides in the same exact path relative to the bundle directory as it does
 * in the FOSUserBundle.
 */
  }
}
