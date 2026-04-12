<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119054303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_movies ADD CONSTRAINT FK_9BD6DCC9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9BD6DCC9B03A8386 ON admin_movies (created_by_id)');
        $this->addSql('ALTER TABLE admin_rooms ADD created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE admin_rooms ADD CONSTRAINT FK_DC8BE16CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_DC8BE16CB03A8386 ON admin_rooms (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_movies DROP FOREIGN KEY FK_9BD6DCC9B03A8386');
        $this->addSql('DROP INDEX IDX_9BD6DCC9B03A8386 ON admin_movies');
        $this->addSql('ALTER TABLE admin_rooms DROP FOREIGN KEY FK_DC8BE16CB03A8386');
        $this->addSql('DROP INDEX IDX_DC8BE16CB03A8386 ON admin_rooms');
        $this->addSql('ALTER TABLE admin_rooms DROP created_by_id');
    }
}
