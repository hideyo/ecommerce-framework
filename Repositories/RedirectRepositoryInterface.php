<?php
namespace Hideyo\Repositories;

interface RedirectRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();
    
    public function selectNewRedirects();
    
    public function find($id);

    public function findByUrl($url);

    public function findByUrlAndActive($url);

    public function checkByCompanyIdAndUrl($companyId, $shopUrl);

    public function findByCompanyIdAndUrl($companyId, $shopUrl);

    public function destroy($id);

    public function destroyByUrl($url);
}
