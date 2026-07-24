<?php

namespace Zerp\Recruitment\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Zerp\Recruitment\Models\JobPosting;
use Zerp\Recruitment\Http\Requests\Api\StoreJobPostingApiRequest;
use Zerp\Recruitment\Http\Requests\Api\UpdateJobPostingApiRequest;
use Zerp\Recruitment\Events\CreateJobPosting;
use Zerp\Recruitment\Events\UpdateJobPosting;
use Zerp\Recruitment\Events\DestroyJobPosting;

class JobPostingApiController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('manage-job-postings')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $jobpostings = JobPosting::query()
                ->with(['jobType:id,name', 'location:id,name'])
                ->where(function($q) {
                    if (Auth::user()->can('manage-any-job-postings')) {
                        $q->where('job_postings.created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-job-postings')) {
                        $q->where('job_postings.creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when($request->title, function($q) use ($request) {
                    $q->where(function($query) use ($request) {
                        $query->where('job_postings.title', 'like', '%' . $request->title . '%')
                              ->orWhere('job_postings.description', 'like', '%' . $request->title . '%')
                              ->orWhere('job_postings.posting_code', 'like', '%' . $request->title . '%');
                    });
                })
                ->when($request->job_type_id && $request->job_type_id !== 'all', fn($q) => $q->where('job_type_id', $request->job_type_id))
                ->when($request->location_id && $request->location_id !== 'all', fn($q) => $q->where('location_id', $request->location_id))
                ->when($request->branch_id && $request->branch_id !== 'all', fn($q) => $q->where('branch_id', $request->branch_id))
                ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate($request->get('per_page', 10))
                ->withQueryString();

            return $this->paginatedResponse($jobpostings, __('Job postings retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('JobPosting API index error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function store(StoreJobPostingApiRequest $request)
    {
        try {
            if (!Auth::user()->can('create-job-postings')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $validated = $request->validated();

            $jobposting = new JobPosting();
            $jobposting->code = 'JOB-' . rand(1000, 9999);
            $jobposting->posting_code = JobPosting::generatePostingCode();
            $jobposting->title = $validated['title'];
            $jobposting->position = $validated['position'];
            $jobposting->priority = $validated['priority'];
            $jobposting->job_application = $validated['job_application'];
            $jobposting->application_url = $validated['application_url'] ?? null;
            $jobposting->branch_id = $validated['branch_id'];
            $jobposting->min_experience = $validated['min_experience'];
            $jobposting->max_experience = $validated['max_experience'] ?? null;
            $jobposting->min_salary = $validated['min_salary'] ?? null;
            $jobposting->max_salary = $validated['max_salary'] ?? null;
            $jobposting->description = $validated['description'] ?? null;
            $jobposting->requirements = $validated['requirements'] ?? null;
            $jobposting->benefits = $validated['benefits'] ?? null;
            $jobposting->terms_condition = $validated['terms_condition'];
            $jobposting->show_terms_condition = $request->boolean('show_terms_condition', false);
            $jobposting->application_deadline = $validated['application_deadline'] ?? null;
            $jobposting->is_published = $request->boolean('is_published', false);
            $jobposting->publish_date = $validated['publish_date'] ?? null;
            $jobposting->is_featured = $request->boolean('is_featured', false);
            $jobposting->status = $validated['status'];
            $jobposting->job_type_id = $validated['job_type_id'];
            $jobposting->location_id = $validated['location_id'];
            $jobposting->creator_id = Auth::id();
            $jobposting->created_by = creatorId();
            $jobposting->save();

            CreateJobPosting::dispatch($request, $jobposting);

            return $this->successResponse($jobposting, __('Job posting created successfully'), 201);
        } catch (\Exception $e) {
            Log::error('JobPosting API store error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can('manage-job-postings')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $jobposting = JobPosting::with(['jobType', 'location'])
                ->where('id', $id)
                ->where(function($q) {
                    if (Auth::user()->can('manage-any-job-postings')) {
                        $q->where('job_postings.created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-job-postings')) {
                        $q->where('job_postings.creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->first();

            if (!$jobposting) {
                return $this->errorResponse(__('Job posting not found'), null, 404);
            }

            return $this->successResponse($jobposting, __('Job posting details retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('JobPosting API show error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function update(UpdateJobPostingApiRequest $request, $id)
    {
        try {
            if (!Auth::user()->can('edit-job-postings')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $jobposting = JobPosting::where('id', $id)
                ->where('created_by', creatorId())
                ->first();

            if (!$jobposting) {
                return $this->errorResponse(__('Job posting not found'), null, 404);
            }

            $validated = $request->validated();

            $jobposting->title = $validated['title'];
            $jobposting->position = $validated['position'];
            $jobposting->priority = $validated['priority'];
            $jobposting->job_application = $validated['job_application'];
            $jobposting->application_url = $validated['application_url'] ?? null;
            $jobposting->branch_id = $validated['branch_id'];
            $jobposting->min_experience = $validated['min_experience'];
            $jobposting->max_experience = $validated['max_experience'] ?? null;
            $jobposting->min_salary = $validated['min_salary'] ?? null;
            $jobposting->max_salary = $validated['max_salary'] ?? null;
            $jobposting->description = $validated['description'] ?? null;
            $jobposting->requirements = $validated['requirements'] ?? null;
            $jobposting->benefits = $validated['benefits'] ?? null;
            $jobposting->terms_condition = $validated['terms_condition'];
            $jobposting->show_terms_condition = $request->boolean('show_terms_condition', false);
            $jobposting->application_deadline = $validated['application_deadline'] ?? null;
            $jobposting->is_published = $request->boolean('is_published', false);
            $jobposting->publish_date = $validated['publish_date'] ?? null;
            $jobposting->is_featured = $request->boolean('is_featured', false);
            $jobposting->status = $validated['status'];
            $jobposting->job_type_id = $validated['job_type_id'];
            $jobposting->location_id = $validated['location_id'];
            $jobposting->save();

            UpdateJobPosting::dispatch($request, $jobposting);

            return $this->successResponse($jobposting, __('Job posting updated successfully'));
        } catch (\Exception $e) {
            Log::error('JobPosting API update error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->can('delete-job-postings')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $jobposting = JobPosting::where('id', $id)
                ->where('created_by', creatorId())
                ->first();

            if (!$jobposting) {
                return $this->errorResponse(__('Job posting not found'), null, 404);
            }

            DestroyJobPosting::dispatch($jobposting);
            $jobposting->delete();

            return $this->successResponse(null, __('Job posting deleted successfully'));
        } catch (\Exception $e) {
            Log::error('JobPosting API destroy error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }
}
