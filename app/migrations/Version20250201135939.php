<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201135939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blocked_slots DROP FOREIGN KEY FK_7F35742C59E5119C');
        $this->addSql('DROP TABLE blocked_slots');
        $this->addSql('ALTER TABLE cars DROP INDEX IDX_95C71D147E3C61F9, ADD UNIQUE INDEX UNIQ_95C71D147E3C61F9 (owner_id)');
        $this->addSql('ALTER TABLE cars ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reservations DROP INDEX UNIQ_4DA239A76ED395, ADD INDEX IDX_4DA239A76ED395 (user_id)');
        $this->addSql('ALTER TABLE reservations DROP INDEX UNIQ_4DA239C3C6F69F, ADD INDEX IDX_4DA239C3C6F69F (car_id)');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA23959E5119C');
        $this->addSql('DROP INDEX IDX_4DA23959E5119C ON reservations');
        $this->addSql('ALTER TABLE reservations ADD parking_space_id INT NOT NULL, ADD date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', DROP slot_id, DROP day, CHANGE car_id car_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA23945DF8272 FOREIGN KEY (parking_space_id) REFERENCES parking_spaces (id)');
        $this->addSql('CREATE INDEX IDX_4DA23945DF8272 ON reservations (parking_space_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blocked_slots (id INT AUTO_INCREMENT NOT NULL, slot_id INT DEFAULT NULL, day DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', UNIQUE INDEX UNIQ_7F35742C59E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE blocked_slots ADD CONSTRAINT FK_7F35742C59E5119C FOREIGN KEY (slot_id) REFERENCES parking_spaces (id)');
        $this->addSql('ALTER TABLE cars DROP INDEX UNIQ_95C71D147E3C61F9, ADD INDEX IDX_95C71D147E3C61F9 (owner_id)');
        $this->addSql('ALTER TABLE cars DROP name');
        $this->addSql('ALTER TABLE reservations DROP INDEX IDX_4DA239A76ED395, ADD UNIQUE INDEX UNIQ_4DA239A76ED395 (user_id)');
        $this->addSql('ALTER TABLE reservations DROP INDEX IDX_4DA239C3C6F69F, ADD UNIQUE INDEX UNIQ_4DA239C3C6F69F (car_id)');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA23945DF8272');
        $this->addSql('DROP INDEX IDX_4DA23945DF8272 ON reservations');
        $this->addSql('ALTER TABLE reservations ADD slot_id INT DEFAULT NULL, ADD day DATE NOT NULL, DROP parking_space_id, DROP date, CHANGE car_id car_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA23959E5119C FOREIGN KEY (slot_id) REFERENCES parking_spaces (id)');
        $this->addSql('CREATE INDEX IDX_4DA23959E5119C ON reservations (slot_id)');
    }
}
