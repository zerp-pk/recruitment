<?php

namespace Zerp\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => 'required|exists:candidates,id,created_by,' . creatorId(),
            'checklist_id' => 'required|exists:onboarding_checklists,id,created_by,' . creatorId(),
            'start_date' => 'required|date',
            'buddy_employee_id' => 'nullable|exists:users,id',
            'status' => 'required|in:Pending,In Progress,Completed'
        ];
    }
}