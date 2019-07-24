<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190719141326 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE project SET title='Projet sans titre' WHERE title = NULL OR title = '';"
        );
        // this up() migration is auto-generated, please modify it to your needs
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE project SET title='' WHERE title = 'Projet sans titre';");
    }
}
