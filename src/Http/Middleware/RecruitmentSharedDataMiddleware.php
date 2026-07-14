<?php

namespace Zerp\Recruitment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Zerp\Recruitment\Models\RecruitmentSetting;
use App\Models\User;
use App\Classes\Module;
use App\Models\Concerns\TenantScope;

class RecruitmentSharedDataMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // The job board is public and serves ONE company's postings, addressed by the
        // slug in the URL — not by whoever happens to be logged in. Lift the tenant
        // scope for this request so a visitor signed in to another company sees the
        // board instead of an empty page. Every query here already filters by $userId.
        TenantScope::standDownForThisRequest();

        $userId = $this->getUserIdFromRequest($request);

        $user = User::find($userId);
        $userSlug = $request->route('userSlug');
        $sanitizedUserSlug = $userSlug ? htmlspecialchars($userSlug, ENT_QUOTES, 'UTF-8') : null;

        $recruitmentSettings = $this->getRecruitmentSettings($userId);

        Inertia::share([
            'recruitmentSettings' => $recruitmentSettings,
            'userSlug' => $sanitizedUserSlug,
            'companyAllSetting' => getCompanyAllSetting($userId),
            'auth' => [
                'user' => ['activatedPackages' => ActivatedModule($userId ?? null)],
            ],
            'packages' => (new Module())->allModules(),
            'imageUrlPrefix' => $user ? getImageUrlPrefix() : url('/'),
            'settings' => [
                'header' => [
                    'logo' => $recruitmentSettings['logo_dark'],
                    'site_title' => $recruitmentSettings['title_text'],
                ],
                'footer' => [
                    'footer_text' => $recruitmentSettings['footer_text'],
                ],
                'favicon' => $recruitmentSettings['favicon'],
                'site_title' => $recruitmentSettings['title_text'],
            ],
        ]);

        return $next($request);
    }

    private function getUserIdFromRequest(Request $request): int
    {
        $userSlug = $request->route('userSlug');
        if ($userSlug) {
            try {
                $user = User::where('slug', $userSlug)->first();
                if ($user) {
                    return $user->id;
                }
            } catch (\Exception $e) {
                \Log::error('Database error in RecruitmentSharedDataMiddleware: ' . $e->getMessage());
                abort(500, 'Database error');
            }
        }
        
        abort(404, 'Recruitment page not found');
    }

    private function getRecruitmentSettings($userId)
    {
        $settings = RecruitmentSetting::where('created_by', $userId)->pluck('value', 'key');

        return [
            'logo_dark' => $settings['logo_dark'] ?? 'packages/local/Recruitment/src/Resources/images/logo.png',
            'favicon' => $settings['favicon'] ?? 'packages/local/Recruitment/src/Resources/images/favicon.png',
            'title_text' => $settings['title_text'] ?? 'Careers',
            'footer_text' => $settings['footer_text'] ?? '© ' . date('Y') . ' Zerp. All rights reserved.',
        ];
    }
}
