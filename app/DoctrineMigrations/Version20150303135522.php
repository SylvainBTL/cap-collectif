<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150303135522 extends AbstractMigration implements ContainerAwareInterface
{
    protected $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $this->resetFeatures();

    }


    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function postDown(Schema $schema)
    {
        $this->resetFeatures();
    }

    private function resetFeatures()
    {
        $toggleManager = $this->container->get('capco.toggle.manager');

        $toggleManager->activate('blog');
        $toggleManager->activate('calendar');
        $toggleManager->activate('newsletter');
        $toggleManager->activate('ideas');
        $toggleManager->activate('themes');
        $toggleManager->activate('registration');
        $toggleManager->activate('login_facebook');
        $toggleManager->activate('login_gplus');
        $toggleManager->deactivate('shield_mode');
    }


}
