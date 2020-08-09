<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190613181300 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    if ($schema->hasTable('history'))
      $schema->dropTable('history');

    $table = $schema->createTable('history');
    $table->addColumn('id',         'integer',  ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('date',       'integer',  ['notnull' => true]);
    $table->addColumn('ticket_id',  'integer',  ['notnull' => false]);
    $table->addColumn('device',     'string',   ['notnull' => false]);
    $table->addColumn('ip',         'string',   ['length' => 256, 'notnull' => false]);
    $table->addColumn('status',     'string',   ['length' => 256, 'notnull' => true]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('history');
  }
}
