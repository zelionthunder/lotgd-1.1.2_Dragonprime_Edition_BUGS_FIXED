# PHP 5 to PHP 8 Migration Checklist

## Phase 1: Database Layer (COMPLETED ✅)
- [x] Review `lib/dbwrapper.php`
- [x] Create `lib/dbwrapper_mysqli_proc.php`
- [x] Update default connection type to MySQLi
- [x] Fix `db_fetch_assoc()` in `lib/dbwrapper_mysql.php`
- [ ] Test database connection
- [ ] Test all query types (SELECT, INSERT, UPDATE, DELETE)
- [ ] Test transactions

## Phase 2: Remove Deprecated Functions (PARTIALLY COMPLETE ✅)
- [x] Replace `each()` + `list()` in `configuration.php` (Lines 31, 68, 101, 133)
- [x] Replace `each()` in `common.php` (Line 84)
- [x] Replace `each()` in `lib/dbwrapper_mysql.php` (Line 84)
- [ ] Search entire codebase for remaining `each()` calls
- [ ] Replace `split()` if present (removed in PHP 7)
- [ ] Replace `create_function()` if present (removed in PHP 7.2)
- [ ] Replace `preg_replace()` with `/e` modifier if present (removed in PHP 5.5)
- [ ] Replace any `mysql_*` function calls outside wrapper

## Phase 3: Error Handling (IN PROGRESS)
- [x] Add null coalescing to `common.php`
- [x] Fix `unserialize()` error handling
- [x] Add isset checks for array access
- [ ] Review all `@` error suppression operators
- [ ] Replace error suppression with proper try-catch where appropriate
- [ ] Check `unserialize()` calls throughout codebase
- [ ] Verify file operation error handling

## Phase 4: Type Safety (PENDING)
- [ ] Add type declarations to function signatures
- [ ] Review null comparisons (use `===` not `==`)
- [ ] Fix implicit type conversions
- [ ] Test with `strict_types=1` in critical files
- [ ] Add return type hints to functions

## Phase 5: Comprehensive Scan (PENDING)
- [ ] Run PHPStan with PHP 8 rules
- [ ] Run PHP_CodeSniffer with PHP 8 standards
- [ ] Manual code review of lib/ directory
- [ ] Manual code review of modules/ directory
- [ ] Check all require/include statements for path issues

## Phase 6: Integration Testing (PENDING)
- [ ] Setup PHP 8.0+ test environment
- [ ] Enable all PHP error reporting
- [ ] Test user login flow
- [ ] Test database operations
- [ ] Test session handling
- [ ] Test admin configuration page
- [ ] Test module system
- [ ] Test player data persistence
- [ ] Test newday functionality
- [ ] Test combat system

## Phase 7: Modules Testing (PENDING)
- [ ] Verify all modules load correctly
- [ ] Test module hooks fire correctly
- [ ] Test module database operations
- [ ] Test module settings

## Phase 8: Production Preparation (PENDING)
- [ ] Create migration documentation
- [ ] Create rollback plan
- [ ] Backup procedures
- [ ] Performance testing
- [ ] Security review

---

## Key Files to Review

### Already Fixed
- ✅ `common.php` - Core initialization
- ✅ `configuration.php` - Admin settings
- ✅ `lib/dbwrapper.php` - Database abstraction
- ✅ `lib/dbwrapper_mysql.php` - MySQL functions
- ✅ `lib/dbwrapper_mysqli_proc.php` - NEW MySQLi wrapper

### Still Need Review
- [ ] `lib/sql.php` - SQL utilities
- [ ] `lib/settings.php` - Settings management
- [ ] `lib/modules.php` - Module system
- [ ] `lib/template.php` - Template engine
- [ ] All files in `lib/` directory
- [ ] All files in root directory
- [ ] Module files

---

## Known PHP 8 Issues Fixed

1. ✅ **Removed mysql_* functions**
   - Replaced with MySQLi equivalents
   - Created procedural wrapper

2. ✅ **Removed each() function**
   - Replaced with foreach loops
   - Manual pointer management where needed

3. ✅ **Variable references in global scope**
   - Updated session reference assignment
   - Added null coalescing operators

4. ✅ **Error suppression operators**
   - Partially addressed with null checks
   - Unserialize errors now handled properly

---

## Testing Commands

```bash
# Check PHP version
php -v

# Check for deprecated functions (requires PHPStan)
vendor/bin/phpstan analyse --level=8

# Check for common issues
grep -r "each(" lib/ --include="*.php"
grep -r "mysql_" lib/ --include="*.php"
grep -r "split(" lib/ --include="*.php"
```

---

## Estimated Completion

- **Phase 1-2**: 80% complete
- **Overall Progress**: ~35% complete
- **Est. Full Completion**: 3-5 more phases needed

---

## Support Resources

- [PHP 8.0 Migration Guide](https://www.php.net/manual/en/migration80.php)
- [PHP 8.1 Migration Guide](https://www.php.net/manual/en/migration81.php)
- [MySQLi Documentation](https://www.php.net/manual/en/book.mysqli.php)
- [Removed Functions Reference](https://www.php.net/manual/en/migration70.removed-exts-sapis-functions.php)
