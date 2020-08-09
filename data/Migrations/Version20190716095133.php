<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190716095133 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    if ($schema->hasTable('stat'))
      $schema->dropTable('stat');

    $table = $schema->createTable('stat');
    $table->addColumn('id',             'integer',  ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('date',           'integer',  ['notnull' => true]);
    $table->addColumn('request_count',  'float',    ['notnull' => true]);
    $table->addColumn('speed_upload',   'float',    ['notnull' => false]);
    $table->addColumn('speed_download', 'float',    ['notnull' => false]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('stat');
  }
}
