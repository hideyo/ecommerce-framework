<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;

class Attribute extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attribute';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['value', 'attribute_group_id', 'modified_by_user_id'];

    public function attributeGroup()
    {
        return $this->belongsTo('Hideyo\Models\AttributeGroup');
    }
}
