<?php

namespace Zerp\Recruitment\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Zerp\Recruitment\Models\Candidate;
use Zerp\Recruitment\Models\CandidateOnboarding;
use Zerp\Recruitment\Models\JobPosting;
use Zerp\Recruitment\Models\Interview;

class DashboardApiController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            if (!Auth::user()->can('manage-recruitment-dashboard')) {
                return $this->errorResponse(__('Permission denied'), null, 403);
            }

            $creatorId = creatorId();

            $totalCandidates = Candidate::where('created_by', $creatorId)->count();
            $completedOnboardings = CandidateOnboarding::where('created_by', $creatorId)->where('status', 'Completed')->count();
            $activeJobPostings = JobPosting::where('created_by', $creatorId)->where('is_published', true)->count();
            $pendingInterviews = Interview::where('created_by', $creatorId)->where('status', '0')->count();

            $calendarEvents = Interview::where('created_by', $creatorId)
                ->where('status', '0')
                ->with(['candidate', 'jobPosting'])
                ->get()
                ->map(fn($interview) => [
                    'id' => $interview->id,
                    'title' => ($interview->candidate ? $interview->candidate->first_name . ' ' . $interview->candidate->last_name : 'Interview') .
                              ($interview->jobPosting ? ' - ' . $interview->jobPosting->title : ''),
                    'date' => $interview->scheduled_date,
                    'time' => $interview->scheduled_time ?? '09:00',
                    'status' => $interview->status ?? 'Scheduled'
                ]);

            return $this->successResponse([
                'stats' => [
                    'total_candidates' => $totalCandidates,
                    'completed_onboardings' => $completedOnboardings,
                    'active_job_postings' => $activeJobPostings,
                    'pending_interviews' => $pendingInterviews,
                ],
                'calendar_events' => $calendarEvents,
            ], __('Dashboard retrieved successfully'));
        } catch (\Exception $e) {
            Log::error('Recruitment Dashboard API error', ['e' => $e]);
            return $this->errorResponse(__('Something went wrong'), null, 500);
        }
    }
}
