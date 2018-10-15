<?php 

namespace Hideyo\Ecommerce\Framework\Services\Content\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Content\Entity\Content;
use Hideyo\Ecommerce\Framework\Services\Content\Entity\ContentImage;
use Hideyo\Ecommerce\Framework\Services\Content\Entity\ContentGroup;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ContentRepository extends BaseRepository 
{

    protected $model;

    public function __construct(Content $model, ContentImage $modelImage, ContentGroup $modelGroup)
    {
        $this->model = $model;
        $this->modelImage = $modelImage;
        $this->modelGroup = $modelGroup;
    }

    public function selectAllGroups()
    {
        return $this->modelGroup->where('shop_id', auth('hideyobackend')->user()->selected_shop_id)->get();
    }
 
    public function findGroup($newsGroupId)
    {
        return $this->modelGroup->find($newsGroupId);
    }

    public function getGroupModel()
    {
        return $this->modelGroup;
    }

    public function findImage($newsImageId)
    {
        return $this->modelImage->find($newsImageId);
    }

    public function getImageModel()
    {
        return $this->modelImage;
    }
}