<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201105102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blocked_slots (id INT AUTO_INCREMENT NOT NULL, slot_id INT DEFAULT NULL, day DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', UNIQUE INDEX UNIQ_7F35742C59E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cars (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, licence_plate VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_95C71D147E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parking_spaces (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, car_id INT NOT NULL, slot_id INT DEFAULT NULL, day DATE NOT NULL, UNIQUE INDEX UNIQ_4DA239A76ED395 (user_id), UNIQUE INDEX UNIQ_4DA239C3C6F69F (car_id), INDEX IDX_4DA23959E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blocked_slots ADD CONSTRAINT FK_7F35742C59E5119C FOREIGN KEY (slot_id) REFERENCES parking_spaces (id)');
        $this->addSql('ALTER TABLE cars ADD CONSTRAINT FK_95C71D147E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239C3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id)');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA23959E5119C FOREIGN KEY (slot_id) REFERENCES parking_spaces (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blocked_slots DROP FOREIGN KEY FK_7F35742C59E5119C');
        $this->addSql('ALTER TABLE cars DROP FOREIGN KEY FK_95C71D147E3C61F9');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239A76ED395');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239C3C6F69F');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA23959E5119C');
        $this->addSql('DROP TABLE blocked_slots');
        $this->addSql('DROP TABLE cars');
        $this->addSql('DROP TABLE parking_spaces');
        $this->addSql('DROP TABLE reservations');
    }
}
