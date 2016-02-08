<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/**
 * Create test user
 */
class Version20160208101955 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $enc = new BCryptPasswordEncoder(10);
        $p = $enc->encodePassword('Au123456', null);
        $this->addSql("INSERT INTO user
        (`username`, `password`, `first_name`, `last_name`, `role`, `email`, `email_is_verify`) VALUES
        ('testuser', '{$p}', 'DemoUser', 'DemoUser', 0, 'asd@qwe.ru', 1)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
