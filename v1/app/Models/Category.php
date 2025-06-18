<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'lkp_category';
    protected $primaryKey = 'CATEGORY_ID';

    // Timestamps
    const CREATED_AT = 'CREATED_ON';
    const UPDATED_AT = 'MODIFIED_ON';

    // Allowing assignment
    protected $guarded = [];

    // Relationship between Category and Sub-Category
    public function subcategory()
    {
        return $this->hasMany(SubCategory::class, 'CATEGORY_ID');
    }
}
