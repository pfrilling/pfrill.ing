<?php

declare(strict_types = 1);

namespace Drupal\pf_migrate\Plugin\migrate\source;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source plugin for Tags taxonomy terms.
 *
 * @MigrateSource(
 *   id = "tags"
 * )
 */
class Tags extends SqlBase {

  /**
   * The vocabulary name to migrate.
   *
   * @var string
   */
  protected string $vocabulary;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MigrationInterface $migration,
    StateInterface $state
  )
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state);
    $this->highWaterProperty = [
      'name' => 'changed',
      'type' => 'int',
    ];

    // Set the vocabulary name.
    $this->vocabulary = 'tags';
  }

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $fields = $this->getJoinedTables();

    $query = $this->select('taxonomy_term_data', 'taxonomy_term_data');

    foreach ($fields as $table => $tableFields) {
      $query->fields($table, array_keys($tableFields));
    }

    $query->join('taxonomy_term_field_data', 'taxonomy_term_field_data', 'taxonomy_term_data.tid = taxonomy_term_field_data.tid');

    $query->condition('taxonomy_term_data.vid', $this->vocabulary, '=');

    $query->orderBy('taxonomy_term_field_data.changed');
    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields(): array {
    $baseFields = $this->getJoinedTables();

    $fields = [];

    foreach ($baseFields as $table => $tableFields) {
      $fields = array_merge($fields, $tableFields);
    }

    return $fields;
  }

  /**
   * Get the joined tables.
   *
   * @return array
   *   Associative array of fields keyed by table.
   */
  private function getJoinedTables(): array {
    $fields['taxonomy_term_data'] = [
      'tid' => $this->t('Term ID'),
      'uuid' => $this->t('Uuid')
    ];

    $fields['taxonomy_term_field_data'] = [
      'revision_id' => $this->t('Revision id'),
      'vid' => $this->t('Vocabulary id'),
      'langcode' => $this->t('Language code'),
      'name' => $this->t('Term name'),
      'description__value' => $this->t('Description value'),
      'description__format' => $this->t('Description format'),
      'weight' => $this->t('Weight'),
      'changed' => $this->t('Changed timestamp'),
      'default_langcode' => $this->t('Default language code'),
      'status' => $this->t('Published status'),
      'revision_translation_affected' => $this->t('Revision translation affected'),
    ];

    return $fields;
  }

  /**
   * Get the ids for the migration.
   *
   * @return \string[][]
   */
  public function getIds(): array {
    return [
      'tid' => [
        'type' => 'integer',
      ],
    ];
  }
}
