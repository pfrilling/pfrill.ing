<?php

declare(strict_types = 1);

namespace Drupal\pf_migrate\Plugin\migrate\source;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Source plugin for UrlAlias redirects.
 *
 * @MigrateSource(
 *   id = "url_redirects"
 * )
 */
class UrlRedirects extends SqlBase {

  /**
   * The type of entity to migrate.
   *
   * @var string
   */
  protected string $queryType = 'node';

  /**
   * The type of entity to migrate.
   *
   * @var string
   */
  protected string $entityType = 'node';

  /**
   * Entity storage interface.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

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
    if (!empty($configuration['entity_type'])) {
      $this->entityType = $configuration['entity_type'];
    }

    if (!empty($configuration['query_type'])) {
      $this->queryType = $configuration['query_type'];
    }

    $this->entityStorage = \Drupal::entityTypeManager()->getStorage($this->entityType);
  }

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $fields = $this->getJoinedTables();

    $query = $this->select('url_alias', 'url_alias');

    foreach ($fields as $table => $tableFields) {
      $query->fields($table, array_keys($tableFields));
    }

    $query->condition('url_alias.source', "/{$query->escapeLike($this->queryType)}%", 'LIKE');

    $query->orderBy('url_alias.pid');

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

    $fields['computed_entity_id'] = $this->t('The entity id parsed from the source uri');
    $fields['destination_source_alias'] = $this->t('The alias of the Drupal 9 entity');

    return $fields;
  }

  /**
   * Get the joined tables.
   *
   * @return array
   *   Associative array of fields keyed by table.
   */
  private function getJoinedTables(): array {
    $fields['url_alias'] = [
      'pid' => $this->t('Path ID'),
      'source' => $this->t('Source path'),
      'alias' => $this->t('Alias path'),
      'langcode' => $this->t('Langcode'),
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
      'pid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    parent::prepareRow($row);

    $originalAlias = $row->getSourceProperty('alias');
    $row->setSourceProperty('alias', ltrim($originalAlias, '/'));

    $sourceUrl = $row->getSourceProperty('source');

    $sourceParts = explode('/', ltrim($sourceUrl, '/'));

    $entityId = array_pop($sourceParts);
    $type = array_shift($sourceParts);

    if (!is_numeric($entityId)) {
      return FALSE;
    }

    if ($type !== $this->queryType) {
      return FALSE;
    }

    /** @var EntityInterface $entity */
    $entity = $this->entityStorage->load($entityId);

    if (empty($entity)) {
      return FALSE;
    }

    if ($originalAlias === $entity->toUrl()->toString()) {
      return FALSE;
    }

    $row->setSourceProperty('destination_source_alias', "entity:{$entity->getEntityTypeId()}/{$entity->id()}");
  }

}
