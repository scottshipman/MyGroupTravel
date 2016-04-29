<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeploymentCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this
      ->setName('toolkit:deployment')
      ->setDescription('Runs a deployment task')
      ->addArgument('id', InputArgument::REQUIRED, 'The ID to run against')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $task_id = $input->getArgument('id');

    $output->writeln('=============================================');
    $output->writeln('Deployment task: ' . $task_id);
    $output->writeln('=============================================');

    switch ($task_id) {
      case 'TOOL-405':
        $em = $this->getContainer()->get('doctrine')->getManager();
        $tours = $em->getRepository('TourBundle:Tour')->findAll();

        if (!empty($tours)) {
          $output->writeln('The following redirects need manually creating:');
          foreach ($tours as $tour) {
            $output->writeln('RewriteRule ^/tour/view/' . $tour->getId() . '$ /tour/view/' . $tour->getId() . '/' . $tour->getQuoteNumber() . ' [L,R=301]');
          }
        }
        break;
    }
  }
}