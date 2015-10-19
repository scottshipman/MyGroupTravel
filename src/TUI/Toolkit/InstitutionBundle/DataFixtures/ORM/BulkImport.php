<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\institutionBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TUI\Toolkit\InstitutionBundle\Entity\Institution;
use TUI\Toolkit\MediaBundle\Entity\Media;




//class BulkImport implements FixtureInterface, ContainerAwareInterface
//{
//  private $container;
//
//  public function setContainer(ContainerInterface $container = null)
//  {
//    $this->container = $container;
//  }
//
//  public function load(ObjectManager $em)
//  {
//
//
//    // load file from somewhere
//    // loop through each row and create institution
//    // do we need images?
//
//    //$fileName = 'scripts/institutions.csv';
//    $fileName = 'institutions.csv';
//    if(!file_exists(getcwd() . '/scripts/' . $fileName) || !is_readable(getcwd() . '/scripts/' . $fileName)) {
//      echo "ERROR: Could not find the file $fileName\n";
//      echo getcwd() . $fileName . "\n";
//      var_dump(scandir(getcwd() . '/scripts/'));
//      return FALSE;
//    }
//
//
//    if (($handle = fopen(getcwd() . '/scripts/' . $fileName, "r")) !== FALSE) {
//      $head = fgetcsv($handle);
//      while(($row = fgetcsv($handle)) !== FALSE) {
//
//        $row = array_combine($head, $row);
//
//        $institution = new Institution();
//        $institution->setName($row['institutionName']);
//        $institution->setAddress1($row['address1']);
//        $institution->setAddress2('');
//        $institution->setCity($row['town']);
//        $institution->setCounty($row['county']);
//        $institution->setState('');
//        $institution->setPostCode($row['postCode']);
//        $institution->setCountry($row['country']);
//
//        // if media file, then create Media entity and associate it
//        if($row['fileName'] !== 'NULL')  {
//          $media = new Media();
//          $media->setContext('institution');
//          $media->setFilename($row['fileName']);
//          $media->setHashedFilename($row['fileID']. '.' . strtolower($row['fileType']));
//          $media->setFilepath('/srv/www/Toolkit/app/../web/static/uploads/media/institution');
//          $media->setRelativepath('/static/uploads/media/institution');
//          $media->setMimetype($row['fileType']);
//          $em->persist($media);
//          $em->flush($media);
//          $institution->setMedia($media);
//        }
//
//
//        $em->persist($institution);
//        $em->flush();
//      }
//    }
//
//
//   /*
//   *** Sql from Java app DB to generate CSV file ***
//
//    select
//      i.id as institutionID,
//      i.code as SchoolCode,
//      i.local_authority as localAuthority,
//      i.name as institutionName,
//      i.type as institutionType,
//      i.website_address as institutionWebSite,
//      a.country as country,
//      a.county as county,
//      a.post_code as postCode,
//      a.address_text as address1,
//      a.town as town,
//      i.logo as fileID,
//      f.source_file_name as fileName,
//      f.type as fileType
//
//    from institution i
//      left join address a on i.address_id = a.id
//      left join file f on i.logo = f.id
//   */
//
//  }
//}