<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'lkp_sub_category';
    protected $primaryKey = 'SUB_CATEGORY_ID';

    // Timestamps
    const CREATED_AT = 'CREATED_ON';
    const UPDATED_AT = 'MODIFIED_ON';

    // Allowing assignment
    protected $guarded = [];

    // Inverse Relationship between Sub-Categories and Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'CATEGORY_ID');
    }
}
