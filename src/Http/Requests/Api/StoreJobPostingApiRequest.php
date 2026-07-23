<?php

namespace Zerp\Recruitment\Http\Requests\Api;

use App\Http\Requests\ApiFormRequest;

class StoreJobPostingApiRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'position' => 'required|integer|min:1',
            'priority' => 'required|in:0,1,2',
            'job_application' => 'required|in:existing,custom',
            'application_url' => 'required_if:job_application,custom|nullable|url',
            'branch_id' => 'required|exists:branches,id,created_by,' . creatorId(),
            'applicant' => 'nullable|array',
            'applicant.*' => 'string|in:gender,date_of_birth,country',
            'visibility' => 'nullable|array',
            'visibility.*' => 'string|in:profile_image,resume,cover_letter',
            'min_experience' => 'required|numeric|min:0',
            'max_experience' => 'nullable|numeric|min:0|gte:min_experience',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'terms_condition' => 'required|string',
            'show_terms_condition' => 'nullable|boolean',
            'application_deadline' => 'nullable|date|after_or_equal:today',
            'is_published' => 'nullable|boolean',
            'publish_date' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|string',
            'job_type_id' => 'required|exists:job_types,id,created_by,' . creatorId(),
            'location_id' => 'required|exists:job_locations,id,created_by,' . creatorId(),
            'custom_questions' => 'nullable|array',
            'custom_questions.*' => 'integer|exists:custom_questions,id,created_by,' . creatorId(),
            'skills' => 'required|array',
            'skills.*' => 'string|max:50'
        ];
    }
}
