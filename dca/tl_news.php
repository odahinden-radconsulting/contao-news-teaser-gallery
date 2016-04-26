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
 * Table tl_module
 */

// palettes

$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] .= ';{gallery_legend},addGallery;';
$GLOBALS['TL_DCA']['tl_news']['palettes']['__selector__'][] = 'addGallery';
$GLOBALS['TL_DCA']['tl_news']['subpalettes']['addGallery'] = 'multiSRC,sortBy,metaIgnore,size,imagemargin,perRow,fullsize,perPage,numberOfItems,galleryTpl,customTpl';


$GLOBALS['TL_DCA']['tl_news']['fields']['addGallery'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news']['addGallery'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['multiSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['multiSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'orderField'=>'orderSRC', 'files'=>true, 'mandatory'=>true),
    'sql'                     => "blob NULL",
    'load_callback' => array
    (
        array('tl_news_gallery', 'setMultiSrcFlags')
    )
);

$GLOBALS['TL_DCA']['tl_news']['fields']['sortBy'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['sortBy'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array('custom', 'name_asc', 'name_desc', 'date_asc', 'date_desc', 'random'),
    'reference'               => &$GLOBALS['TL_LANG']['tl_content'],
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['metaIgnore'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metaIgnore'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['perRow'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['perRow'],
    'default'                 => 4,
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
);


$GLOBALS['TL_DCA']['tl_news']['fields']['perPage'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['perPage'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'natural', 'tl_class'=>'w50'),
    'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['numberOfItems'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['numberOfItems'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'natural', 'tl_class'=>'w50'),
    'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['galleryTpl'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['galleryTpl'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'gallery_slider',
    'options_callback'        => array('tl_news_gallery', 'getGalleryTemplates'),
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['customTpl'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['customTpl'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_news_gallery', 'getElementTemplates'),
    'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['orderSRC'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['orderSRC'],
    'sql'                     => "blob NULL"
);



/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_news_gallery extends Backend
{
    /**
     * Return all content element templates as array
     *
     * @return array
     */
    public function getElementTemplates()
    {
        return $this->getTemplateGroup('ce_');
    }

    /**
     * Return all gallery templates as array
     *
     * @return array
     */
    public function getGalleryTemplates()
    {
        return $this->getTemplateGroup('gallery_');
    }

    /**
     * Dynamically add flags to the "multiSRC" field
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function setMultiSrcFlags($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord)
        {
            switch ($dc->activeRecord->type)
            {
                case 'gallery':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isGallery'] = true;
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('validImageTypes');
                    break;

                case 'downloads':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isDownloads'] = true;
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
                    break;
            }
        }

        return $varValue;
    }
}