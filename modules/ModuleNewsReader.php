<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace ContaoGallery;

use Contao;

/**
 * Front end module "news reader".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleNewsReader extends Contao\ModuleNewsReader
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_newsreader';


	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function compile()
	{
        parent::compile();

        // Get the news item
        $objArticle = \NewsModel::findPublishedByParentAndIdOrAlias(Contao\Input::get('items'), $this->news_archives);
        $files = Contao\FilesModel::findMultipleByUuids(deserialize($objArticle->multiSRC));
        $objArticle->images = $this->buildGallery($files, $objArticle->orderSrc, $objArticle->sortBy, $objArticle->perPage, $objArticle->numberOfItems, $objArticle->galleryTpl, $objArticle->size, $objArticle->fullsize, $objArticle->imagemargin, $objArticle->arrData);
        $this->Template->articles = $this->parseArticle($objArticle);

    }

    /**
     * @param $files
     * @param $perPage
     */
    protected function buildGallery($files, $orderSrc, $sortBy, $perPage, $numberOfItems, $galleryTpl, $size, $fullsize, $imagemargin, $arrData)
    {
        global $objPage;

        $images = array();
        $auxDate = array();
        $objFiles = $files;

        // Get all images
        while ($objFiles->next())
        {
            // Continue if the files has been processed or does not exist
            if (isset($images[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path))
            {
                continue;
            }

            // Single files
            if ($objFiles->type == 'file')
            {
                $objFile = new \File($objFiles->path, true);

                if (!$objFile->isImage)
                {
                    continue;
                }

                $arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

                if (empty($arrMeta))
                {
                    if ($this->metaIgnore)
                    {
                        continue;
                    }
                    elseif ($objPage->rootFallbackLanguage !== null)
                    {
                        $arrMeta = $this->getMetaData($objFiles->meta, $objPage->rootFallbackLanguage);
                    }
                }

                // Use the file name as title if none is given
                if ($arrMeta['title'] == '')
                {
                    $arrMeta['title'] = specialchars($objFile->basename);
                }

                // Add the image
                $images[$objFiles->path] = array
                (
                    'id'        => $objFiles->id,
                    'uuid'      => $objFiles->uuid,
                    'name'      => $objFile->basename,
                    'singleSRC' => $objFiles->path,
                    'alt'       => $arrMeta['title'],
                    'imageUrl'  => $arrMeta['link'],
                    'caption'   => $arrMeta['caption']
                );

                $auxDate[] = $objFile->mtime;
            }

            // Folders
            else
            {
                $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

                if ($objSubfiles === null)
                {
                    continue;
                }

                while ($objSubfiles->next())
                {
                    // Skip subfolders
                    if ($objSubfiles->type == 'folder')
                    {
                        continue;
                    }

                    $objFile = new \File($objSubfiles->path, true);

                    if (!$objFile->isImage)
                    {
                        continue;
                    }

                    $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->language);

                    if (empty($arrMeta))
                    {
                        if ($this->metaIgnore)
                        {
                            continue;
                        }
                        elseif ($objPage->rootFallbackLanguage !== null)
                        {
                            $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->rootFallbackLanguage);
                        }
                    }

                    // Use the file name as title if none is given
                    if ($arrMeta['title'] == '')
                    {
                        $arrMeta['title'] = specialchars($objFile->basename);
                    }

                    // Add the image
                    $images[$objSubfiles->path] = array
                    (
                        'id'        => $objSubfiles->id,
                        'uuid'      => $objSubfiles->uuid,
                        'name'      => $objFile->basename,
                        'singleSRC' => $objSubfiles->path,
                        'alt'       => $arrMeta['title'],
                        'imageUrl'  => $arrMeta['link'],
                        'caption'   => $arrMeta['caption']
                    );

                    $auxDate[] = $objFile->mtime;
                }
            }
        }

        // Sort array
        switch ($sortBy)
        {
            default:
            case 'name_asc':
                uksort($images, 'basename_natcasecmp');
                break;

            case 'name_desc':
                uksort($images, 'basename_natcasercmp');
                break;

            case 'date_asc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
                break;

            case 'date_desc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
                break;

            case 'meta': // Backwards compatibility
            case 'custom':
                if ($orderSrc != '')
                {
                    $tmp = deserialize($orderSrc);

                    if (!empty($tmp) && is_array($tmp))
                    {
                        // Remove all values
                        $arrOrder = array_map(function(){}, array_flip($tmp));

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k=>$v)
                        {
                            if (array_key_exists($v['uuid'], $arrOrder))
                            {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images))
                        {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }
                }
                break;

            case 'random':
                shuffle($images);
                break;
        }

        $images = array_values($images);

        // Limit the total number of items (see #2652)
        if ($numberOfItems > 0)
        {
            $images = array_slice($images, 0, $numberOfItems);
        }

        $offset = 0;
        $total = count($images);
        $limit = $total;

        // Pagination
        if ($perPage > 0)
        {
            // Get the current page
            $id = 'page_g' . $this->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total/$perPage), 1))
            {
                /** @var \PageError404 $objHandler */
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }

            // Set limit and offset
            $offset = ($page - 1) * $perPage;
            $limit = min($perPage + $offset, $total);

            $objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }else {
            $perPage = 1;
        }



        $rowcount = 0;
        $colwidth = floor(100/$perPage);
        $intMaxWidth = (TL_MODE == 'BE') ? floor((640 / $perPage)) : floor((\Config::get('maxImageWidth') / $perPage));
        $strLightboxId = 'lightbox[lb' . $this->id . ']';
        $body = array();

        // Rows
        for ($i=$offset; $i<$limit; $i=($i+$perPage))
        {
            $class_tr = '';

            if ($rowcount == 0)
            {
                $class_tr .= ' row_first';
            }

            if (($i + $perPage) >= $limit)
            {
                $class_tr .= ' row_last';
            }

            $class_eo = (($rowcount % 2) == 0) ? ' even' : ' odd';

            // Columns
            for ($j=0; $j<$perPage; $j++)
            {
                $class_td = '';

                if ($j == 0)
                {
                    $class_td .= ' col_first';
                }

                if ($j == ($perPage - 1))
                {
                    $class_td .= ' col_last';
                }

                $objCell = new \stdClass();
                $key = 'row_' . $rowcount . $class_tr . $class_eo;

                // Empty cell
                if (!is_array($images[($i+$j)]) || ($j+$i) >= $limit)
                {
                    $objCell->colWidth = $colwidth . '%';
                    $objCell->class = 'col_'.$j . $class_td;
                }
                else
                {
                    // Add size and margin
                    $images[($i+$j)]['size'] = $size;
                    $images[($i+$j)]['imagemargin'] = $imagemargin;
                    $images[($i+$j)]['fullsize'] = $fullsize;

                    $this->addImageToTemplate($objCell, $images[($i+$j)], $intMaxWidth, $strLightboxId);

                    // Add column width and class
                    $objCell->colWidth = $colwidth . '%';
                    $objCell->class = 'col_'.$j . $class_td;
                }

                $body[$key][$j] = $objCell;
            }

            ++$rowcount;
        }


        $strTemplate = 'gallery_slider';


        // Use a custom template
        if (TL_MODE == 'FE' && $galleryTpl != '')
        {
            $strTemplate = $galleryTpl;
        }
//
        /** @var \FrontendTemplate|object $objTemplate */
        $objTemplate = new \FrontendTemplate($strTemplate);
        $objTemplate->setData($arrData);

        $objTemplate->body = $body;
        $objTemplate->headline = $this->headline; // see #1603

        return $objTemplate->parse();
    }

}
