<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190207081501 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    if ($schema->hasTable('ticket')) {
      $schema->dropTable('ticket');
    }

    // таблица TICKET
    $table = $schema->createTable('ticket');
    $table->addColumn('id', 'integer', ['autoincrement' => false, 'notnull' => true]); // идентификатор билета
    $table->addColumn('event_id', 'integer'); // идентификатор события
    $table->addColumn('fio', 'text', ['notnull' => false]); // ФИО гостя
    $table->addColumn('unumber', 'string', ['length' => 256, 'notnull' => false]); // идентификатор гостя в системе
    $table->addColumn('phone', 'string', ['length' => 100, 'notnull' => false]); // телефон
    $table->addColumn('city', 'string', ['length' => 256, 'notnull' => false]); // город
    $table->addColumn('onumber', 'integer', ['length' => 10, 'notnull' => false]); // номер заказа
    $table->addColumn('ostatus', 'string', ['length' => 256, 'notnull' => false]); // статус заказа
    $table->addColumn('type', 'string', ['length' => 2, 'notnull' => false]); // тип билета
    $table->addColumn('groups', 'string', ['length' => 256, 'notnull' => false]);
    $table->addColumn('place', 'string', ['length' => 256, 'notnull' => false]); // название места
    $table->addColumn('status', 'string', ['length' => 256, 'notnull' => false]);
    $table->addColumn('modified_time', 'integer', ['notnull' => false]);
    $table->addColumn('sync_time', 'integer', ['default' => null, 'notnull' => false]);
    $table->addColumn('log', 'text', ['notnull' => false]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('ticket');

  }
}
