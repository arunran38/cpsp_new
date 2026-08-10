<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes, \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $primaryKey = 'address_id';
    protected $guarded = [];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function designation()
    {
        return $this->belongsTo(DesignationList::class, 'designation_id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentList::class, 'department_id');
    }
}
