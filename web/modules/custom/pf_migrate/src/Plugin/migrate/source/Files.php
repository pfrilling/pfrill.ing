<?php

namespace Drupal\pf_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Source plugin for Files.
 *
 * @MigrateSource(
 *   id = "files"
 * )
 */
class Files extends SqlBase {

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state);
    $this->highWaterProperty = [
      'name' => 'changed',
      'type' => 'int',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {

    $fields = $this->getJoinedTables();
    $query = $this->select('file_managed');

    foreach ($fields as $table => $tableFields) {
      $query->fields($table, array_keys($tableFields));
    }

    $query->condition('uid', 0, '>');
    $query->orderBy('changed');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    $baseFields = $this->getJoinedTables();

    $fields = [];

    foreach ($baseFields as $table => $tableFields) {
      $fields = array_merge($fields, $tableFields);
    }

    $fields['filepath'] = $this->t('Filepath.');
    return $fields;
  }

  /**
   * Return multiple table array of field names.
   */
  private function getJoinedTables(): array {
    $fields['file_managed'] = [
      'fid' => $this->t('File ID'),
      'uuid' => $this->t('Uuid'),
      'langcode' => $this->t('Language code'),
      'uid' => $this->t('User ID'),
      'filename' => $this->t('File name'),
      'uri' => $this->t('The URI of the file'),
      'filemime' => $this->t('File MIME type'),
      'filesize' => $this->t('The file size'),
      'created' => $this->t('File created date UNIX timestamp'),
      'changed' => $this->t('File changed date UNIX timestamp'),
      'status' => $this->t('Status'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'fid' => [
        'type' => 'integer',
      ],
    ];
  }

}
