<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241028160601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'First init';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE developer (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, job VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE project (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, client VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE project_developer (project_id INT NOT NULL, developer_id INT NOT NULL, PRIMARY KEY(project_id, developer_id))');
        $this->addSql('CREATE INDEX IDX_74C7CE4D166D1F9C ON project_developer (project_id)');
        $this->addSql('CREATE INDEX IDX_74C7CE4D64DD9267 ON project_developer (developer_id)');
        $this->addSql('ALTER TABLE project_developer ADD CONSTRAINT FK_74C7CE4D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_developer ADD CONSTRAINT FK_74C7CE4D64DD9267 FOREIGN KEY (developer_id) REFERENCES developer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project_developer DROP CONSTRAINT FK_74C7CE4D166D1F9C');
        $this->addSql('ALTER TABLE project_developer DROP CONSTRAINT FK_74C7CE4D64DD9267');
        $this->addSql('DROP TABLE developer');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_developer');
    }
}
