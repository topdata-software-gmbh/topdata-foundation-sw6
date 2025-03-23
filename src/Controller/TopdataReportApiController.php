<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Topdata\TopdataFoundationSW6\Service\TopdataReportService;

/**
 * 03/2025 created
 */
class TopdataReportApiController extends AbstractTopdataApiController
{
    public function __construct(
        private readonly TopdataReportService $reportService
    ) {
    }

    #[Route(
        path: '/topdata-foundation/reports',
        name: 'topdata.foundation.reports',
        defaults: ['_routeScope' => ['storefront']],
        methods: ['GET'],
        requirements: ['_format' => 'html']
    )]
    public function getLatestReports(): Response
    {
        try {
            $reports = $this->reportService->getLatestReports();
            return $this->render('@TopdataFoundationSW6/storefront/page/content/reports.html.twig', [
                'reports' => $reports
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}