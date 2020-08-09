<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190717070718 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    if ($schema->hasTable('server'))
      $schema->dropTable('server');

    $table = $schema->createTable('server');
    $table->addColumn('id',       'integer',  ['autoincrement' => true, 'notnull' => true]);
    $table->addColumn('name',     'string',   ['length' => 256, 'notnull' => true]);
    $table->addColumn('address',  'string',   ['length' => 256, 'notnull' => true]);
    $table->setPrimaryKey(['id']);
    $table->addOption('engine', 'InnoDB');
  }

  public function postUp(Schema $schema)
  {
    $this->write('Заполняем данными');

    $iniPars = [
      ['name' => 'Тестовый',          'address' => 'http://dev.p.greenwaystart.com:8080/api/Tickets'],
      ['name' => 'Боевой (без SSL)',  'address' => 'http://p.greenwaystart.com/api/Tickets'],
      ['name' => 'Боевой (c SSL)',    'address' => 'https://p.greenwaystart.com/api/Tickets'],
    ];

    foreach ($iniPars as $iniPar) {
      $this->addSql('insert into server (`name`, `address`) values (:name, :value)', $iniPar);
    }
  }

  public function down(Schema $schema)
  {
    $schema->dropTable('server');
  }
}
