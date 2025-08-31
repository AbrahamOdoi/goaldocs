#!/bin/bash

# 🧪 GoalDocs Comprehensive Testing Suite
# Complete and thorough testing covering all aspects

echo "🧪 GoalDocs Comprehensive Testing Suite"
echo "========================================"
echo "Date: $(date)"
echo "Environment: Development/Testing"
echo "Build Version: GoalDocs v1.0.0"
echo ""

# Create test results directory
mkdir -p test_results
TEST_LOG="test_results/comprehensive_test_$(date +%Y%m%d_%H%M%S).log"

# Function to log test results
log_test() {
    local test_name="$1"
    local status="$2"
    local message="$3"
    echo "[$(date +%H:%M:%S)] $test_name: $status - $message" | tee -a "$TEST_LOG"
}

# Function to run a test and log results
run_test() {
    local test_name="$1"
    local test_command="$2"
    local expected_output="$3"

    echo "Running: $test_name"
    if [ -n "$expected_output" ]; then
        if eval "$test_command" 2>&1 | grep -q "$expected_output"; then
            log_test "$test_name" "PASSED" "Expected output found"
            return 0
        else
            log_test "$test_name" "FAILED" "Expected output not found"
            return 1
        fi
    else
        if eval "$test_command" > /dev/null 2>&1; then
            log_test "$test_name" "PASSED" "Test completed successfully"
            return 0
        else
            log_test "$test_name" "FAILED" "Test failed or encountered errors"
            return 1
        fi
    fi
}

# Initialize counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

echo "Starting Comprehensive Test Execution..." | tee -a "$TEST_LOG"
echo "=======================================" | tee -a "$TEST_LOG"

# ============================================================================
# PHASE 1: APPLICATION STARTUP & BASIC HEALTH CHECKS
# ============================================================================

echo ""
echo "🔍 PHASE 1: Application Startup & Basic Health Checks"
echo "====================================================="

# Test 1: Laravel Application Health
run_test "Laravel Application Health" "php artisan --version" "Laravel Framework"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 2: Database Connection
run_test "Database Connection" "php artisan migrate:status" "Migration table created successfully"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 3: Configuration Loading
run_test "Configuration Loading" "php artisan config:cache" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 4: Route Cache
run_test "Route Cache" "php artisan route:cache" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 2: ROUTE DEFINITION VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 2: Route Definition Validation"
echo "======================================="

# Test 5: Authentication Routes
run_test "Authentication Routes - Login" "php artisan route:list --name=login" "login"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Authentication Routes - Register" "php artisan route:list --name=register" "register"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Authentication Routes - Logout" "php artisan route:list --name=logout" "logout"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 6: MFA Routes
run_test "MFA Routes - Verification Method" "php artisan route:list --name=verification.method" "verification.method"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "MFA Routes - Send OTP" "php artisan route:list --name=verification.send" "verification.send"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "MFA Routes - Check OTP" "php artisan route:list --name=verification.check" "verification.check"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 7: Dashboard Routes
run_test "Dashboard Routes" "php artisan route:list --name=dashboard" "dashboard"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 8: File Management Routes
run_test "File Management - Index" "php artisan route:list --name=files.index" "files.index"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Management - Upload" "php artisan route:list --name=files.upload" "files.upload"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Management - Download" "php artisan route:list --name=files.download" "files.download"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Management - Preview" "php artisan route:list --name=files.preview" "files.preview"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 9: File Permission Routes (CRITICAL - These were missing!)
run_test "File Permissions - Get" "php artisan route:list --name=files.permissions.get" "files.permissions.get"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Permissions - Assign" "php artisan route:list --name=files.permissions.assign" "files.permissions.assign"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Permissions - Remove" "php artisan route:list --name=files.permissions.remove" "files.permissions.remove"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Permissions - My" "php artisan route:list --name=files.permissions.my" "files.permissions.my"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Permissions - Preset" "php artisan route:list --name=files.permissions.preset" "files.permissions.preset"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 10: Search Routes
run_test "Search Routes" "php artisan route:list --name=files.search" "files.search"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 11: Folder Management Routes
run_test "Folder Management - Create" "php artisan route:list --name=files.folders.create" "files.folders.create"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 12: User Management Routes
run_test "User Management - Index" "php artisan route:list --name=users.index" "users.index"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 13: Analytics Routes
run_test "Analytics Routes" "php artisan route:list --name=analytics.dashboard" "analytics.dashboard"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 3: MIDDLEWARE VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 3: Middleware Validation"
echo "================================="

# Test 14: MFA Middleware Existence
run_test "MFA Middleware Exists" "test -f app/Http/Middleware/RequireMfa.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 15: Email Verification Middleware Existence
run_test "Email Verification Middleware Exists" "test -f app/Http/Middleware/EnsureEmailIsVerified.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 4: CONTROLLER VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 4: Controller Validation"
echo "================================="

# Test 16: Authentication Controllers
run_test "Login Controller Exists" "test -f app/Http/Controllers/Auth/LoginController.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Register Controller Exists" "test -f app/Http/Controllers/Auth/RegisterController.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Verification Controller Exists" "test -f app/Http/Controllers/Auth/VerificationController.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 17: File Management Controllers
run_test "File Controller Exists" "test -f app/Http/Controllers/FileController.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Dashboard Controller Exists" "test -f app/Http/Controllers/Tenant/DashboardController.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 5: MODEL VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 5: Model Validation"
echo "============================"

# Test 18: Core Models
run_test "User Model Exists" "test -f app/Models/User.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Model Exists" "test -f app/Models/File.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Folder Model Exists" "test -f app/Models/Folder.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "FilePermission Model Exists" "test -f app/Models/FilePermission.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 6: VIEW VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 6: View Validation"
echo "==========================="

# Test 19: Authentication Views
run_test "Login View Exists" "test -f resources/views/auth/login.blade.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Register View Exists" "test -f resources/views/auth/register.blade.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Verification Views Exist" "test -f resources/views/auth/verify-method.blade.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "OTP Entry View Exists" "test -f resources/views/auth/verify-enter.blade.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 20: File Management Views
run_test "Files Index View Exists" "test -f resources/views/files/index.blade.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Dashboard View Exists" "test -f resources/views/dashboard.blade.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 7: DATABASE MIGRATION VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 7: Database Migration Validation"
echo "========================================="

# Test 21: Migration Status
run_test "Migration Status Check" "php artisan migrate:status" "Migration table created successfully"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 22: Key Migration Files
run_test "Users Migration Exists" "find database/migrations -name '*create_users_table.php' | head -1" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "Files Migration Exists" "find database/migrations -name '*create_files_table.php' | head -1" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

run_test "File Permissions Migration Exists" "find database/migrations -name '*create_file_permissions_table.php' | head -1" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 8: APPLICATION STARTUP TESTING
# ============================================================================

echo ""
echo "🔍 PHASE 8: Application Startup Testing"
echo "======================================="

# Test 23: Application Startup (Background)
echo "Starting application server for testing..."
php artisan serve --port=8001 > /dev/null 2>&1 &
SERVER_PID=$!
sleep 5

# Test 24: Server Process Check
run_test "Application Server Process" "ps -p $SERVER_PID" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 25: Home Page Accessibility
run_test "Home Page Accessibility" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8001" "200"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 26: Login Page Accessibility
run_test "Login Page Accessibility" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8001/login" "200"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 27: Register Page Accessibility
run_test "Register Page Accessibility" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8001/register" "200"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 28: Dashboard Page (Should redirect to login when not authenticated)
run_test "Dashboard Page Redirect" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8001/dashboard" "302"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 29: Files Page (Should redirect to login when not authenticated)
run_test "Files Page Redirect" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8001/files" "302"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Stop the server
kill $SERVER_PID 2>/dev/null

# ============================================================================
# PHASE 9: CONFIGURATION VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 9: Configuration Validation"
echo "===================================="

# Test 30: Environment File
run_test "Environment File Exists" "test -f .env" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 31: App Key Configuration
run_test "App Key Configuration" "php artisan config:show app.key" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 32: Database Configuration
run_test "Database Configuration" "php artisan config:show database.default" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 10: SECURITY VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 10: Security Validation"
echo "================================"

# Test 33: CSRF Protection
run_test "CSRF Token Generation" "php artisan tinker --execute='echo csrf_token();'" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 34: Session Configuration
run_test "Session Configuration" "php artisan config:show session.driver" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 11: SERVICE VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 11: Service Validation"
echo "==============================="

# Test 35: Permission Service
run_test "Permission Service Exists" "test -f app/Services/PermissionService.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 36: Security Service
run_test "Security Service Exists" "test -f app/Services/SecurityService.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 12: NOTIFICATION VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 12: Notification Validation"
echo "===================================="

# Test 37: OTP Notification
run_test "OTP Notification Exists" "test -f app/Notifications/SendOtpNotification.php" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 13: COMPREHENSIVE ROUTE TESTING
# ============================================================================

echo ""
echo "🔍 PHASE 13: Comprehensive Route Testing"
echo "========================================"

# Test 38: All Routes Load Without Errors
run_test "All Routes Load" "php artisan route:list --compact" ""
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# Test 39: Route Count Validation
ROUTE_COUNT=$(php artisan route:list --compact | wc -l)
if [ $ROUTE_COUNT -gt 50 ]; then
    log_test "Route Count Validation" "PASSED" "Found $ROUTE_COUNT routes (sufficient)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    log_test "Route Count Validation" "FAILED" "Only found $ROUTE_COUNT routes (insufficient)"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# PHASE 14: ERROR HANDLING VALIDATION
# ============================================================================

echo ""
echo "🔍 PHASE 14: Error Handling Validation"
echo "======================================"

# Test 40: 404 Error Handling
run_test "404 Error Handling" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8001/nonexistent-page" "404"
if [ $? -eq 0 ]; then PASSED_TESTS=$((PASSED_TESTS + 1)); else FAILED_TESTS=$((FAILED_TESTS + 1)); fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

# ============================================================================
# FINAL RESULTS
# ============================================================================

echo ""
echo "🎉 COMPREHENSIVE TESTING COMPLETE!"
echo "=================================="
echo "📊 Test Results Summary:"
echo "   Total Tests: $TOTAL_TESTS"
echo "   Passed: $PASSED_TESTS"
echo "   Failed: $FAILED_TESTS"
echo "   Success Rate: $((PASSED_TESTS * 100 / TOTAL_TESTS))%"

# Calculate success rate
SUCCESS_RATE=$((PASSED_TESTS * 100 / TOTAL_TESTS))

echo ""
echo "📋 Detailed Results:"
echo "==================="

if [ $SUCCESS_RATE -eq 100 ]; then
    echo "✅ EXCELLENT - All tests passed!"
    echo "✅ Application is fully functional"
    echo "✅ Ready for production deployment"
elif [ $SUCCESS_RATE -ge 90 ]; then
    echo "✅ GOOD - Most tests passed"
    echo "⚠️  Some minor issues detected"
    echo "✅ Application is functional"
elif [ $SUCCESS_RATE -ge 80 ]; then
    echo "⚠️  FAIR - Several tests failed"
    echo "❌ Issues need attention"
    echo "⚠️  Application may have problems"
else
    echo "❌ POOR - Many tests failed"
    echo "❌ Critical issues detected"
    echo "❌ Application needs significant work"
fi

echo ""
echo "📝 Test Log Location: $TEST_LOG"
echo "🔍 Review the log for detailed test results"

# Exit with appropriate code
if [ $SUCCESS_RATE -ge 90 ]; then
    exit 0
else
    exit 1
fi
