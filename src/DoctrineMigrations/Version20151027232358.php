<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151027232358 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->connection->update('section', ['type' => 'projects'], ['type' => 'consultations']);
    }

    public function down(Schema $schema): void
    {
        $this->connection->update('section', ['type' => 'consultations'], ['type' => 'projects']);
    }
}
