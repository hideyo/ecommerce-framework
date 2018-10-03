<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class Exception extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'exception';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'class'];

    public function __construct(array $attributes = array())
    {
        $this->table = $this->table;        
        parent::__construct($attributes);
    }
}
