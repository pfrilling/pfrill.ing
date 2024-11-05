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

    $this->entityStorage = \Drupal::entityTypeManager()->getStorage($this->entityType);
  }

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $query = $this->select('url_alias')
      ->fields('url_alias', array_keys($this->fields()));

    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields(): array {
    return [
      'pid' => $this->t('The alias id'),
      'source' => $this->t('The source entity'),
      'alias' => $this->t('The alias for the entity'),
      'langcode' => $this->t('The language code'),
    ];
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
    $parent = parent::prepareRow($row);
    $row->setSourceProperty('entity_id', NULL);

    $source = $row->getSourceProperty('source');
    $parts = array_filter(explode('/', $source));
    $entityId = (int) array_pop($parts);

    $row->setSourceProperty('entity_id', $entityId);
    //$row->setSourceProperty('entity_type', $entityType);

    return $parent;
  }

}
