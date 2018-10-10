<?php

namespace Hideyo\Ecommerce\Framework\Services\News;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\News\Entity\NewsRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class NewsService extends BaseService
{
	public function __construct(NewsRepository $news)
	{
		$this->repo = $news;
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

    public function findGroup($groupId)
    {
        return $this->repo->findGroup($groupId);
    }

    public function getGroupModel()
    {
        return $this->repo->getGroupModel();
    }

    public function findImage($imageId)
    {
        return $this->repo->getImageModel()->find($imageId);
    }

    public function getImageModel()
    {
        return $this->repo->getImageModel();
    }

    public function selectAllGroups()
    {
       return $this->repo->selectAllGroups();
    }


}