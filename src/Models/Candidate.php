<?php

namespace Zerp\Recruitment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Zerp\Recruitment\Models\JobPosting;
use Zerp\Recruitment\Models\InterviewFeedback;
use Zerp\Recruitment\Models\Interview;
use Zerp\Recruitment\Models\CandidateAssessment;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'dob',
        'country',
        'state',
        'city',
        'current_company',
        'current_position',
        'experience_years',
        'current_salary',
        'expected_salary',
        'notice_period',
        'skills',
        'education',
        'portfolio_url',
        'linkedin_url',
        'profile_path',
        'resume_path',
        'cover_letter_path',
        'profile_media_id',
        'resume_media_id',
        'cover_letter_media_id',
        'status',
        'application_date',
        'custom_question',
        'tracking_id',
        'job_id',
        'user_id',
        'source_id',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'current_salary' => 'decimal:2',
            'expected_salary' => 'decimal:2',
            'status' => 'string',
            'application_date' => 'date',
            'dob' => 'date'
        ];
    }

    public function job_posting()
    {
        return $this->belongsTo(JobPosting::class, 'job_id', 'id');
    }

    public function candidate_source()
    {
        return $this->belongsTo(CandidateSources::class, 'source_id', 'id');
    }

    public function profileMedia()
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'profile_media_id');
    }

    public function resumeMedia()
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'resume_media_id');
    }

    public function coverLetterMedia()
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'cover_letter_media_id');
    }

    /**
     * Link each of this candidate's file columns to a real Media row
     * (resolving one from a prior MediaPicker upload, or backfilling one
     * for a file written via the upload_file() helper).
     */
    public function linkMedia(?int $creatorId, ?int $createdBy): void
    {
        $fields = [
            'profile_path' => ['media_id' => 'profile_media_id', 'collection' => 'candidate_profiles', 'directory' => 'Candidate Profiles'],
            'resume_path' => ['media_id' => 'resume_media_id', 'collection' => 'candidate_resumes', 'directory' => 'Candidate Resumes'],
            'cover_letter_path' => ['media_id' => 'cover_letter_media_id', 'collection' => 'candidate_cover_letters', 'directory' => 'Candidate Cover Letters'],
        ];

        $updates = [];
        foreach ($fields as $pathField => $config) {
            if (!$this->$pathField) {
                continue;
            }
            $media = \App\Services\MediaAttachmentService::resolveOrBackfill(
                $this->$pathField,
                self::class,
                $this->id,
                $config['collection'],
                $creatorId,
                $createdBy,
                \App\Services\MediaAttachmentService::ensureDirectory($config['directory'], $createdBy, $creatorId)
            );
            if ($media) {
                $updates[$config['media_id']] = $media->id;
            }
        }

        if ($updates) {
            $this->update($updates);
        }
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'candidate_id', 'id');
    }

    public function interviewFeedbacks()
    {
        return $this->hasManyThrough(InterviewFeedback::class, Interview::class, 'candidate_id', 'interview_id', 'id', 'id');
    }

    public function candidateAssessments()
    {
        return $this->hasMany(CandidateAssessment::class, 'candidate_id', 'id');
    }

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function generateTrackingId($user_id = null)
    {
        $user_id = $user_id ?? creatorId();
        $prefix = 'TRK';
        $currentYear = date('Y');

        $lastCandidate = self::where('created_by', $user_id)
            ->where('tracking_id', 'LIKE', $prefix . $user_id . $currentYear . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCandidate) {
            $lastNumber = (int) substr($lastCandidate->tracking_id, strlen($prefix . $user_id . $currentYear));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $user_id . $currentYear . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($candidate) {
            if (empty($candidate->tracking_id)) {
                $candidate->tracking_id = static::generateTrackingId($candidate->created_by);
            }
        });
    }
}
