<?php

namespace Hideyo\Ecommerce\Framework\Services\HtmlBlock;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\HtmlBlock\Entity\HtmlBlockRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class HtmlBlockService extends BaseService
{
	public function __construct(HtmlBlockRepository $htmlBlock)
	{
		$this->repo = $htmlBlock;
	} 

    public function selectByLimitAndOrderBy($shopId, $limit, $orderBy)
    {
    	return $this->repo->selectByLimitAndOrderBy($shopId, $limit, $orderBy);
    }

    public function selectOneBySlug($shopId, $slug)
    {
    	return $this->repo->selectOneBySlug($shopId, $slug);
    }

    public function selectAllActiveGroupsByShopId($shopId)
    {
    	return $this->repo->selectAllActiveGroupsByShopId($shopId);
    }

    public function selectOneByShopIdAndPosition($position, $shopId) {
        return $this->repo->selectOneByShopIdAndPosition($position, $shopId);
    }
}