<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsDashboardService;

class DashboardController extends Controller
{
    public function index(AnalyticsDashboardService $analyticsService)
    {
        $data = [
            'kpis' => $analyticsService->getKpiMetrics(),
            'resourceGrowth' => $analyticsService->getResourceGrowth(),
            'searchInsights' => $analyticsService->getSearchInsights(),
            'aiUtilization' => $analyticsService->getAiUtilization(),
            'pendingItems' => $analyticsService->getPendingItems(20),
            'userMetrics' => $analyticsService->getUserMetrics(),
            'examMetrics' => $analyticsService->getExamMetrics(),
            'certificateMetrics' => $analyticsService->getCertificateMetrics(),
            'questionBankHealth' => $analyticsService->getQuestionBankHealth(),
            'recentActivity' => $analyticsService->getRecentActivity(15),
        ];

        return view('admin.dashboard-advanced', $data);
    }
}
