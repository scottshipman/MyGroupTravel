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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
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
                'APD Code',
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
                'Date Signed Up',
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
        if($type=="organizer"){$type="";}
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $type . '-Passengers-' . $tour->getName() . '.csv"');

        return $response;
    }


    public function getBrandPassengerList($tourId){
        $locale =$this->container->getParameter('locale');
        switch (true) {
            case strstr($locale, 'en_GB'):

                $format = 'd-M-Y';
                break;
            default:

                $format = 'M-d-Y';
                break;
        }
        $items=array();
        $em = $this->getDoctrine()->getManager();
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findBy(array('tourReference' => $tourId));
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        foreach($passengers as $passenger){
            $item=array(); $passport=null;
            if($passenger->getPassportReference() != NULL){$passport = $em->getRepository('PassengerBundle:Passport')->find($passenger->getPassportReference()->getId());}
            $item['Family Name'] = isset($passport) ? $passport->getPassportLastName() : '';
            $item['First Name'] = isset($passport) ? $passport->getPassportFirstName() : '';
            $item['Middle Name'] = isset($passport) ? $passport->getPassportMiddleName() : '';
            $item['Gender'] = isset($passport) ? $passport->getPassportGender() : '';
            $item['Title'] = isset($passport) ? $passport->getPassportTitle() : '';
            $item['Child Fare'] = 'N';
            $item['Dietary Request Code'] = '';
            $item['Code of Issuing State'] = isset($passport) ? $passport->getPassportIssuingState() : '';
            $item['Passport Number'] = isset($passport) ? $passport->getPassportNumber() : '';
            $item['Nationality'] = isset($passport) ? $passport->getPassportNationality() : '';
            if(isset($passport) && get_class($passport->getPassportDateOfBirth()) == "DateTime"){$ppid = $passport->getPassportDateOfBirth()->format($format);} else {$ppid = '';}
            $item['Date of Birth'] = $ppid;
            if (!empty($ppid)) {
                $dob = new \DateTime($ppid);
                $departure_date = $tour->getDepartureDate();
                $age = $dob->diff($departure_date)->y;
            }
            $item['APD Code'] = (!empty($age) && $age < 16) ? 'CHILD - ACCOMPANIED' : '';
            if(isset($passport) && get_class($passport->getPassportDateOfExpiry()) == "DateTime"){$pped = $passport->getPassportDateOfExpiry()->format($format);} else {$pped = '';}
            $item['Passport Expiry Date'] = $pped;
            $item['Passport Holder'] = '';
            $item['Country of Residence'] = '';
            $item['Citizenship'] = isset($passport) ? $passport->getPassportNationality() : '';
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
        $locale =$this->container->getParameter('locale');
        switch (true) {
            case strstr($locale, 'en_GB'):

                $format = 'd-M-Y';
                break;
            default:

                $format = 'M-d-Y';
                break;
        }
        $em = $this->getDoctrine()->getManager();
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findBy(array('tourReference' => $tourId));
        foreach($passengers as $passenger){
            $item=array(); $medical=null; $dietary=null; $emergency = null; $passport = null;
            if($passenger->getPassportReference() != NULL){$passport = $em->getRepository('PassengerBundle:Passport')->find($passenger->getPassportReference()->getId());}
            if($passenger->getEmergencyReference() != NULL){$emergency = $em->getRepository('PassengerBundle:Emergency')->find($passenger->getEmergencyReference()->getId());}
            if($passenger->getDietaryReference() != NULL){$dietary = $em->getRepository('PassengerBundle:Dietary')->find($passenger->getDietaryReference()->getId());}
            if($passenger->getMedicalReference() != NULL){$medical = $em->getRepository('PassengerBundle:Medical')->find($passenger->getMedicalReference()->getId());}
            $parentId = $this->get("permission.set_permission")->getUser('parent', $passenger->getId(), 'passenger');
            $parent = $em->getRepository('TUIToolkitUserBundle:User')->find($parentId[1]);
            $status = 'waitlist';
            if ($passenger->getStatus() == 'accepted') { $status = 'accepted';}
            if ($passenger->getFree() == TRUE) { $status = 'free';}
            $item['Passenger Name']  = $passenger->getFName() . ' ' . $passenger->getLName();
            if(get_class($passenger->getDateOfBirth()) == "DateTime"){$pdob = $passenger->getDateOfBirth()->format($format);} else {$pdob = '';}
            $item['Passenger DOB']  =  $pdob;
            $item['Passenger Gender'] = $passenger->getGender();
            $item['Passport Number'] = isset($passport) ? $passport->getPassportNumber() : '';
            $item['Title'] = isset($passport) ? $passport->getPassportTitle() : '';
            $item['First Name'] = isset($passport) ? $passport->getPassportFirstName() : '';
            $item['Middle Name'] = isset($passport) ? $passport->getPassportMiddleName() : '';
            $item['Last Name'] = isset($passport) ? $passport->getPassportLastName() : '';
            if(isset($passport) && get_class($passport->getPassportDateOfIssue()) == "DateTime"){$ppid = $passport->getPassportDateOfIssue()->format($format);} else {$ppid = '';}
            $item['Passport Issue Date'] = $ppid;
            if(isset($passport) && get_class($passport->getPassportDateOfExpiry()) == "DateTime"){$pped = $passport->getPassportDateOfExpiry()->format($format);} else {$pped = '';}
            $item['Passport Expiry Date'] = $pped;
            $item['Nationality'] = isset($passport) ? $passport->getPassportNationality() : '';
            $item['Code of Issuing State'] = isset($passport) ? $passport->getPassportIssuingState() : '';
            $item['Gender'] = isset($passport) ? $passport->getPassportGender() : '';
            if(isset($passport) && get_class($passport->getPassportDateOfBirth()) == "DateTime"){$ppdob = $passport->getPassportDateOfBirth()->format($format);} else {$ppdob = '';}
            $item['Passport DOB'] = $ppdob;
            $item['Medical Doctors Name'] = isset($medical) ? $medical->getDoctorName() : '';
            $item['Medical Doctors Phone Number'] = isset($medical) ? $medical->getDoctorNumber() : '';
            $item['Medical Conditions'] = isset($medical) ? $medical->getConditions() : '';
            $item['Medical Medications'] = isset($medical) ? $medical->getMedications() : '';
            $item['Dietary details'] = isset($dietary) ? $dietary->getDescription() : '';
            $item['Emergency Contact Name'] = isset($emergency) ? $emergency->getEmergencyName() :  '';
            $item['Emergency Contact Relationship'] = isset($emergency) ? $emergency->getEmergencyRelationship() : '';
            $item['Emergency Contact Phone Number'] = isset($emergency) ? $emergency->getEmergencyNumber() : '';
            $item['Emergency Contact Email'] = isset($emergency) ? $emergency->getEmergencyEmail() : '';
            $item['Parent Name'] = $parent->getFirstName() . ' ' . $parent->getLastName();
            $item['Parent Email'] = $parent->getEmail();
            if(get_class($passenger->getSignUpDate()) == "DateTime"){$psud = $passenger->getSignUpDate()->format($format);} else {$psud = '';}
            $item['Date Signed Up'] = $psud;
            $item['Travelling Status'] = $status;
            $items[] = $item;
        }
        return $items;
    }

}