<?php

namespace Drupal\react_graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\react_graphql\Wrappers\QueryConnection;

/**
 * @Schema(
 *   id = "product",
 *   name = "Product schema"
 * )
 */
class ProductSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry();

    $this->addQueryFields($registry, $builder);
    $this->addProductFields($registry, $builder);
    $this->addCategoryFields($registry, $builder);
    $this->addCategoryTermFields($registry, $builder);

    // Re-usable connection type fields.
    $this->addConnectionFields('ProductConnection', $registry, $builder);
    $this->addConnectionFields('CategoryConnection', $registry, $builder);

    return $registry;
  }

  /**
   * {@inheritdoc}
   */
  protected function addCategoryTermFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('CategoryTerm', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('CategoryTerm', 'name',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function addCategoryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Category', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Category', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Category', 'imageUrl',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:taxonomy_term'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_image.entity')),
        $builder->produce("image_url")
          ->map('entity',$builder->fromParent()))
    );

    $registry->addFieldResolver('Category', 'linkUrl',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:taxonomy_term'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_link.value'))
    );

    $registry->addFieldResolver('Category', 'size',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:taxonomy_term'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_size.value'))
    );

    $registry->addFieldResolver('Category', 'products',
      $builder->produce('query_products_by_term')
        ->map('term', $builder->fromParent())
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function addProductFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Product', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Product', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Product', 'price',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_price.value'))
    );

    $registry->addFieldResolver('Product', 'imageUrl',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:node'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_image.entity')),
        $builder->produce("image_url")
          ->map('entity',$builder->fromParent()))
    );

    $registry->addFieldResolver('Product', 'author',
      $builder->compose(
        $builder->produce('entity_owner')
          ->map('entity', $builder->fromParent()),
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Product', 'categories',
      $builder->produce('entity_reference')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_category'))
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'product',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['product']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'category',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('taxonomy_term'))
        ->map('bundles', $builder->fromValue(['category']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'products',
      $builder->produce('query_products')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );

    $registry->addFieldResolver('Query', 'categories',
      $builder->produce('query_categories')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
        ->map('sort', $builder->fromArgument('sort'))
    );

//    $registry->addFieldResolver('Query', 'getCategoriesByTitle',
//      $builder->produce('entity_load')
//        ->map('type', $builder->fromValue('taxonomy_term'))
//        ->map('bundles', $builder->fromValue(['category']))
//        ->map('id', $builder->fromArgument('title'))
//    );

    $registry->addFieldResolver('Query', 'getCategoriesByTitle',
      $builder->produce('query_categories_by_title')
        ->map('title', $builder->fromArgument('title'))
    );
  }

  /**
   * @param string $type
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addConnectionFields($type, ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->items();
      })
    );
  }
}
