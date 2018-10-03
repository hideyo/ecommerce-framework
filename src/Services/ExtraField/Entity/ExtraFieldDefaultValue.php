<?php 

namespace Hideyo\Ecommerce\Framework\Services\ExtraField\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class ExtraFieldDefaultValue extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'extra_field_default_value';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['value', 'extra_field_id', 'modified_by_user_id'];
}