<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190611070104 extends AbstractMigration
{
  public function getDescription()
  {
    return 'Создание таблицы устройств';
  }

  public function up(Schema $schema)
  {
    if ($schema->hasTable('device')) {
      $schema->dropTable('device');
    }

    $table = $schema->createTable('device');
    $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('event_id', 'integer', ['notnull' => true]);
    $table->addColumn('name','string', ['length' => 256, 'notnull' => true]);
    $table->addColumn('groups', 'string', ['length' => 256]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('device');
  }
}
