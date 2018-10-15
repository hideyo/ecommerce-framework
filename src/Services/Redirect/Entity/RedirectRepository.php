<?php
namespace Hideyo\Ecommerce\Framework\Services\Redirect\Entity;

use Hideyo\Ecommerce\Framework\Services\Redirect\Entity\Redirect;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class RedirectRepository  extends BaseRepository 
{
    protected $model;

    public function __construct(Redirect $model)
    {
        $this->model = $model;
    }

    public function destroyByUrl($url)
    {
        return $this->model->where('url', $url)->delete();
    }

    public function selectNewRedirects()
    {
        return \DB::table('number')
        ->leftJoin('user_number', 'number.id', '=', 'user_number.number_id')
        ->whereNull('user_number.number_id')
        ->select('number.id', 'number.number')
        ->get();
    }

    public function checkByCompanyIdAndUrl($companyId, $shopUrl)
    {
        $result = $this->model->where('company_id', '=', $companyId)->where('url', '=', $shopUrl)->get()->first();

        if (isset($result->id)) {
            return $result->id;
        }
        
        return false;
    }

    public function findByUrlAndActive($url)
    {
        return $this->model->where('url', $url)->whereNotNull('redirect_url')->where('active', 1)->get()->first();
    }

    public function findByUrl($url)
    {
        return $this->model->where('url', '=', $url)->get()->first();
    }
    
    public function findByCompanyIdAndUrl($companyId, $shopUrl)
    {
        return $this->model->where('company_id', '=', $companyId)->where('url', '=', $shopUrl)->get()->first();
    }    
}