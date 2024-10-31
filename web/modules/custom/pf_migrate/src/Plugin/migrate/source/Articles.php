<?php

declare(strict_types = 1);

namespace Drupal\pf_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Source plugin for Blog to Article migration.
 *
 * @MigrateSource(
 *   id = "articles"
 * )
 */
class Articles extends SqlBase {

  const PHP_REGEX = "/\<\?php((\n|.)*?)\?\>/";
  const BASH_REGEX = "/\<bash\>((\n|.)*?)\<\/bash\>/";

  const GROUP_CONCAT_TABLES = [
    'node__field_tags',
    'node__field_client_tags',
    'node__field_resources',
  ];

  /**
   * The node bundle to query.
   *
   * @var string
   */
  protected string $bundle;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state);
    $this->highWaterProperty = [
      'name' => 'changed',
      'type' => 'int',
    ];

    $this->bundle = 'blog';
  }

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = $this->getJoinedTables();
    $query = $this->select('node');

    foreach ($fields as $table => $tableFields) {
      if (!in_array($table, self::GROUP_CONCAT_TABLES)) {
        $query->fields($table, array_keys($tableFields));
        foreach (array_keys($tableFields) as $fieldName) {
          $query->groupBy($fieldName);
        }

      }

      if ($table === 'node') {
        continue;
      }

      switch ($table) {
        case 'node_field_data':
          $query->innerJoin($table, $table, "{$table}.nid = node.nid && {$table}.vid = node.vid");
          break;

        case 'node__field_tags':
          $query->leftJoin($table, $table, $table . '.entity_id = node.nid && ' . $table . '.bundle = :bundle', [':bundle' => $this->bundle]);
          $query->addExpression("GROUP_CONCAT(DISTINCT {$table}.field_tags_target_id)", "field_tags_list");
          break;

        case 'node__field_client_tags':
          $query->leftJoin($table, $table, $table . '.entity_id = node.nid && ' . $table . '.bundle = :bundle', [':bundle' => $this->bundle]);
          $query->addExpression("GROUP_CONCAT(DISTINCT {$table}.field_client_tags_target_id)", "field_client_tags_list");
          break;

        case 'node__field_resources':
          $query->leftJoin($table, $table, $table . '.entity_id = node.nid && ' . $table . '.bundle = :bundle', [':bundle' => $this->bundle]);
          $query->addExpression("GROUP_CONCAT(DISTINCT {$table}.field_resources_target_id)", "field_resources_target_id_list");
          break;

        case 'node__field_header_image':
          $query->leftJoin($table, $table, $table . '.entity_id = node.nid && ' . $table . '.bundle = :bundle', [':bundle' => $this->bundle]);
          break;

        default:
          $query->innerJoin($table, $table, $table . '.entity_id = node.nid && ' . $table . '.bundle = :bundle', [':bundle' => $this->bundle]);
          break;
      }
    }

    $query->condition('node.type', $this->bundle, '=');
    $query->orderBy('node_field_data.changed');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    $baseFields = $this->getJoinedTables();

    $fields = [];

    foreach ($baseFields as $table => $tableFields) {
      if (in_array($table, self::GROUP_CONCAT_TABLES)) {
        continue;
      }
      $fields = array_merge($fields, $tableFields);
    }

    return $fields;
  }

  /**
   * Return multiple table array of field names.
   */
  private function getJoinedTables(): array {
    $fields['node'] = [
      'nid' => $this->t('The original NID'),
      'vid' => $this->t('The original VID'),
      'type' => $this->t('Node type'),
      'uuid' => $this->t('Uuid'),
    ];

    $fields['node_field_data'] = [
      'langcode' => $this->t('Langcode'),
      'status' => $this->t('Status'),
      'title' => $this->t('Title'),
      'uid' => $this->t('Uid'),
      'created' => $this->t("Created'"),
      'changed' => $this->t('Changed'),
      'promote' => $this->t('Promote'),
      'sticky' => $this->t("Sticky"),
    ];

    $fields['node__body'] = [
      'body_value' => $this->t('Body value'),
      'body_summary' => $this->t('Body summary'),
      'body_format' => $this->t('Body Format')
    ];

    $fields['node__field_client_tags'] = [
      'delta' => $this->t('Delta'),
      'field_tags_target_id' => $this->t('Target id'),
    ];

    $fields['node__field_header_image'] = [
//      'deleted' => $this->t('Deleted'),
//      'langcode' => $this->t('Langcode'),
//      'delta' => $this->t('Delta'),
      'field_header_image_target_id' => $this->t('Target id'),
      'field_header_image_alt' => $this->t('Alt text'),
      'field_header_image_title' => $this->t('Title'),
      'field_header_image_width' => $this->t('Width'),
      'field_header_image_height' => $this->t('Height'),
    ];

    $fields['node__field_resources'] = [
      'deleted' => $this->t('Deleted'),
      'langcode' => $this->t('Langcode'),
      'delta' => $this->t('Delta'),
      'field_resources_target_id' => $this->t('Target id'),
      'field_resources_display' => $this->t('Display the file'),
      'field_resources_description' => $this->t('Description'),
    ];

    // Get the tags.
    $fields['node__field_tags'] = [
      'delta' => $this->t('Delta'),
      'field_tags_target_id' => $this->t('Target id'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'nid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    parent::prepareRow($row);

    $format = $row->get('body_format');

    if ($format === 'filtered_html') {
      $row->setSourceProperty('body_format', 'basic_html');
    }

    // Replace all php tags with with <code></code>
    $body = $row->get('body_value');
    $newBody = preg_replace(self::PHP_REGEX, "<pre><code class=\"language-php\">$1</code></pre>", $body);
    $newBody = preg_replace(self::BASH_REGEX, "<pre><code class=\"language-plaintext\">$1</code></pre>", $newBody);
    $row->setSourceProperty('body_value', $newBody);

    $summary = $row->get('body_summary');
    $newSummary = preg_replace(self::PHP_REGEX, "<pre><code class=\"language-php\">$1</code></pre>", $summary);
    $newSummary = preg_replace(self::BASH_REGEX, "<pre><code class=\"language-plaintext\">$1</code></pre>", $newSummary);
    $row->setSourceProperty('body_summary', $newSummary);


  }

}
