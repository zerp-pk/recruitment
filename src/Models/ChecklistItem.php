<?php

namespace Zerp\Recruitment\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Zerp\Recruitment\Models\OnboardingChecklist;

class ChecklistItem extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'checklist_id',
        'task_name',
        'description',
        'category',
        'assigned_to_role',
        'due_day',
        'is_required',
        'status',
        'checklist_id',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'status' => 'boolean'
        ];
    }

    public function checklist()
    {
        return $this->belongsTo(OnboardingChecklist::class);
    }
}