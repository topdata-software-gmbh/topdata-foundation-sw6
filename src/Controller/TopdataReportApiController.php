<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
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
    public function getLatestReportsAction(Request $request): Response
    {
        try {
            if (!$request->getSession()->get('topdata_reports_authenticated', false)) {
                $this->addFlash('error', 'Not authenticated');
                return $this->redirectToRoute('topdata.foundation.reports.login');
            }

            return $this->render('@TopdataFoundationSW6/storefront/page/content/reports.html.twig', [
                'reports' => $this->reportService->getLatestReports()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    #[Route(
        path: '/topdata-foundation/reports/login',
        name: 'topdata.foundation.reports.login',
        defaults: ['_routeScope' => ['storefront']],
        methods: ['GET', 'POST'],
        requirements: ['_format' => 'html']
    )]
    public function loginAction(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            if ($this->reportService->validateReportsPassword($password)) {
                $request->getSession()->set('topdata_reports_authenticated', true);

                return $this->redirectToRoute('topdata.foundation.reports');
            }
            $this->addFlash('error', 'Invalid password');
        }
        return $this->render('@TopdataFoundationSW6/storefront/page/content/login.html.twig');
    }

    #[Route(
        path: '/topdata-foundation/reports/logout',
        name: 'topdata.foundation.reports.logout',
        defaults: ['_routeScope' => ['storefront']],
        methods: ['GET'],
        requirements: ['_format' => 'html']
    )]
    public function logoutAction(Request $request): Response
    {
        $request->getSession()->remove('topdata_reports_authenticated');
        $this->addFlash('success', 'You have been logged out.');
        return $this->redirectToRoute('topdata.foundation.reports.login');
    }
}