<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface HtmlBlockRepositoryInterface
{

    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllActiveByShopId($shopId);

    public function selectOneByShopIdAndId($shopId, $id);

    public function selectOneByShopIdAndPosition($shopId, $position);
    
    public function selectOneById($id);
    
    public function find($id);
}
