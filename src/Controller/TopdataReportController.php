<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Topdata\TopdataFoundationSW6\Service\TopdataReportService;

/**
 * Handles report-related actions, including authentication and displaying reports.
 * 03/2025 created
 */
class TopdataReportController extends AbstractTopdataApiController
{
    public function __construct(
        private readonly TopdataReportService $reportService
    ) {
    }

    /**
     * Retrieves and displays the latest reports.
     *
     * @param Request $request The HTTP request.
     * @return Response The HTTP response.
     * @throws Exception
     */
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
            // ---- Check if the user is authenticated
            if (!$request->getSession()->get('topdata_reports_authenticated', false)) {
                $this->addFlash('error', 'Not authenticated');
                return $this->redirectToRoute('topdata.foundation.login');
            }

            // ---- Render the reports template with the latest reports
            return $this->render('@TopdataFoundationSW6/storefront/page/content/reports.html.twig', [
                'reports' => $this->reportService->getLatestReports()
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Handles the login action for accessing reports.
     *
     * @param Request $request The HTTP request.
     * @return Response The HTTP response.
     */
    #[Route(
        path: '/topdata-foundation/login',
        name: 'topdata.foundation.login',
        defaults: ['_routeScope' => ['storefront']],
        methods: ['GET', 'POST'],
        requirements: ['_format' => 'html']
    )]
    public function loginAction(Request $request): Response
    {
        // ---- Handle POST request (login attempt)
        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            // ---- Validate the password
            if ($this->reportService->validateReportsPassword($password)) {
                $request->getSession()->set('topdata_reports_authenticated', true);

                return $this->redirectToRoute('topdata.foundation.reports');
            }
            $this->addFlash('error', 'Invalid password');
        }
        // ---- Render the login form
        return $this->render('@TopdataFoundationSW6/storefront/page/content/login.html.twig');
    }

    /**
     * Handles the logout action.
     *
     * @param Request $request The HTTP request.
     * @return Response The HTTP response.
     */
    #[Route(
        path: '/topdata-foundation/logout',
        name: 'topdata.foundation.logout',
        defaults: ['_routeScope' => ['storefront']],
        methods: ['GET'],
        requirements: ['_format' => 'html']
    )]
    public function logoutAction(Request $request): Response
    {
        // ---- Remove the authentication flag from the session
        $request->getSession()->remove('topdata_reports_authenticated');
        $this->addFlash('success', 'You have been logged out.');
        return $this->redirectToRoute('topdata.foundation.login');
    }

    /**
     * Retrieves and displays the details of a specific report.
     *
     * @param Request $request The HTTP request.
     * @param string $id The ID of the report.
     * @return Response The HTTP response.
     * @throws Exception
     */
    #[Route(
        path: '/topdata-foundation/report/{id}',
        name: 'topdata.foundation.report.detail',
        defaults: ['_routeScope' => ['storefront']],
        methods: ['GET'],
        requirements: ['_format' => 'html']
    )]
    public function getReportDetailAction(Request $request, string $id): Response
    {
        try {
            // ---- Check if the user is authenticated
            if (!$request->getSession()->get('topdata_reports_authenticated', false)) {
                $this->addFlash('error', 'Not authenticated');
                return $this->redirectToRoute('topdata.foundation.login');
            }

            // ---- Retrieve the report by ID
            $report = $this->reportService->getReportById($id);

            // ---- Check if the report exists
            if (!$report) {
                $this->addFlash('error', 'Report not found');
                return $this->redirectToRoute('topdata.foundation.reports');
            }

            // ---- Render the detailed report template
            return $this->render('@TopdataFoundationSW6/storefront/page/content/detailed_report.html.twig', [
                'report' => $report,
            ]);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}