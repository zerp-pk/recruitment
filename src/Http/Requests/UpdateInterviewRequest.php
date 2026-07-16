<?php

namespace Zerp\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => 'required|exists:candidates,id,created_by,' . creatorId(),
            'round_id' => 'required|exists:interview_rounds,id,created_by,' . creatorId(),
            'interview_type_id' => 'required|exists:interview_types,id,created_by,' . creatorId(),
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'duration' => 'required|integer|min:1',
            'location' => 'nullable|max:255',
            'meeting_link' => 'nullable',
            'interviewer_ids' => 'nullable|array',
            'interviewer_ids.*' => 'exists:users,id',
            'status' => 'required',

        ];
    }
}