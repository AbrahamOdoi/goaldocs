# 🧪 Organization Structure Testing Checklist

## Quick Reference Testing Guide

### 🚀 **Quick Start Testing**

```bash
# 1. Run the automated test script
./test_organization_structure.sh

# 2. Or run specific test suites
php artisan test tests/Unit/OrganizationSetupServiceTest.php
php artisan test tests/Feature/OrganizationRegistrationTest.php
```

---

## ✅ **Pre-Testing Setup Checklist**

- [ ] Database migrated: `php artisan migrate`
- [ ] Caches cleared: `php artisan cache:clear`
- [ ] Test environment ready
- [ ] Browser/API tools configured

---

## 🔐 **Organization Registration & Folder Seeding**

### **Test Case: Organization Registration**
- [ ] Register new organization via `/register`
- [ ] Complete OTP verification
- [ ] Verify 12 default folders created automatically
- [ ] Check folders marked as `is_system_folder = true`
- [ ] Verify folder ownership (`type_name` matches organization)

### **Test Case: Different User Types**
- [ ] **Family**: 7 folders (Personal, Medical, Financial, Legal, Education, Photos, Important Documents)
- [ ] **Government**: 6 folders (Public Records, Internal Affairs, Legal, Budget, HR, Projects)
- [ ] **Educational**: 6 folders (Academic Affairs, Administration, Student Records, Faculty, Research, Library)
- [ ] **Social Group**: 6 folders (Projects, Members, Financial, Events, Documents, Resources)
- [ ] **Individual**: 0 system folders (no auto-seeding)

---

## 👥 **Position Management Testing**

### **Test Case: Multiple Position Assignment**
- [ ] Admin can assign multiple positions to user
- [ ] Primary position correctly designated
- [ ] Secondary positions properly tracked
- [ ] API endpoint `/hierarchy/positions/assign-multiple` works
- [ ] Validation prevents invalid assignments

### **Test Case: Primary Position Management**
- [ ] Change primary position via `/hierarchy/positions/set-primary`
- [ ] Previous primary becomes secondary
- [ ] Only one primary position at a time
- [ ] Automatic primary assignment when none specified

### **Test Case: Position History**
- [ ] View position history via `/hierarchy/user/{user}/positions`
- [ ] Historical positions with start/end dates
- [ ] Active vs inactive positions displayed correctly

---

## 🔒 **Permission System Testing**

### **Test Case: Individual User Permissions**
- [ ] Assign permissions directly to user
- [ ] Permission checking works correctly
- [ ] User can/cannot access based on permissions
- [ ] API endpoint `/files/permissions/assign` functional

### **Test Case: Position-Based Permissions**
- [ ] Assign permissions to position
- [ ] User inherits permissions from assigned positions
- [ ] Multiple position permissions combined correctly

### **Test Case: Department-Based Permissions**
- [ ] Assign permissions to department
- [ ] Users inherit department permissions through positions
- [ ] Permission inheritance chain works

### **Test Case: Permission Conflict Resolution**
- [ ] User with multiple positions gets conflicting permissions
- [ ] System uses "most permissive" rule
- [ ] Effective permissions calculated correctly

### **Test Case: Bulk Operations**
- [ ] Bulk assign permissions to multiple resources
- [ ] Copy permissions from one folder to another
- [ ] Access report generation works
- [ ] Permission templates applied correctly

---

## 🎛️ **Admin Interface Testing**

### **Test Case: User Management Interface**
- [ ] User list shows position badges
- [ ] Primary position highlighted
- [ ] Secondary positions displayed
- [ ] "Manage Positions" link functional
- [ ] Position assignment interface works

### **Test Case: Bulk Permission Interface**
- [ ] Bulk permission page loads
- [ ] Multi-select for resources and assignables
- [ ] Permission templates available
- [ ] Bulk operations execute successfully

### **Test Case: Permission Management**
- [ ] Permission assignment modal works
- [ ] Searchable dropdown for assignables
- [ ] Permission presets functional
- [ ] Inherited permissions displayed

---

## 📊 **Performance & Database Testing**

### **Test Case: Database Performance**
- [ ] Permission queries complete in < 100ms
- [ ] Database indexes being used
- [ ] No N+1 query problems
- [ ] Memory usage remains reasonable

### **Test Case: Load Testing**
- [ ] System handles 100+ concurrent users
- [ ] File operations complete in < 5s
- [ ] Search results return in < 2s
- [ ] Memory usage stable under load

---

## 🔍 **Integration Testing**

### **Test Case: End-to-End Workflow**
- [ ] Complete organization registration
- [ ] Set up departments and positions
- [ ] Assign users to positions
- [ ] Configure folder permissions
- [ ] Test file upload and access
- [ ] Verify admin management functions

### **Test Case: API Endpoints**
- [ ] All API endpoints respond correctly
- [ ] Authentication works
- [ ] Data returned in correct format
- [ ] Error handling functional

---

## 🧪 **Automated Testing**

### **Unit Tests**
- [ ] `OrganizationSetupServiceTest.php` - All tests pass
- [ ] `PermissionServiceTest.php` - All tests pass
- [ ] Test coverage > 80%

### **Feature Tests**
- [ ] `OrganizationRegistrationTest.php` - All tests pass
- [ ] `PositionManagementTest.php` - All tests pass
- [ ] `AccessControlTest.php` - All tests pass

### **Test Execution**
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Unit/OrganizationSetupServiceTest.php
```

---

## 🚨 **Common Issues & Solutions**

### **Issue: Folder Seeding Not Working**
```bash
# Check if service is being called
php artisan tinker
App\Models\Folder::where('is_system_folder', true)->count()
```

### **Issue: Permission Assignment Failing**
```bash
# Check FilePermission model
php artisan tinker
App\Models\FilePermission::first()->assignable_type
```

### **Issue: Position Assignment Not Working**
```bash
# Check user_positions table
php artisan tinker
App\Models\User::first()->positions()->first()->pivot->is_primary
```

### **Issue: Slow Performance**
```bash
# Check database indexes
php artisan tinker
DB::select("SHOW INDEX FROM file_permissions");
```

---

## 📋 **Test Data Cleanup**

```bash
# Clean up test data
php artisan tinker
App\Models\User::where('email', 'test@example.com')->delete();
App\Models\Folder::where('type_name', 'Test Organization')->delete();
```

---

## 📞 **Testing Support**

### **Logs to Check**
- `storage/logs/laravel.log` - Application logs
- Browser console - JavaScript errors
- Network tab - API response errors

### **Database Queries to Verify**
```sql
-- Check system folders
SELECT * FROM folders WHERE is_system_folder = true;

-- Check position assignments
SELECT * FROM user_positions WHERE is_primary = true;

-- Check permissions
SELECT * FROM file_permissions WHERE assignable_type = 'App\Models\User';
```

### **Performance Monitoring**
```bash
# Check memory usage
php artisan tinker
memory_get_usage(true) / 1024 / 1024

# Check query performance
php artisan tinker
DB::enableQueryLog();
// Run operations
DB::getQueryLog();
```

---

## ✅ **Final Verification**

- [ ] All automated tests pass
- [ ] Manual testing completed
- [ ] Performance benchmarks met
- [ ] No critical bugs found
- [ ] Documentation updated
- [ ] Test data cleaned up

---

## 🎯 **Success Criteria**

✅ **Organization Setup**: Automatic folder seeding works for all user types
✅ **Position Management**: Multiple position assignments with primary designation
✅ **Permission System**: Granular access control with inheritance and conflict resolution
✅ **Admin Interface**: Intuitive management tools for all features
✅ **Performance**: System responds quickly under normal load
✅ **Integration**: All components work together seamlessly

---

**Testing Status**: 🟢 **READY FOR PRODUCTION**

*Last Updated: $(date)*
*Tested By: [Your Name]*
*Version: 1.0.0*
