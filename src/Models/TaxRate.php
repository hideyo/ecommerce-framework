<?php 

namespace Hideyo\Ecommerce\Framework\Models;

class TaxRate extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'tax_rate';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['title', 'rate', 'shop_id', 'modified_by_user_id'];
}