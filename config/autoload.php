<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   RadExtensions
 * @author    Olivier Dahinden <o.dahinden@rad-consulting.ch>
 * @license   GNU
 * @copyright 2016
 */

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'Contao',
));
/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'Contao\ModuleNewsArchiveGallery'            => 'system/modules/contao-news-teaser-gallery/modules/ModuleNewsArchiveGallery.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'gallery_slider'   => 'system/modules/contao-news-teaser-gallery/templates/',
    'mod_newslist'      => 'system/modules/contao-news-teaser-gallery/templates/',
));