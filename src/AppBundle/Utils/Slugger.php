<?php
/**
 * Created By: scottshipman
 * Date: 6/18/15
 */

namespace AppBundle\Utils;


class Slugger {

  /** Create a url path slug
   * eg: Page title "Some Toolkit Page" becomes slug "some-toolkit-page"
   *
   * Implementation:
   *     $slug = $this->get('app.slugger')->slugify($post->getTitle());
   *     $post->setSlug($slug);
   */
  static function slugify($string)
  {
    return preg_replace(
      '/[^a-z0-9]/', '-', strtolower(trim(strip_tags($string)))
    );
  }
} 