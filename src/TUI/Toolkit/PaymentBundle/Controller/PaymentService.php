<?php
/**
 * Created by PhpStorm.
 * User: scottshipman
 * Date: 2/10/16
 * Time: 9:27 AM
 */

namespace TUI\Toolkit\PaymentBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PaymentBundle\Entity\Payment;
use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\TourBundle\Entity\Tour;

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
        $em = $this->em;
        $now = new \DateTime('now');
        $final = 0;
        $finalStatus = 'pending';
        $finalOverdueAmt = 0;
        $cashBalance = $this->getTourPaymentsPaid($tourId);
        $collected = $cashBalance;
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $tourPaymentTasks = $tour->getPaymentTasksPassenger();
        $tourPassengers = $this->container->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        // initialize task storage structure
        foreach($tourPaymentTasks as $tourPaymentTask) {
            $taskItems[$tourPaymentTask->getId()] = array(
                    'credit'=>0,
                    'total'=>0,
                    'overdueAmt'=>0,
                    'status'=>'',
                    'task'=>'',
                );
        }


        foreach($tourPassengers as $tourPassenger) {
            $paxPayments = $this->getPassengersPaymentsPaid($tourPassenger->getId());
            $cashBalance = $paxPayments['total'];
            foreach ($tourPaymentTasks as $tourPaymentTask) {
                $taskItems[$tourPaymentTask->getId()]['task'] = $tourPaymentTask;
                // was task overriden?
                if ($paymentOverride = $em->getRepository('TourBundle:PaymentTaskOverride')
                    ->findBy(array('paymentTaskSource'=>$tourPaymentTask->getId(), 'passenger'=>$tourPassenger->getId()))){
                    $total = $paymentOverride[0]->getValue();
                } else {
                    $total = $tourPaymentTask->getValue();
                }
                // increment to total due for the task
                $taskItems[$tourPaymentTask->getId()]['total'] = $taskItems[$tourPaymentTask->getId()]['total'] + $total;

                //allocate funds to task for this passenger
                $overdueAmt = 0;
                if($cashBalance >= $total){
                    $credit = $total;
                    $cashBalance = $cashBalance - $credit;
                    $taskItems[$tourPaymentTask->getId()]['credit'] = $taskItems[$tourPaymentTask->getId()]['credit'] + $credit;
                }elseif($cashBalance < $total){
                    $credit = $cashBalance;
                    $cashBalance = 0;
                    if ($tourPaymentTask->getDueDate() < $now) {
                        $taskItems[$tourPaymentTask->getId()]['overdueAmt'] = $taskItems[$tourPaymentTask->getId()]['overdueAmt'] + ( $total - $credit);
                    }
                }
            }
        }

        foreach($taskItems as $key=>$taskItem){
            $finalOverdueAmt = $finalOverdueAmt + $taskItem['overdueAmt'];
            $final = $final + $taskItem['total'];
            $taskItems[$key]['status'] = 'pending';

            if($taskItem['credit'] >= $taskItem['total'] ) {
                $taskItem['status'] = 'paid';
            } elseif ($taskItem['overdueAmt'] > 0) {
                $taskItems[$key]['status'] = 'overdue';
                $finalStatus = 'overdue';
            }
        }

        //    $items[] = array('task' => $tourPaymentTask, 'status'=>$status, 'overdueAmt' => $overdueAmt, 'credit' => $credit, 'total' => $total);

        if ($collected - $final >= 0) { $finalStatus = 'paid';}
        return array('total' => $final, 'items' => $taskItems, 'finalStatus' => $finalStatus, 'overdueAmt' => $finalOverdueAmt, 'paid' => $collected);
    }

    /**
     * @param tourId
     * @return float total amount Paid for a tour
     */
    public function getTourPaymentsPaid($tourId) {
        $em = $this->em;
        $total = 0;
        $payments = $em->getRepository('PaymentBundle:Payment')->findBy(array('tour' => $tourId));
        foreach($payments as $payment) {
            $total = $total + $payment->getValue();
        }
        return $total;

    }

}