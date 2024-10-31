<?php

declare(strict_types = 1);

namespace Drupal\pf_migrate\Plugin\migrate\source;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source plugin for User accounts.
 *
 * @MigrateSource(
 *   id = "users"
 * )
 */
class Users extends SqlBase {

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
      'name' => 'access',
      'type' => 'int',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $fields = $this->getJoinedTables();

    $query = $this->select('users', 'users');

    foreach ($fields as $table => $tableFields) {
      $query->fields($table, array_keys($tableFields));
    }

    $query->join('users_field_data', 'users_field_data', 'users_field_data.uid = users.uid');

    $query->condition('users.uid', 0, '>');

    $query->orderBy('access');
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
    $fields['users'] = [
      'uid' => $this->t('User ID'),
      'uuid' => $this->t('Uuid')
    ];

    $fields['users_field_data'] = [
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'created' => $this->t('Account created date UNIX timestamp'),
      'access' => $this->t('Last access UNIX timestamp'),
      'login' => $this->t('Last login UNIX timestamp'),
      'status' => $this->t('Blocked/Allowed'),
      'timezone' => $this->t('Timeone offset'),
      'init' => $this->t('Initial email address used at registration'),
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
      'uid' => [
        'type' => 'integer',
      ],
    ];
  }
}
