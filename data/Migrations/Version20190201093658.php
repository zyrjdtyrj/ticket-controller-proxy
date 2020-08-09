<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190201093658 extends AbstractMigration
{
  public function getDescription()
  {
    return 'Создание таблицы системных переменных ini_par';
  }

  public function up(Schema $schema)
  {
    if ($schema->hasTable('ini_par'))
      $schema->dropTable('ini_par');

    if ($schema->hasTable('ini_par_history'))
      $schema->dropTable('ini_par_history');

    $table = $schema->createTable('ini_par');
    $table->addColumn('id', 'integer', ['autoincrement' => true]);
    $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
    $table->addColumn('value', 'string', ['notnull' => true, 'length' => 255]);
    $table->addColumn('user', 'string', ['notnull' => false, 'length' => 256]);
    $table->setPrimaryKey(['id']);
    $table->addIndex(['name'], 'name_idx');
    $table->addIndex(['user'], 'user_idx');
    $table->addOption('engine', 'InnoDB');

    $table = $schema->createTable('ini_par_history');
    $table->addColumn('id', 'integer', ['autoincrement' => true]);
    $table->addColumn('user_id', 'integer', ['notnull' => true]);
    $table->addColumn('date', 'integer', ['notnull' => true]);
    $table->addColumn('par_id', 'integer', ['notnull' => true]);
    $table->addColumn('value', 'string', ['notnull' => true, 'length' => 255]);
    $table->addColumn('ip', 'string', ['notnull' => true, 'length' => 16]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function postUp(Schema $schema)
  {
    $this->write('Заполняем данными');

    $iniPars = [
      ['name' => 'server', 'value' => '0'],
      ['name' => 'proxyMode', 'value' => 'online'],
      ['name' => 'proxyId', 'value' => 'PROXY'],
    ];

    foreach ($iniPars as $iniPar) {
      $this->addSql('insert into ini_par (`name`, `value`) values (:name, :value)', $iniPar);
    }
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('ini_par');
    $schema->dropTable('ini_par_history');
  }
}
