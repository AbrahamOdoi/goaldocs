<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SystemIntegrationService;
use Illuminate\Support\Facades\Auth;

class SystemIntegrationController extends Controller
{
    protected $systemIntegrationService;

    public function __construct(SystemIntegrationService $systemIntegrationService)
    {
        $this->systemIntegrationService = $systemIntegrationService;
    }

    /**
     * Show system integration dashboard
     */
    public function dashboard()
    {
        return view('system-integration.dashboard');
    }

    /**
     * Perform system integration check
     */
    public function performIntegrationCheck()
    {
        try {
            $results = $this->systemIntegrationService->performSystemIntegrationCheck();
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Integration check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test cross-module functionality
     */
    public function testCrossModuleFunctionality()
    {
        try {
            $results = $this->systemIntegrationService->testCrossModuleFunctionality();
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cross-module test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check data consistency
     */
    public function checkDataConsistency()
    {
        try {
            $results = $this->systemIntegrationService->ensureDataConsistency();
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data consistency check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        try {
            $integrationResults = $this->systemIntegrationService->performSystemIntegrationCheck();
            $crossModuleResults = $this->systemIntegrationService->testCrossModuleFunctionality();
            $consistencyResults = $this->systemIntegrationService->ensureDataConsistency();
            
            $overallHealth = $integrationResults['status'] && 
                           $crossModuleResults['overall_status'] && 
                           $consistencyResults['overall_consistent'];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'overall_health' => $overallHealth,
                    'integration_status' => $integrationResults,
                    'cross_module_status' => $crossModuleResults,
                    'data_consistency_status' => $consistencyResults,
                    'timestamp' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System health check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run comprehensive system test
     */
    public function runComprehensiveTest()
    {
        try {
            $startTime = microtime(true);
            
            // Run all tests
            $integrationResults = $this->systemIntegrationService->performSystemIntegrationCheck();
            $crossModuleResults = $this->systemIntegrationService->testCrossModuleFunctionality();
            $consistencyResults = $this->systemIntegrationService->ensureDataConsistency();
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
            
            $overallStatus = $integrationResults['status'] && 
                           $crossModuleResults['overall_status'] && 
                           $consistencyResults['overall_consistent'];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'overall_status' => $overallStatus,
                    'execution_time_ms' => $executionTime,
                    'integration_test' => $integrationResults,
                    'cross_module_test' => $crossModuleResults,
                    'consistency_test' => $consistencyResults,
                    'summary' => [
                        'total_tests' => 3,
                        'passed_tests' => array_sum([
                            $integrationResults['status'] ? 1 : 0,
                            $crossModuleResults['overall_status'] ? 1 : 0,
                            $consistencyResults['overall_consistent'] ? 1 : 0
                        ]),
                        'failed_tests' => array_sum([
                            $integrationResults['status'] ? 0 : 1,
                            $crossModuleResults['overall_status'] ? 0 : 1,
                            $consistencyResults['overall_consistent'] ? 0 : 1
                        ])
                    ],
                    'timestamp' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comprehensive test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export system integration report
     */
    public function exportReport(Request $request)
    {
        try {
            $format = $request->get('format', 'json');
            
            $integrationResults = $this->systemIntegrationService->performSystemIntegrationCheck();
            $crossModuleResults = $this->systemIntegrationService->testCrossModuleFunctionality();
            $consistencyResults = $this->systemIntegrationService->ensureDataConsistency();
            
            $report = [
                'report_title' => 'GoalDocs System Integration Report',
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'overall_status' => $integrationResults['status'] && 
                                 $crossModuleResults['overall_status'] && 
                                 $consistencyResults['overall_consistent'],
                'integration_results' => $integrationResults,
                'cross_module_results' => $crossModuleResults,
                'consistency_results' => $consistencyResults,
                'recommendations' => $this->generateRecommendations($integrationResults, $crossModuleResults, $consistencyResults)
            ];
            
            if ($format === 'json') {
                return response()->json([
                    'success' => true,
                    'data' => $report
                ]);
            } else {
                // For other formats, return JSON for now
                return response()->json([
                    'success' => true,
                    'data' => $report,
                    'message' => 'Export format not yet implemented, returning JSON'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Report export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate recommendations based on test results
     */
    private function generateRecommendations($integrationResults, $crossModuleResults, $consistencyResults)
    {
        $recommendations = [];
        
        // Integration recommendations
        if (!$integrationResults['status']) {
            $failedComponents = $integrationResults['summary']['failed_components_list'];
            foreach ($failedComponents as $component) {
                $recommendations[] = "Fix integration issues in {$component} component";
            }
        }
        
        // Cross-module recommendations
        if (!$crossModuleResults['overall_status']) {
            $failedTests = array_keys(array_filter($crossModuleResults['tests'], function($value) {
                return !$value;
            }));
            foreach ($failedTests as $test) {
                $recommendations[] = "Resolve cross-module functionality issues in {$test}";
            }
        }
        
        // Consistency recommendations
        if (!$consistencyResults['overall_consistent']) {
            $failedChecks = array_keys(array_filter($consistencyResults['checks'], function($value) {
                return !$value;
            }));
            foreach ($failedChecks as $check) {
                $recommendations[] = "Fix data consistency issues in {$check}";
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "All systems are functioning correctly. No immediate action required.";
        }
        
        return $recommendations;
    }
}
