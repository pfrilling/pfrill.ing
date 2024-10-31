<?php

declare(strict_types = 1);

namespace Drupal\pf_migrate\Plugin\migrate\source;

use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source plugin for Client tags taxonomy terms.
 *
 * @MigrateSource(
 *   id = "client_tags"
 * )
 */
class ClientTags extends Tags {

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

    // Set the vocabulary name.
    $this->vocabulary = 'client_tags';
  }
}
