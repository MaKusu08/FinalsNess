<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119044113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       // $this->addSql('DROP TABLE staff_movies');
        //$this->addSql('DROP TABLE staff_rooms');
       // $this->addSql('ALTER TABLE admin_movies ADD created_by_id INT NOT NULL');
       // $this->addSql('ALTER TABLE admin_movies ADD CONSTRAINT FK_9BD6DCC9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
       // $this->addSql('CREATE INDEX IDX_9BD6DCC9B03A8386 ON admin_movies (created_by_id)');
        //$this->addSql('ALTER TABLE admin_rooms ADD created_by_id INT NOT NULL');
       // $this->addSql('ALTER TABLE admin_rooms ADD CONSTRAINT FK_DC8BE16CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
       // $this->addSql('CREATE INDEX IDX_DC8BE16CB03A8386 ON admin_rooms (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staff_movies (id INT AUTO_INCREMENT NOT NULL, movie_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, movie_description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, movie_image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, movie_duration VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, movie_price DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE staff_rooms (id INT AUTO_INCREMENT NOT NULL, room_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, capacity INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE admin_movies DROP FOREIGN KEY FK_9BD6DCC9B03A8386');
        $this->addSql('DROP INDEX IDX_9BD6DCC9B03A8386 ON admin_movies');
        $this->addSql('ALTER TABLE admin_movies DROP created_by_id');
        $this->addSql('ALTER TABLE admin_rooms DROP FOREIGN KEY FK_DC8BE16CB03A8386');
        $this->addSql('DROP INDEX IDX_DC8BE16CB03A8386 ON admin_rooms');
        $this->addSql('ALTER TABLE admin_rooms DROP created_by_id');
    }
}
