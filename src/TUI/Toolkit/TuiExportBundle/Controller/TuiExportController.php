<?php
/**
 * Created by  scottshipman
 * Date: 3/2/16
 * Time: 11:55 AM
 */


namespace TUI\Toolkit\TuiExportBundle\Controller;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TUI\Toolkit\PassengerBundle\Controller\PassengerController;
use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use \AppBundle\Twig\AppExtension;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class TuiExportController extends Controller
{


    public function generateCsvAction($type, $tourId)
    {
        $permission = array();

        //Check to see if the user is allowed to view the dashboard

        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        if ($user != 'anon.') {
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
        }

        if (!$securityContext->isGranted('ROLE_BRAND')) {
            if ($permission != null && !in_array('organizer', $permission) && !in_array('assistant', $permission)) {
                throw $this->createAccessDeniedException('You are not authorized to view this data!');
            }
            elseif ($permission == null ) {
                throw $this->createAccessDeniedException('You are not authorized to view this data!');
            }
        }

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        if ($type == 'brand') {
            $results = $this->getBrandPassengerList($tourId);
            $rowHeaders = array(
                'Family Name',
                'First Name',
                'Middle Name',
                'Gender',
                'Title',
                'Child Fare',
                'Dietary Request Code',
                'Code of Issuing State',
                'Passport Number',
                'Nationality',
                'Date of Birth',
                'Passport Expiry Date',
                'Passport Holder',
                'Country of Residence',
                'Citizenship',
                'Destination or Residence',
                'Country 3 Letter Code',
                'Hotel & Address',
                'City',
                'State/County',
                'Post Code',
            );
        } else {
            $results = $this->getOrganizerPassengerList($tourId);
            $rowHeaders = array(
                'Passenger name',
                'Date of birth',
                'Gender',
                'Passport Number',
                'Passport Title',
                'Passport First Name',
                'Passport Middle Name',
                'Passport Last Name',
                'Passport Date of Issue',
                'Passport Date of Expiry',
                'Passport Nationality',
                'Passport Issuing State',
                'Passport Gender',
                'Passport DOB',
                'Medical Doctors Name',
                'Medical Doctors Phone Number',
                'Medical Conditions',
                'Medical Medications',
                'Dietary details',
                'Emergency Contact Name',
                'Emergency Contact Relationship',
                'Emergency Contact Phone Number',
                'Emergency Contact Email',
                'Parent Name',
                'Parent Email',
                'Date Singed Up',
                'Travelling Status',
            );
        }
        array_unshift($results, $rowHeaders);
        $response = new StreamedResponse();
        $response->setCallback(
            function () use ($results) {
                $handle = fopen('php://output', 'r+');
                foreach ($results as $row) {
                    //array list fields you need to export
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }
        );
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $type . '-Passengers-' . $tour->getName() . '.csv"');

        return $response;
    }


    public function getBrandPassengerList($tourId){
        $items=array();
        $format =$this->container->getParameter('date_format');
        $em = $this->getDoctrine()->getManager();
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findBy(array('tourReference' => $tourId));
        foreach($passengers as $passenger){
            $passport = $em->getRepository('PassengerBundle:Passport')->findBy(array('passengerReference' => $passenger->getPassportReference()));
            $item['Family Name'] = isset($passport[0]) ? $passport[0]->getPassportLastName() : '';
            $item['First Name'] = isset($passport[0]) ? $passport[0]->getPassportFirstName() : '';
            $item['Middle Name'] = isset($passport[0]) ? $passport[0]->getPassportMiddleName() : '';
            $item['Gender'] = isset($passport[0]) ? $passport[0]->getPassportGender() : '';
            $item['Title'] = isset($passport[0]) ? $passport[0]->getPassportTitle() : '';
            $item['Child Fare'] = 'N';
            $item['Dietary Request Code'] = '';
            $item['Code of Issuing State'] = isset($passport[0]) ? $passport->getPassportIssuingState() : '';
            $item['Passport Number'] = isset($passport[0]) ? $passport->getPassportNumber() : '';
            $item['Nationality'] = isset($passport[0]) ? $passport->getPassportNationality() : '';
            if(isset($passport[0]) && get_class($passport[0]->getPassportDateOfBirth()) == "DateTime"){$ppid = $passport[0]->getPassportDateOfBirth()->format($format);} else {$ppid = '';}
            $item['Date of Birth'] = $ppid;
            if(isset($passport[0]) && get_class($passport[0]->getPassportDateOfExpiry()) == "DateTime"){$pped = $passport[0]->getPassportDateOfExpiry()->format($format);} else {$pped = '';}
            $item['Passport Expiry Date'] = $pped;
            $item['Passport Holder'] = '';
            $item['Country of Residence'] = '';
            $item['Citizenship'] = isset($passport[0]) ? $passport[0]->getPassportNationality() : '';
            $item['Destination or Residence'] = '';
            $item['Country 3 Letter Code'] = '';
            $item[ 'Hotel & Address'] = '';
            $item['City'] = '';
            $item['State/County'] = '';
            $item['Post Code']= '';
            $items[] = $item;
        }
        return $items;
    }

    public function getOrganizerPassengerList($tourId){
        $items=array();
        $format =$this->container->getParameter('date_format');
        $em = $this->getDoctrine()->getManager();
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findBy(array('tourReference' => $tourId));
        foreach($passengers as $passenger){
            $medical = $em->getRepository('PassengerBundle:Medical')->findBy(array('passengerReference' => $passenger->getMedicalReference()));
            $passport = $em->getRepository('PassengerBundle:Passport')->findBy(array('passengerReference' => $passenger->getPassportReference()));
            $emergency = $em->getRepository('PassengerBundle:Emergency')->findBy(array('passengerReference' => $passenger->getEmergencyReference()));
            $dietary = $em->getRepository('PassengerBundle:Dietary')->findBy(array('passengerReference' => $passenger->getDietaryReference()));
            $parentId = $this->get("permission.set_permission")->getUser('parent', $passenger->getId(), 'passenger');
            $parent = $em->getRepository('TUIToolkitUserBundle:User')->find($parentId[1]);
            $status = 'waitlist';
            if ($passenger->getStatus() == 'accepted') { $status = 'accepted';}
            if ($passenger->getFree() == TRUE) { $status = 'free';}
            $item['Passenger Name']  = $passenger->getFName() . ' ' . $passenger->getLName();
            if(get_class($passenger->getDateOfBirth()) == "DateTime"){$pdob = $passenger->getDateOfBirth()->format($format);} else {$pdob = '';}
            $item['Passenger DOB']  =  $pdob;
            $item['Passenger Gender'] = $passenger->getGender();
            $item['Passport Number'] = isset($passport[0]) ? $passport->getPassportNumber() : '';
            $item['Title'] = isset($passport[0]) ? $passport[0]->getPassportTitle() : '';
            $item['First Name'] = isset($passport[0]) ? $passport[0]->getPassportFirstName() : '';
            $item['Middle Name'] = isset($passport[0]) ? $passport[0]->getPassportMiddleName() : '';
            $item['Last Name'] = isset($passport[0]) ? $passport[0]->getPassportLastName() : '';
            if(isset($passport[0]) && get_class($passport[0]->getDateOfIssue()) == "DateTime"){$ppid = $passport[0]->getDateOfIssue()->format($format);} else {$ppid = '';}
            $item['Passport Issue Date'] = $ppid;
            if(isset($passport[0]) && get_class($passport[0]->getPassportDateOfExpiry()) == "DateTime"){$pped = $passport[0]->getPassportDateOfExpiry()->format($format);} else {$pped = '';}
            $item['Passport Expiry Date'] = $pped;
            $item['Nationality'] = isset($passport[0]) ? $passport[0]->getPassportNationality() : '';
            $item['Code of Issuing State'] = isset($passport[0]) ? $passport[0]->getPassportIssuingState()->format($format) : '';
            $item['Gender'] = isset($passport[0]) ? $passport[0]->getPassportGender() : '';
            if(isset($passport[0]) && get_class($passport[0]->getPassportDateOfBirth()) == "DateTime"){$ppdob = $passport[0]->getPassportDateOfBirth()->format($format);} else {$ppdob = '';}
            $item['Passport DOB'] = $ppdob;
            $item['Medical Doctors Name'] = isset($medical[0]) ? $medical[0]->getDoctorName() : '';
            $item['Medical Doctors Phone Number'] = isset($medical[0]) ? $medical->getDoctorNumber() : '';
            $item['Medical Conditions'] = isset($medical[0]) ? $medical[0]->getConditions() : '';
            $item['Medical Medications'] = isset($medical[0]) ? $medical[0]->getMedications() : '';
            $item['Dietary details'] = isset($dietary[0]) ? $dietary[0]->getDescription() : '';
            $item['Emergency Contact Name'] = isset($emergency[0]) ? $emergency[0]->getEmergencyName() :  '';
            $item['Emergency Contact Relationship'] = isset($emergency[0]) ? $emergency[0]->getEmergencyRelationship() : '';
            $item['Emergency Contact Phone Number'] = isset($emergency[0]) ? $emergency[0]->getEmergencyNumber() : '';
            $item['Emergency Contact Email'] = isset($emergency[0]) ? $emergency[0]->getEmergencyEmail() : '';
            $item['Parent Name'] = $parent->getFirstName() . ' ' . $parent->getLastName();
            $item['Parent Email'] = $parent->getEmail();
            if(get_class($passenger->getSignUpDate()) == "DateTime"){$psud = $passenger->getSignUpDate()->format($format);} else {$psud = '';}
            $item['Date Singed Up'] = $psud;
            $item['Travelling Status'] = $status;
            $items[] = $item;
        }
        return $items;
    }

}