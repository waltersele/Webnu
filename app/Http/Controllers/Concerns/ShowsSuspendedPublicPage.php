<?php

namespace App\Http\Controllers\Concerns;

use App\Company;
use App\User;
use Illuminate\Http\Request;

trait ShowsSuspendedPublicPage
{
    protected function publicAccessBypassed(Request $request, Company $company): bool
    {
        $previewToken = $request->get('preview_token');
        $validPreviewToken = $previewToken && $company->isValidPreviewToken($previewToken);

        if ($request->boolean('studio_preview') || $request->boolean('sales_demo') || $validPreviewToken) {
            return true;
        }

        if ($request->boolean('preview') && auth()->check()) {
            $ownerId = optional($company->user)->id;
            if ($ownerId && (int) auth()->id() === (int) $ownerId) {
                return true;
            }
        }

        return false;
    }

    protected function shouldShowSuspendedOverlay(Request $request, Company $company): bool
    {
        if ($this->publicAccessBypassed($request, $company)) {
            return false;
        }

        if ($company->user && $company->user->isSuspended()) {
            return true;
        }

        return ! $company->enabled;
    }

    protected function ownerIsSuspended(?User $user): bool
    {
        return $user !== null && $user->isSuspended();
    }

    /** @param  array<string, mixed>  $data */
    protected function withSuspendedOverlayFlag(array $data, bool $show): array
    {
        if ($show) {
            $data['showSuspendedOverlay'] = true;
        }

        return $data;
    }

    protected function suspendedResponse($view, array $data = [])
    {
        return response()->view($view, $data, 403)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
