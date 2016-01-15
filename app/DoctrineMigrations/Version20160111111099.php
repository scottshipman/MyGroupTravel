<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\TourBundle\Entity\Tour;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160111111099 extends AbstractMigration implements ContainerAwareInterface

{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }

    /**
     * @param Schema $schema
     *
     * Manually adding pax records to existing tours where primary organizer has no pax record
     */
    public function postUp(Schema $schema)
    {

        echo "***********************************************\n"
            ."This is a One-time execution script\n"
            ."This script updates Passenger and Permission Records\n"
            ."For Primary Organizers of Tours prior to this schema existing.\n"
            ."\n"
            ."IMPORTANT: You cannot revert this change. Please keep a copy\n"
            ."of this console ouput so manual db queries can be run.\n"
            ."***********************************************\n"
            ."\n";

        $em = $this->container->get('doctrine.orm.entity_manager');
        $tours = $em->getRepository('TourBundle:Tour')->findAll();

        // loop through tours and get Primary Organizer
        foreach($tours as $tour) {
            echo "Checking tour with ID " . $tour->getId() . "\n";
            $organizer = $tour->getOrganizer();
            echo "Verifying Organizer with User ID  " . $organizer->getId() . "\n";

            //search for a passenger permission for this tour with this organizaer as the user
            $paxPermission = $this->container->get("permission.set_permission")->getObject('parent', $organizer, 'passenger');

            if (empty($paxPermission)) { // no parent grant for a passenger object using this Organizer's User ID
                echo "No matching Permission records \n";
                 $this->createOrganizerPassenger($organizer, $tour, $em);
            }

            elseif ($paxPermission) { // There is a parent grant for a passenger. Verify the passenger is the organizer for that tour
               $match = FALSE;
                echo "Found Permission records:\n";
                //loop the results
                foreach($paxPermission as $permission) {
                    $permission = array_values($permission);
                    echo "... checking permisions with Passenger Object ID: " . $permission[0] . "\n";
                    $passenger = $em->getRepository('PassengerBundle:Passenger')
                        ->find($permission[0]);
                    // all we have is name values to compare, so...
                    if ($passenger &&
                       /* strtolower(trim($organizer->getFirstName())) == strtolower(trim($passenger->getFName())) &&
                        strtolower(trim($organizer->getLastName())) == strtolower(trim($passenger->getLName())) &&*/
                        $passenger->getSelf() == TRUE &&
                        $tour->getId() == $passenger->getTourReference()->getId()
                    ) {
                        echo "...... Nothing to do. Found matching Passenger record with ID " . $passenger->getId() . "\n";
                        $match = TRUE;
                    }
                    else {
                        if ($tour->getId() == $passenger->getTourReference()->getId()) {
                            // same tour but not a match, create a passenger record and permission record
                            echo "...... Tour ID and Organizer Name did not match for this Permission/Passenger record\n";

                        } else {
                            echo "...... Unaffiliated tour for this passenger, moving on...\n";
                        }
                    }
                }
                // no matching records, so create a passenger
                if ($match ==  FALSE) {
                    echo "... No Matching records found for this tour and organizer combo, so need to create records.\n";
                    $this->createOrganizerPassenger($organizer, $tour, $em);
                }
            }
                echo "\n";
        }
    }

    /**
     * @param $organizer
     * @param $tour
     * @param $em
     *
     * Helper function to add a passenger and permission record for the primary organizer
     * when none were created.
     *
     * This happens for any tour converted prior to release of necessary code to handle this (release 11)
     */
    public function createOrganizerPassenger($organizer, $tour, $em)
    {
        $newPassenger = new Passenger();
        $newPassenger->setStatus("waitlist");
        $newPassenger->setFree(false);
        $newPassenger->setFName($organizer->getFirstName());
        $newPassenger->setLName($organizer->getLastName());
        $newPassenger->setTourReference($tour);
        $newPassenger->setGender('undefined');
        $newPassenger->setDateOfBirth(new \DateTime("1987-01-01"));
        $newPassenger->setSignUpDate(new \DateTime("now"));

        $em->persist($newPassenger);
        $em->flush($newPassenger);
        echo "Created Passenger Record with ID " . $newPassenger->getID() . "\n";

        $newPermission = new Permission();
        $newPermission->setUser($organizer);
        $newPermission->setClass('passenger');
        $newPermission->setGrants('parent');
        $newPermission->setObject($newPassenger->getId());


        $em->persist($newPermission);
        $em->flush($newPermission);


        echo "Created Permission Record with ID " . $newPermission->getID() . "\n";
    }
}
