<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Etsy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Ced\Etsy\Helper\Data;

/**
 * Class FetchCategory
 * @package Ced\Etsy\Controller\Adminhtml\Config
 */
class FetchCategory extends Action
{
    /**
     * @var JsonFactory
     */
        public $resultJsonFactory;
    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var Filesystem
     */
    public $filesystem;
    /**
     * @var Data
     */
    public $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        Filesystem\Io\File $file,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response['data'] = "<span style='color:green'>Category fetched successfully!!</span>";
        $response['msg'] = "success";
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
        $msg = false;
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy');
        if (!file_exists($folderPath)) {
            $this->file->mkdir($folderPath, 0777, true);
        }
        $taxonomy = $this->helper->ApiObject()->getSellerTaxonomy(['param' => null]);
        
        // fetch category
        try {
            foreach ($taxonomy['results'] as $key => $value) 
            {
                if( count($value['children_ids']) > 0 ) {
                    $arr1[] = ['id'=>$value['id'], 'name'=>$value['name'], 'path'=>$value['path'], 'children'=>count( $value['children_ids'])];
                } else {
                    $arr1[] = ['id'=>$value['id'], 'name'=>$value['name'], 'path'=>$value['path'], 'children'=>0];
                }
                foreach ($value['children'] as $key1 => $value1) {
                    if( count($value1['children_ids']) > 0 ) {
                        $arr2[] = ['parent_id'=>$value['id'], 'id'=>$value1['id'], 'name'=>$value1['name'], 'path'=>$value1['path'], 'children'=>count( $value1['children_ids'] )];
                    } else {
                        $arr2[] = ['parent_id'=>$value['id'], 'id'=>$value1['id'], 'name'=>$value1['name'], 'path'=>$value1['path'], 'children'=>0];
                    }
                    foreach ($value1['children'] as $key2 => $value2) 
                    {
                        if( count($value2['children_ids']) > 0 )
                        {
                            $arr3[] = ['parent_id'=>$value1['id'], 'id'=>$value2['id'], 'name'=>$value2['name'], 'path'=>$value2['path'], 'children'=>count( $value2['children_ids'] )];
                        }
                        else
                        {
                            $arr3[] = ['parent_id'=>$value1['id'], 'id'=>$value2['id'], 'name'=>$value2['name'], 'path'=>$value2['path'], 'children'=>0];
                        }
                        foreach ($value2['children'] as $key3 => $value3) 
                        {
                            if( count($value3['children_ids']) > 0 )
                            {
                                $arr4[] = ['parent_id'=>$value2['id'], 'id'=>$value3['id'], 'name'=>$value3['name'], 'path'=>$value3['path'], 'children'=>count( $value3['children_ids'] )];
                            }
                            else
                            {
                                $arr4[] = ['parent_id'=>$value2['id'], 'id'=>$value3['id'], 'name'=>$value3['name'], 'path'=>$value3['path'], 'children'=>0];
                            }
                            foreach ($value3['children'] as $key4 => $value4) 
                            {
                                if( count($value4['children_ids']) > 0 )
                                {
                                    $arr5[] = ['parent_id'=>$value3['id'], 'id'=>$value4['id'], 'name'=>$value4['name'], 'path'=>$value4['path'], 'children'=>count( $value4['children_ids'] )];
                                }
                                else
                                {
                                    $arr5[] = ['parent_id'=>$value3['id'], 'id'=>$value4['id'], 'name'=>$value4['name'], 'path'=>$value4['path'], 'children'=>0];
                                }
                                foreach ($value4['children'] as $key5 => $value5) 
                                {
                                    if( count($value5['children_ids']) > 0 )
                                    {
                                        $arr6[] = ['parent_id'=>$value4['id'], 'id'=>$value5['id'], 'name'=>$value5['name'], 'path'=>$value5['path'], 'children'=>count( $value5['children_ids'] )];
                                    }
                                    else
                                    {
                                        $arr6[] = ['parent_id'=>$value4['id'], 'id'=>$value5['id'], 'name'=>$value5['name'], 'path'=>$value5['path'], 'children'=>0 ];
                                    }
                                    foreach ($value5['children'] as $key6 => $value6) 
                                    {
                                        if( is_array($value6['children_ids']) && !empty( $value6['children_ids'] ) )
                                        {
                                            
                                            $arr7[] = ['parent_id'=>$value5['id'], 'id'=>$value6['id'], 'name'=>$value6['name'], 'path'=>$value6['path'], 'children'=>count( $value6['children_ids'] )];
                                            
                                        }
                                        else
                                        {
                                            $arr7[] = ['parent_id'=>$value5['id'], 'id'=>$value6['id'], 'name'=>$value6['name'], 'path'=>$value6['path'], 'children'=>0];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $path = $folderPath . '/categoryLevel-1.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr1));
            fclose($file);
            $path = $folderPath . '/categoryLevel-2.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr2));
            fclose($file);
            $path = $folderPath . '/categoryLevel-3.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr3));
            fclose($file);
            $path = $folderPath . '/categoryLevel-4.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr4));
            fclose($file);
            $path = $folderPath . '/categoryLevel-5.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr5));
            fclose($file);
            $path = $folderPath . '/categoryLevel-6.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr6));
            fclose($file);
            $path = $folderPath . '/categoryLevel-7.json';
            $file = fopen($path, "w");
            fwrite($file, json_encode($arr7));
            fclose($file);
            $response['data'] = "<span style='color:green'>Category fetched successfully</span>";
            $response['msg'] = "success";
        } catch (\Exception $e) {
            $response['data'] = $e->getMessage();
            $response['msg'] = "error";
        }
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
