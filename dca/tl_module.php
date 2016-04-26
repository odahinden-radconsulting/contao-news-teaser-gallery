<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['newsarchivegallery'] = '{title_legend},name,headline,type;{config_legend},news_archives,news_jumpToCurrent,news_readerModule,perPage,news_format;{template_legend:hide},news_metaFields,news_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['newsarchivegallery'] = str_replace('news_archives,', 'news_archives,news_filterCategories,news_filterDefault,news_filterPreserve,', $GLOBALS['TL_DCA']['tl_module']['palettes']['newsarchivegallery']);
