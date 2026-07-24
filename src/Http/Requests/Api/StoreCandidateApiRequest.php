<?php

namespace Zerp\Recruitment\Http\Requests\Api;

use App\Http\Requests\ApiFormRequest;

class StoreCandidateApiRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date|before:today',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'current_company' => 'nullable|string|max:100',
            'current_position' => 'nullable|string|max:100',
            'experience_years' => 'required|numeric|min:0',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:50',
            'skills' => 'nullable',
            'education' => 'nullable',
            'portfolio_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'status' => 'required|string',
            'application_date' => 'required|date',
            'job_id' => 'required|numeric|exists:job_postings,id,created_by,' . creatorId(),
            'source_id' => 'required|numeric|exists:candidate_sources,id,created_by,' . creatorId()
        ];
    }
}
