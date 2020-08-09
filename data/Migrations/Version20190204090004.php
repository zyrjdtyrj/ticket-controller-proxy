<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190204090004 extends AbstractMigration
{
  public function getDescription()
  {
    return 'Создание таблицы списка мероприятий';
  }

  public function up(Schema $schema)
  {
    // сами мероприятия
    if ($schema->hasTable('event'))
      $schema->dropTable('event');

    $table = $schema->createTable('event');
    $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('name', 'string', ['notnull' => true, 'length' => 256]);
    $table->addColumn('server_id', 'integer', ['notnull' => true]);
    $table->addColumn('event_id', 'integer', ['notnull' => true]);
    $table->addColumn('date_begin', 'integer', ['notnull' => false]);
    $table->addColumn('date_end', 'integer', ['notnull' => false]);
    $table->addColumn('sync_time', 'integer', ['length' => 13, 'default' => null, 'notnull' => false]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');

    // группы доступа на мероприятие
    if ($schema->hasTable('event_group'))
      $schema->dropTable('event_group');

    $table = $schema->createTable('event_group');
    $table->addColumn('id',      'integer', ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('group_id','string',  ['length' => 256, 'notnull' => true]);
    $table->addColumn('name',    'string',  ['length' => 256, 'notnull' => false]);
    $table->addColumn('event_id','integer', ['notnull' => true]);
    $table->addColumn('color',   'string',  ['length' => 256, 'notnull' => true]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');

    // уровень доступа группы
    if ($schema->hasTable('event_group_allow'))
      $schema->dropTable('event_group_allow');

    $table = $schema->createTable('event_group_allow');
    $table->addColumn('id',             'integer', ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('event_group_id', 'integer', ['notnull' => true]);
    $table->addColumn('allow',          'string',  ['length' => 256, 'notnull' => true]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');

    // места посадки группы
    if ($schema->hasTable('event_group_places'))
      $schema->dropTable('event_group_places');

    $table = $schema->createTable('event_group_places');
    $table->addColumn('id',             'integer', ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('event_group_id', 'integer', ['notnull' => true]);
    $table->addColumn('s',              'integer', ['notnull' => true]);
    $table->addColumn('r',              'integer', ['notnull' => true]);
    $table->addColumn('f',              'integer', ['notnull' => true]);
    $table->addColumn('t',              'integer', ['notnull' => true]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('event');
    $schema->dropTable('event_group');
    $schema->dropTable('event_group_allow');
    $schema->dropTable('event_group_place');
  }
}
