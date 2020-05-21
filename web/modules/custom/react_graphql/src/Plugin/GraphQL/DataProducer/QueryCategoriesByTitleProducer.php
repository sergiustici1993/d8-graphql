<?php

namespace Drupal\react_graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "query_categories_by_title",
 *   name = @Translation("Load Categories by title"),
 *   description = @Translation("Loads a list of Categories by title."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Categories connection")
 *   ),
 *   consumes = {
 *     "title" = @ContextDefinition("string",
 *       label = @Translation("title"),
 *       required = true
 *     )
 *   }
 * )
 */
class QueryCategoriesByTitleProducer extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * Articles constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($title) {
    if (!$title) {
      throw new UserError(sprintf('Exceeded a string value: '));
    }

    return new Deferred(function () use ($title) {
      $entity = $this->entityManager
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $title, 'vid' => 'category']);

      return reset($entity);
    });
  }
}
