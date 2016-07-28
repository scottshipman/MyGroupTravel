<?php
/*
 * DataGridBundle documentation:
 * https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/summary.md
 */

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\QuoteBundle\Controller\QuoteVersionController;
use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\PermissionBundle\Entity\Permission;

/**
 * Quote controller.
 *
 */
class QuoteController extends Controller
{

    /**
     * Finds and displays a Quote entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
        $quoteReference = $quoteVersion->getQuoteReference();
        $entity = $em->getRepository('QuoteBundle:Quote')->find($quoteReference);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Quote entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $isTemplate = $entity->getIsTemplate();

        return $this->render('QuoteBundle:Quote:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            'isTemplate' => $isTemplate,
        ));
    }

    /**
     * Deletes a Quote entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $entity = $em->getRepository('QuoteBundle:Quote')->find($id);

          if (!$entity) {
            throw $this->createNotFoundException('Unable to find Quote entity.');
          }

          $em->remove($entity);
          $em->flush();
          $this->get('session')
            ->getFlashBag()
            ->add('notice', $this->get('translator')
                ->trans('quote.flash.delete') . $entity->getName());
        }
        return $this->redirect($this->generateUrl('manage_quote'));
    }

    /**
     * Creates a form to delete a Quote entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_quote_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('quote.actions.delete')))
            ->getForm();
    }


  public function retrieve_organizers_nameAction(Request $request)
    {
        $term = $request->get('term', null);
        $role = '%ROLE_CUSTOMER%';

        $choices = array();
        $term = str_replace(array('<', '>'), '', $term);
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder('TUIToolkitUserBundle:User');
        $qb->select('u')
            ->from('TUIToolkitUserBundle:User', 'u')
            ->where(
                $qb->expr()->like('CONCAT(u.firstName, \' \', u.lastName, \' \', u.email)', ':term')
            )
            ->andWhere('u.roles LIKE :role')
            ->setParameters(array('role' => $role, 'term' => '%' . $term . '%'))
            ->orderBy('u.firstName', 'ASC');
        $query = $qb->getQuery();
        $organizers = $query->getArrayResult();
        foreach ($organizers as $organizer) {
            $choices[] = array(
                'label' => $organizer['firstName'] . " " . $organizer['lastName'] . ' <' . $organizer['email'].'>',
                'value' => $organizer['firstName'] . " " . $organizer['lastName'] . ' <' . $organizer['email'].'>',
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($choices));

        return $response;
    }



    /*
     * @todo error handling for non values
     *
     *
     */
    public function retrieve_salesagent_nameAction(Request $request)
    {
        $term = $request->get('term', null);
        $role = '%ROLE_BRAND%';

        $choices = array();
        $term = str_replace(array('<', '>'), '', $term);
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder('TUIToolkitUserBundle:User');
        $qb->select('u')
            ->from('TUIToolkitUserBundle:User', 'u')
            ->where(
                $qb->expr()->like('CONCAT(u.firstName, \' \', u.lastName, \' \', u.email)', ':term')
            )
            ->andWhere('u.roles LIKE :role')
            ->setParameters(array('role' => $role, 'term' => '%' . $term . '%'))
            ->orderBy('u.firstName', 'ASC');
        $query = $qb->getQuery();
        $agents = $query->getArrayResult();
        foreach ($agents as $agent) {
            $choices[] = array(
                'label' => $agent['firstName'] . " " . $agent['lastName'] . ' <' . $agent['email'].'>',
                'value' => $agent['firstName'] . " " . $agent['lastName'] . ' <' . $agent['email'].'>',
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($choices));

        return $response;
    }

    public function retrieve_institution_nameAction(Request $request)
    {
        $term = $request->get('term', null);

        $choices = array();
        $term = str_replace('- ', '', $term);
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder('InstitutionBundle:Institution');
        $qb->select('i')
            ->from('InstitutionBundle:Institution', 'i')
            ->where(
                $qb->expr()->like('CONCAT(i.name, \' \', i.city)', ':term')
            )
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('i.name', 'ASC');
        $query = $qb->getQuery();
        $institutions = $query->getArrayResult();
        foreach ($institutions as $institution) {
            $choices[] = array(
                'label' => $institution['name']. ' - ' . $institution['city'],
                'value' => $institution['name']. ' - ' . $institution['city'],

            );
        }


        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($choices));

        return $response;
    }

  /**
   * Clone Content Blocks for a QuoteVersion
   *
   */

  public function cloneContentBlocks($content = array())
  {
    $newContentArray = array();
    if (!empty($content) && $content != NULL) {
      foreach ($content as $tab => $blocks) {
        foreach ($blocks[1] as $block) { // block should be an ID number
          $em = $this->getDoctrine()->getManager();

          $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

          if (!$originalBlock) {
            throw $this->createNotFoundException('Unable to find Content entity while cloning.');
          }

          $newBlock = clone $originalBlock;
          $newBlock->setId(null);
          $em->persist($newBlock);
          $em->flush($newBlock);

          $newContentArray[$tab][0] = $blocks[0];
          $newContentArray[$tab][1][] = $newBlock->getID();
        }
      }
    }

    return $newContentArray;
  }

  /**
   * Clone Header Block for a QuoteVersion
   *
   */

  public function cloneHeaderBlock($block)
  {
    $result = NULL;
    if (!empty($block) && $block != NULL) {
      $em = $this->getDoctrine()->getManager();

      $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

      if (!$originalBlock) {
        throw $this->createNotFoundException('Unable to find Content entity while cloning the header.');
      }

      $newBlock = clone $originalBlock;
      $newBlock->setId(null);
      $em->persist($newBlock);
      $em->flush($newBlock);
      $result = $newBlock;
    }

    return $result;
  }

}