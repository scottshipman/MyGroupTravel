<?php
/**
 * Created by PhpStorm.
 * User: scottshipman
 * Date: 2/10/16
 * Time: 9:27 AM
 */

namespace TUI\Toolkit\PaymentBundle\Controller;


use Symfony\Component\HttpFoundation\Request;use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PaymentBundle\Entity\Payment;
use TUI\Toolkit\PassengerBundle\Entity\Passenger;

use Symfony\Component\DependencyInjection\Container;

class PaymentService {

    protected $em;
    protected $container;

    public function __construct(\Doctrine\ORM\EntityManager $em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
    }


    /**
     * @param passengerId
     * @return multi-dimensional array( total =>  amount due for a passenger for a tour, items => list of paymentTasks)
     */
    public function getPassengerPaymentsDue($passengerId) {
        $due = array('total'=>0, 'items'=>array());

        $em = $this->em;
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $tour = $passenger->getTourReference();
        $tourPaymentTasks = $tour->getPaymentTasksPassenger();
        foreach($tourPaymentTasks as $tourPaymentTask) {
            if ($paymentOverride = $em->getRepository('TourBundle:PaymentTaskOverride')
                ->findBy(array('paymentTaskSource'=>$tourPaymentTask->getId(), 'passenger'=>$passengerId))) {
                $tourPaymentTask->setValue($paymentOverride[0]->getValue());
            }
            $due['total'] = $due['total'] + $tourPaymentTask->getValue();
            $due['items'][] = $tourPaymentTask;
        }
        return $due;
    }

    /**
     * @param passengerId
     * @return  multi-dimensional array( total => amount Paid by a passenger for a tour, items => list of payment objects)
     */
    public function getPassengersPaymentsPaid($passengerId) {
        $payments = array('total'=>0, 'items'=>array());
        $em = $this->em;
        $paymentsItems = $em->getRepository('PaymentBundle:Payment')->findBy(array('passenger' => $passengerId));
        foreach($paymentsItems as $item){
            $payments['total'] = $payments['total'] + $item->getValue();
            $payments['items'][] = $item;
        }
        return $payments;
    }

    /**
     * @param passengerId
     * @return float total amount Paid by a passenger for a tour
     */
    public function getPassengersPaymentTasks($passengerId) {
        $now = new \DateTime('now');
        $payments = $this->getPassengersPaymentsPaid($passengerId);
        $cashBalance = $payments['total'];
        $tasks = array();
        $em = $this->em;
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $tour = $passenger->getTourReference();
        $tourPaymentTasks = $tour->getPaymentTasksPassenger();
        foreach($tourPaymentTasks as $tourPaymentTask){
            if($paymentOverride = $em->getRepository('TourBundle:PaymentTaskOverride')
                ->findBy(array('paymentTaskSource'=>$tourPaymentTask->getId(), 'passenger'=>$passengerId))){
                $tourPaymentTask->setValue($paymentOverride[0]->getValue());
            }
            $overdueAmt = 0;
            if($cashBalance >= $tourPaymentTask->getValue()){
                $credit = $tourPaymentTask->getValue();
                $cashBalance = $cashBalance - $credit;
                $status = "paid";
            }elseif($cashBalance < $tourPaymentTask->getValue()){
                $credit = $cashBalance;
                $cashBalance = 0;
                if ($tourPaymentTask->getDueDate() < $now) {
                    $status = "overdue";
                    $overdueAmt = $tourPaymentTask->getValue() - $credit;
                } else {
                    $status = "pending";
                }
            }

            $tasks[] = array('credit' => $credit, 'item' =>$tourPaymentTask, 'status' => $status, 'overdueAmt' => $overdueAmt);
        }
        return $tasks;
    }

    /**
     * @param tourId
     * @return float total amount due for a tour
     */
    public function getTourPaymentsDue($tourId) {
        $total = 0;
        $tour = $em->getRepository('PassengerBundle:Passenger')->find($tourId);
        $tourPaymentTasks = $tour->getPaymentTasksPassenger();
        $tourPassengers = $this->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        foreach($tourPaymentTasks as $tourPaymentTask){
            foreach($tourPassengers as $tourPassenger){
                if ($paymentOverride = $em->getRepository('TourBundle:PaymentTaskOverride')->findBy(array('tour' => $tourId, 'passenger' => $tourPassenger->getId()))){
                    $total = $total + $paymentOverride->getValue();
                } else {
                    $total = $total + $tourPaymentTask->getValue();
                }
            }
        }
        return $total;
    }

    /**
     * @param tourId
     * @return float total amount Paid for a tour
     */
    public function getTourPaymentsPaid($tourId) {

    }

}