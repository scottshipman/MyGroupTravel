<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151209101700 extends AbstractMigration implements ContainerAwareInterface
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
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user ADD roles_string VARCHAR(255) DEFAULT NULL');


    }

    public function postUp(Schema $schema) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $users = $em->getRepository('TUIToolkitUserBundle:User')->findAll();
        foreach($users as $user){
            $user->setRolesString(implode(', ', $user->getRoles()));
            $em->persist($user);
        }
        $em->flush();

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user DROP roles_string');
    }
}
