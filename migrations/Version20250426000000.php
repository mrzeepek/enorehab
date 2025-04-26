<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale pour créer les tables nécessaires
 */
final class Version20250426000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables enorehab_contacts et enorehab_ebook_subscribers';
    }

    public function up(Schema $schema): void
    {
        // Création de la table enorehab_contacts si elle n'existe pas déjà
        $this->addSql('CREATE TABLE IF NOT EXISTS enorehab_contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            instagram VARCHAR(100) DEFAULT NULL,
            submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45) DEFAULT NULL,
            status VARCHAR(20) DEFAULT \'pending\',
            notes TEXT DEFAULT NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Création de la table enorehab_ebook_subscribers si elle n'existe pas déjà
        $this->addSql('CREATE TABLE IF NOT EXISTS enorehab_ebook_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            download_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45) DEFAULT NULL,
            consent TINYINT(1) DEFAULT 1,
            mail_list TINYINT(1) DEFAULT 1,
            UNIQUE KEY unique_email (email)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // Ne pas supprimer les tables en cas de rollback
        // car elles peuvent contenir des données importantes
        // Si besoin, cette méthode pourrait être implémentée plus tard
        $this->addSql('-- Ne pas supprimer les tables pour éviter la perte de données');
    }
}