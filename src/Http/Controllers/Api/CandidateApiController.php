<?php

namespace Zerp\Recruitment\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Zerp\Recruitment\Models\Candidate;
use Zerp\Recruitment\Http\Requests\Api\StoreCandidateApiRequest;
use Zerp\Recruitment\Http\Requests\Api\UpdateCandidateApiRequest;
use Zerp\Recruitment\Events\CreateCandidate;
use Zerp\Recruitment\Events\UpdateCandidate;
use Zerp\Recruitment\Events\DestroyCandidate;

class CandidateApiController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('manage-candidates')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $candidates = Candidate::query()
                ->with(['job_posting', 'candidate_source'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-candidates')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-candidates')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when($request->name, function ($q) use ($request) {
                    $searchTerm = $request->name;
                    $q->where(function ($query) use ($searchTerm) {
                        $query->where('first_name', 'like', '%' . $searchTerm . '%')
                              ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                              ->orWhere('email', 'like', '%' . $searchTerm . '%')
                              ->orWhere('tracking_id', 'like', '%' . $searchTerm . '%')
                              ->orWhere('skills', 'like', '%' . $searchTerm . '%');
                    });
                })
                ->latest()
                ->paginate($request->get('per_page', 10))
                ->withQueryString();

            return $this->paginatedResponse($candidates, __('Candidates retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('Candidate API index error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function store(StoreCandidateApiRequest $request)
    {
        try {
            if (!Auth::user()->can('create-candidates')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $validated = $request->validated();

            $candidate = new Candidate();
            $candidate->tracking_id = 'CAN-' . rand(10000, 99999);
            $candidate->first_name = $validated['first_name'];
            $candidate->last_name = $validated['last_name'];
            $candidate->email = $validated['email'];
            $candidate->phone = $validated['phone'] ?? null;
            $candidate->gender = $validated['gender'] ?? null;
            $candidate->dob = $validated['dob'] ?? null;
            $candidate->country = $validated['country'] ?? null;
            $candidate->state = $validated['state'] ?? null;
            $candidate->city = $validated['city'] ?? null;
            $candidate->current_company = $validated['current_company'] ?? null;
            $candidate->current_position = $validated['current_position'] ?? null;
            $candidate->experience_years = $validated['experience_years'];
            $candidate->current_salary = $validated['current_salary'] ?? null;
            $candidate->expected_salary = $validated['expected_salary'] ?? null;
            $candidate->notice_period = $validated['notice_period'] ?? null;
            $candidate->skills = $validated['skills'] ?? null;
            $candidate->education = $validated['education'] ?? null;
            $candidate->portfolio_url = $validated['portfolio_url'] ?? null;
            $candidate->linkedin_url = $validated['linkedin_url'] ?? null;
            $candidate->status = $validated['status'];
            $candidate->application_date = $validated['application_date'];
            $candidate->job_id = $validated['job_id'];
            $candidate->source_id = $validated['source_id'];
            $candidate->creator_id = Auth::id();
            $candidate->created_by = creatorId();
            $candidate->save();

            CreateCandidate::dispatch($request, $candidate);

            return $this->successResponse($candidate, __('Candidate created successfully'), 201);
        } catch (\Exception $e) {
            Log::error('Candidate API store error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can('manage-candidates')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $candidate = Candidate::with(['job_posting', 'candidate_source'])
                ->where('id', $id)
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-candidates')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-candidates')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->first();

            if (!$candidate) {
                return $this->errorResponse(__('Candidate not found'), null, 404);
            }

            return $this->successResponse($candidate, __('Candidate details retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('Candidate API show error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function update(UpdateCandidateApiRequest $request, $id)
    {
        try {
            if (!Auth::user()->can('edit-candidates')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $candidate = Candidate::where('id', $id)
                ->where('created_by', creatorId())
                ->first();

            if (!$candidate) {
                return $this->errorResponse(__('Candidate not found'), null, 404);
            }

            $validated = $request->validated();

            $candidate->first_name = $validated['first_name'];
            $candidate->last_name = $validated['last_name'];
            $candidate->email = $validated['email'];
            $candidate->phone = $validated['phone'] ?? null;
            $candidate->gender = $validated['gender'] ?? null;
            $candidate->dob = $validated['dob'] ?? null;
            $candidate->country = $validated['country'] ?? null;
            $candidate->state = $validated['state'] ?? null;
            $candidate->city = $validated['city'] ?? null;
            $candidate->current_company = $validated['current_company'] ?? null;
            $candidate->current_position = $validated['current_position'] ?? null;
            $candidate->experience_years = $validated['experience_years'];
            $candidate->current_salary = $validated['current_salary'] ?? null;
            $candidate->expected_salary = $validated['expected_salary'] ?? null;
            $candidate->notice_period = $validated['notice_period'] ?? null;
            $candidate->skills = $validated['skills'] ?? null;
            $candidate->education = $validated['education'] ?? null;
            $candidate->portfolio_url = $validated['portfolio_url'] ?? null;
            $candidate->linkedin_url = $validated['linkedin_url'] ?? null;
            $candidate->status = $validated['status'];
            $candidate->application_date = $validated['application_date'];
            $candidate->job_id = $validated['job_id'];
            $candidate->source_id = $validated['source_id'];
            $candidate->save();

            UpdateCandidate::dispatch($request, $candidate);

            return $this->successResponse($candidate, __('Candidate updated successfully'));
        } catch (\Exception $e) {
            Log::error('Candidate API update error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->can('delete-candidates')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $candidate = Candidate::where('id', $id)
                ->where('created_by', creatorId())
                ->first();

            if (!$candidate) {
                return $this->errorResponse(__('Candidate not found'), null, 404);
            }

            DestroyCandidate::dispatch($candidate);
            $candidate->delete();

            return $this->successResponse(null, __('Candidate deleted successfully'));
        } catch (\Exception $e) {
            Log::error('Candidate API destroy error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }
}
