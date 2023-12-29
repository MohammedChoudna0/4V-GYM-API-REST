<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231228212439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monitor_activity DROP FOREIGN KEY FK_7647DDAE4CE1C902');
        $this->addSql('ALTER TABLE monitor_activity DROP FOREIGN KEY FK_7647DDAE81C06096');
        $this->addSql('DROP TABLE monitor_activity');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE monitor_activity (monitor_id INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_7647DDAE4CE1C902 (monitor_id), INDEX IDX_7647DDAE81C06096 (activity_id), PRIMARY KEY(monitor_id, activity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE monitor_activity ADD CONSTRAINT FK_7647DDAE4CE1C902 FOREIGN KEY (monitor_id) REFERENCES monitor (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE monitor_activity ADD CONSTRAINT FK_7647DDAE81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
