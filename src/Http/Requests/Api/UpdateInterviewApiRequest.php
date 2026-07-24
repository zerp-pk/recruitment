<?php

namespace Zerp\Recruitment\Http\Requests\Api;

use App\Http\Requests\ApiFormRequest;

class UpdateInterviewApiRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'candidate_id' => 'required|exists:candidates,id,created_by,' . creatorId(),
            'round_id' => 'required|exists:interview_rounds,id,created_by,' . creatorId(),
            'interview_type_id' => 'required|exists:interview_types,id,created_by,' . creatorId(),
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'duration' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'interviewer_ids' => 'nullable|array',
            'interviewer_ids.*' => 'exists:users,id',
            'status' => 'required|string',
        ];
    }
}
