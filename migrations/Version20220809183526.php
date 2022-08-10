<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220809183526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO user
                (id, username, is_enabled, password, roles, created_at, updated_at)
            VALUES
                (1, 'admin@cokuygun.com', true, '$2y$13\$ZTQaebFtlgUAfejyELwaqOJkS3dyEpkDMN2XFxpGCIx5TJBpHSUW.', '[\"ROLE_ADMIN\"]', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."'),
                (2, 'user@cokuygun.com', true, '$2y$13\$ZTQaebFtlgUAfejyELwaqOJkS3dyEpkDMN2XFxpGCIx5TJBpHSUW.', '[\"ROLE_USER\"]', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."')
        ;");

        $this->addSql("INSERT INTO order_state
                (id, name, slug_name, created_at, updated_at)
            VALUES
                (1, 'Siparişiniz Alındı','received', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."'),
                (2, 'Siparişiniz Yolda','onTheWay', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."'),
                (3, 'Siparişiniz İptal Edildi','canceled', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."'),
                (4, 'Siparişiniz Teslim Edilemedi','notDelivered', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."'),
                (5, 'Siparişiniz Teslim Edildi','complete', '".(new \DateTime())->format("Y-m-d H:i:s")."', '".(new \DateTime())->format("Y-m-d H:i:s")."')
        ;");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
