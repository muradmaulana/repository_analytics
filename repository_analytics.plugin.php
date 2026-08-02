<?php
/**
 * Repository Analytics Plugin
 *
 * @author Murad Maulana
 * @version 1.0.0
 */

use SLiMS\Plugins;

defined('INDEX_AUTH') or die('Direct access not allowed!');

$plugin = [
    'name'        => 'Repository Analytics',
    'author'      => 'Murad Maulana',
    'version'     => '1.0.0',
    'description' => 'Analytics Dashboard untuk Repository SLiMS',
];

Plugins::getInstance()->registerMenu(
    'reporting',
    __('Repository Analytics'),
    __DIR__ . '/index.php',
    'fa-chart-line'
);

return $plugin;