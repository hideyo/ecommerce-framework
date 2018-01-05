<?php
namespace Hideyo\Repositories;

interface ClientRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $clientId);
    
    public function selectAll();

    public function selectAllByBillClientAddress();
    
    public function find($clientId);

    public function validateRegister(array $attributes, $shopId);

    public function register(array $attributes, $shopId);

    public function selectOneByShopIdAndId($shopId, $clientId);

    public function selectOneById($clientId);

    public function setBillOrDeliveryAddress($shopId, $clientId, $addressId, $type);

    public function confirm($code, $email, $shopId);

    public function getConfirmationCodeByEmail($email, $shopId);

    public function validateConfirmationCodeByConfirmationCodeAndEmail($confirmationCode, $email, $shopId);

    public function resetPasswordByConfirmationCodeAndEmail(array $attributes, $shopId);

    public function activate($clientId);

    public function deactivate($clientId);
}
