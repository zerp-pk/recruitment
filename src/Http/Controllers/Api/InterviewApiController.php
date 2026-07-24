<?php

namespace Zerp\Recruitment\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Zerp\Recruitment\Models\Interview;
use Zerp\Recruitment\Http\Requests\Api\StoreInterviewApiRequest;
use Zerp\Recruitment\Http\Requests\Api\UpdateInterviewApiRequest;
use Zerp\Recruitment\Events\CreateInterview;
use Zerp\Recruitment\Events\UpdateInterview;
use Zerp\Recruitment\Events\DestroyInterview;

class InterviewApiController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('manage-interviews')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $interviews = Interview::query()
                ->with([
                    'candidate',
                    'jobPosting:id,title,location_id',
                    'jobPosting.location:id,name',
                    'interviewRound:id,name',
                    'interviewType:id,name'
                ])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-interviews')) {
                        $q->where('interviews.created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-interviews')) {
                        $q->where(function ($query) {
                            $query->where('interviews.creator_id', Auth::id())
                                ->orWhereJsonContains('interviews.interviewer_ids', (string) Auth::id());
                        });
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when($request->location, function ($q) use ($request) {
                    $searchTerm = $request->location;
                    $q->where(function ($query) use ($searchTerm) {
                        $query->where('interviews.location', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('candidate', function ($candidateQuery) use ($searchTerm) {
                                $candidateQuery->where('first_name', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
                            });
                    });
                })
                ->latest()
                ->paginate($request->get('per_page', 10))
                ->withQueryString();

            return $this->paginatedResponse($interviews, __('Interviews retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('Interview API index error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function store(StoreInterviewApiRequest $request)
    {
        try {
            if (!Auth::user()->can('create-interviews')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $validated = $request->validated();

            $interview = new Interview();
            $interview->candidate_id = $validated['candidate_id'];
            $interview->round_id = $validated['round_id'];
            $interview->interview_type_id = $validated['interview_type_id'];
            $interview->scheduled_date = $validated['scheduled_date'];
            $interview->scheduled_time = $validated['scheduled_time'];
            $interview->duration = $validated['duration'];
            $interview->location = $validated['location'] ?? null;
            $interview->meeting_link = $validated['meeting_link'] ?? null;
            $interview->interviewer_ids = $validated['interviewer_ids'] ?? [];
            $interview->status = $validated['status'];
            $interview->creator_id = Auth::id();
            $interview->created_by = creatorId();
            $interview->save();

            CreateInterview::dispatch($request, $interview);

            return $this->successResponse($interview, __('Interview scheduled successfully'), 201);
        } catch (\Exception $e) {
            Log::error('Interview API store error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user()->can('manage-interviews')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $interview = Interview::with([
                    'candidate',
                    'jobPosting:id,title,location_id',
                    'interviewRound:id,name',
                    'interviewType:id,name'
                ])
                ->where('id', $id)
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-interviews')) {
                        $q->where('interviews.created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-interviews')) {
                        $q->where(function ($query) {
                            $query->where('interviews.creator_id', Auth::id())
                                ->orWhereJsonContains('interviews.interviewer_ids', (string) Auth::id());
                        });
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->first();

            if (!$interview) {
                return $this->errorResponse(__('Interview not found'), null, 404);
            }

            return $this->successResponse($interview, __('Interview details retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('Interview API show error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function update(UpdateInterviewApiRequest $request, $id)
    {
        try {
            if (!Auth::user()->can('edit-interviews')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $interview = Interview::where('id', $id)
                ->where('created_by', creatorId())
                ->first();

            if (!$interview) {
                return $this->errorResponse(__('Interview not found'), null, 404);
            }

            $validated = $request->validated();

            $interview->candidate_id = $validated['candidate_id'];
            $interview->round_id = $validated['round_id'];
            $interview->interview_type_id = $validated['interview_type_id'];
            $interview->scheduled_date = $validated['scheduled_date'];
            $interview->scheduled_time = $validated['scheduled_time'];
            $interview->duration = $validated['duration'];
            $interview->location = $validated['location'] ?? null;
            $interview->meeting_link = $validated['meeting_link'] ?? null;
            $interview->interviewer_ids = $validated['interviewer_ids'] ?? [];
            $interview->status = $validated['status'];
            $interview->save();

            UpdateInterview::dispatch($request, $interview);

            return $this->successResponse($interview, __('Interview updated successfully'));
        } catch (\Exception $e) {
            Log::error('Interview API update error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->can('delete-interviews')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $interview = Interview::where('id', $id)
                ->where('created_by', creatorId())
                ->first();

            if (!$interview) {
                return $this->errorResponse(__('Interview not found'), null, 404);
            }

            DestroyInterview::dispatch($interview);
            $interview->delete();

            return $this->successResponse(null, __('Interview deleted successfully'));
        } catch (\Exception $e) {
            Log::error('Interview API destroy error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }
}
