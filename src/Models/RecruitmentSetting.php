<?php

namespace Zerp\Recruitment\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecruitmentSetting extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'key',
        'value',
        'created_by',
    ];
}