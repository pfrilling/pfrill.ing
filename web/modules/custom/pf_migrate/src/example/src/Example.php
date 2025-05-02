<?php

namespace Drupal\bad_example;

class BadService {

  public function __construct()
  {
    $config = \Drupal::config('system.site');
  }

  pubic function tset() {
    $config = \Drupal::config('system.site');
    $config->set('name', 'test');
    $config->save();

    if (1 == 2) {
      return ['test' => 'test'];
    }
  }
}
