<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250718163013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP CONSTRAINT fk_23a0e66a76ed395');
        $this->addSql('DROP INDEX idx_23a0e66a76ed395');
        $this->addSql('ALTER TABLE article RENAME COLUMN user_id TO author_id');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES appuser (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66F675F31B');
        $this->addSql('DROP INDEX IDX_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article RENAME COLUMN author_id TO user_id');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT fk_23a0e66a76ed395 FOREIGN KEY (user_id) REFERENCES appuser (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_23a0e66a76ed395 ON article (user_id)');
    }
}
