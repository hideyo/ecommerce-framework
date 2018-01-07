<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface UserRepositoryInterface
{
    public function selectAll();
    
    public function find($id);

    public function signup($input);

    public function updateProfileById(array $attributes, $avatar, $id);

    public function updateById(array $attributes, $avatar, $id);

    public function login($input);

    public function isThrottled($input);

    public function existsButNotConfirmed($input);

    public function resetPassword($input);

    public function destroy($id);
}
