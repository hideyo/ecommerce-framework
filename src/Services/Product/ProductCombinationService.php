<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductCombinationRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductCombinationService extends BaseService
{
	public function __construct(ProductCombinationRepository $productTagGroup)
	{
		$this->repo = $productTagGroup;
	} 

	public function selectAllByProductCategoryId($productCategoryId, $shopId) {
		return $this->repo->selectAllByProductCategoryId($productCategoryId, $shopId);
	}

    public function generatePulldowns($product, $productAttributeId, $attributeLeadingGroup = false, $secondAttributeId = false) 
    {
        $defaultOption = array();
        $check = array();

        //create all pulldowns
        foreach ($product->attributes as $row) {
            if ($row['combinations']) {
                foreach ($row['combinations'] as $key => $value) {
                    $newPullDowns[$value->attribute->attributeGroup->title][$value->attribute->id] = $value->attribute->value;
                }
            }
        }

        if(!$productAttributeId AND $attributeLeadingGroup) {
            $productAttributeId = key($newPullDowns[$attributeLeadingGroup->title]);
        }

        $productAttributeResultWithAttributeId =  $this->repo->getProductAttribute($product, $productAttributeId);   

        if ($productAttributeResultWithAttributeId->get()->first()) {
            foreach ($productAttributeResultWithAttributeId->get()->first()->combinations as $combination) {
                $defaultOption[$combination->attribute->attributeGroup->title][$combination->attribute->id] = $combination->attribute->value;
            }
        } else {
            $productAttributeId = false;
        }

        $productAttributeResultWithAttributeId = $productAttributeResultWithAttributeId->get();

        if ($productAttributeResultWithAttributeId) {

            foreach ($productAttributeResultWithAttributeId as $row) {
                if ($row['combinations']) {
                    foreach ($row['combinations'] as $key => $value) {
                        $defaultOption[$value->attribute->attributeGroup->title][$value->attribute->id] = $value->attribute->value;
                    }
                }
            }
        }

        $defaultPulldown = array();
        if ($attributeLeadingGroup AND isset($newPullDowns[$attributeLeadingGroup->title])) {
            $defaultOption[$attributeLeadingGroup->title] = $newPullDowns[$attributeLeadingGroup->title];
            $newPullDowns = $defaultOption;
        }

        if ($attributeLeadingGroup AND isset($defaultOption[$attributeLeadingGroup->title])) {
            $defaultPulldown = $newPullDowns[$attributeLeadingGroup->title];
            $defaultPulldownFirstKey = key($newPullDowns[$attributeLeadingGroup->title]);
            unset($newPullDowns[$attributeLeadingGroup->title]);
            $newPullDowns = array_merge(array($attributeLeadingGroup->title => $defaultPulldown), $newPullDowns);
        }

        $productAttribute = $this->repo->getProductAttribute($product, $productAttributeId, $secondAttributeId)->first();

        return array('productAttribute' => $productAttribute, 'productAttributeId' => $productAttributeId, 'defaultOption' => $defaultOption, 'newPullDowns' => $newPullDowns);
    }


}