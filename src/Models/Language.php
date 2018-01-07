<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

class Language extends BaseModel  
{
    protected $table = 'language';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['language', 'shop_id', 'modified_by_user_id'];

    public function __construct(array $attributes = array()) {
        $this->table = $this->table;
        parent::__construct($attributes);
    }
}