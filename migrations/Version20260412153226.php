<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260412153226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_log (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, role VARCHAR(50) NOT NULL, action VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE admin_movies (id INT AUTO_INCREMENT NOT NULL, movie_name VARCHAR(255) NOT NULL, movie_description VARCHAR(255) NOT NULL, movie_image VARCHAR(255) NOT NULL, movie_duration VARCHAR(255) NOT NULL, movie_price DOUBLE PRECISION NOT NULL, created_by_id INT NOT NULL, INDEX IDX_9BD6DCC9B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE admin_reservations (id INT AUTO_INCREMENT NOT NULL, customer_name VARCHAR(255) NOT NULL, contact_number VARCHAR(255) NOT NULL, reservation_date DATETIME NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, guests INT NOT NULL, total_amount DOUBLE PRECISION NOT NULL, payment_status VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE admin_rooms (id INT AUTO_INCREMENT NOT NULL, room_name VARCHAR(255) NOT NULL, capacity INT NOT NULL, status VARCHAR(255) NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_DC8BE16CB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE admin_movies ADD CONSTRAINT FK_9BD6DCC9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE admin_rooms ADD CONSTRAINT FK_DC8BE16CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_movies DROP FOREIGN KEY FK_9BD6DCC9B03A8386');
        $this->addSql('ALTER TABLE admin_rooms DROP FOREIGN KEY FK_DC8BE16CB03A8386');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('DROP TABLE admin_movies');
        $this->addSql('DROP TABLE admin_reservations');
        $this->addSql('DROP TABLE admin_rooms');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
