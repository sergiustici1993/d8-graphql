<?php

namespace Drupal\custom_page\Controller;

use Drupal\Core\Controller\ControllerBase;

class CustomPageController extends ControllerBase {

  public function getData() {
    $build = [
      '#type' => 'markup',
      '#markup' => $this->t('Hello World!'),
    ];
    return $build;
  }
}
