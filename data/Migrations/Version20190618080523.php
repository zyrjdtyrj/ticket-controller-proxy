<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190618080523 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    if ($schema->hasTable('log'))
      $schema->dropTable('log');

    $table = $schema->createTable('log');
    $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('date', 'integer', ['notnull' => true]);
    $table->addColumn('device', 'string', ['notnull' => true]);
    $table->addColumn('ip', 'string', ['notnull' => true]);
    $table->addColumn('method', 'string', ['notnull' => true]);
    $table->addColumn('params', 'text', ['notnull' => false]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');

  }

  public function down(Schema $schema)
  {
    $schema->dropTable('log');

  }
}
