# PHP 5 to PHP 8 Migration Guide - LoGD 1.1.2

## Overview
This document tracks the migration of LoGD 1.1.2 from PHP 5 to PHP 8. PHP 8 introduced significant breaking changes that require systematic updates.

## Critical Issues to Address

### 1. **Database Extension (HIGHEST PRIORITY)**
**Current Status**: Using deprecated `mysql_*` functions
**Impact**: These functions were removed entirely in PHP 7.0+
**Solution**: Migrate to MySQLi (recommended) or PDO

**Files affected**:
- `lib/dbwrapper.php` - Database abstraction layer
- `lib/dbwrapper_mysql.php` - MySQL wrapper functions
- May need to create new wrappers for MySQLi

**Required functions to replace**:
- `mysql_connect()` → `mysqli_connect()` or `PDO`
- `mysql_query()` → `mysqli_query()` or `PDO::prepare()`
- `mysql_fetch_assoc()` → `mysqli_fetch_assoc()` or `PDOStatement::fetch()`
- `mysql_num_rows()` → `mysqli_num_rows()` or `rowCount()`
- `mysql_insert_id()` → `mysqli_insert_id()` or `PDO::lastInsertId()`
- `mysql_error()` → `mysqli_error()` or exception handling
- `mysql_select_db()` → Select DB in connection string

### 2. **Removed Functions**
**Current Status**: Code uses deprecated functions
**Impact**: Will cause fatal errors in PHP 8

**Issues found**:
- Line 31, 68, 101, 133 in `configuration.php`: `list()` construct with `each()` function
  - `each()` was deprecated in PHP 7.2 and removed in PHP 8
  - Replace with `foreach()` loops instead
  
**Files affected**:
- `common.php` - Line 84: `list($key,$val)=each($result)`
- `configuration.php` - Multiple occurrences: Lines 31, 68, 101, 133

**Replacement pattern**:
```php
// OLD (PHP 5)
while(list($k, $v) = each($array)) {
    // use $k and $v
}

// NEW (PHP 8)
foreach($array as $k => $v) {
    // use $k and $v
}
```

### 3. **Deprecated Error Control Operator**
**Current Status**: Using `@` operator extensively
**Impact**: Still works but not recommended in PHP 8

**Examples**:
- `common.php` Line 335: `$temp_comp = @unserialize($session['user']['companions']);`
- `lib/dbwrapper_mysql.php` Line 74-76, 108: Error suppression

**Solution**: Replace with proper error handling using try/catch or null coalescing

### 4. **String Position Functions**
**Current Status**: Using deprecated parameter options
**Impact**: Inconsistent behavior in PHP 8

**Example in common.php**:
- Line 195: `strrpos()` usage
- Line 278: `strpos()` usage

**Solution**: Ensure return value is checked properly (returns false or 0, not null)

### 5. **Variable References in Parameter List**
**Current Status**: `&$result` parameter passing (PHP 5 style)
**Impact**: Different behavior in PHP 8

**Example in lib/dbwrapper_mysql.php**:
- Line 43: `function &db_query_cached($sql,$name,$duration=900)`
- Line 81: `function db_fetch_assoc(&$result)`

### 6. **Global Variable Assignment**
**Current Status**: Using old-style global references
**Impact**: May not work as expected in PHP 8

**Example in common.php**:
- Line 94: `$session =& $_SESSION['session'];`

**Solution**: Replace with direct array access or modern patterns

### 7. **Null Coalescing Operator**
**Current Status**: Not using modern syntax
**Impact**: Code could be more concise and safer

**Example replacements needed**:
- Line 250 in common.php: Check if `strlen($_COOKIE['lgi'])<32` - better with null coalescing

### 8. **Type Juggling Changes**
**Current Status**: Implicit type conversions
**Impact**: PHP 8 is stricter with types

**Examples**:
- String to int conversions
- Array comparisons
- Null comparisons

---

## Migration Checklist

### Phase 1: Database Layer (CRITICAL)
- [ ] Review `lib/dbwrapper.php` - Plan MySQLi migration
- [ ] Create `lib/dbwrapper_mysqli_proc.php` (procedural style)
- [ ] Test database connection with new wrapper
- [ ] Update `dbconnect.php` to use new connection method
- [ ] Replace all `mysql_*` function calls

### Phase 2: Remove Deprecated Functions
- [ ] Replace all `each()` + `list()` with `foreach()`
  - [ ] `configuration.php` - Lines 31, 68, 101, 133
  - [ ] `common.php` - Line 84
  - [ ] Search entire codebase for `each(` patterns
  
- [ ] Replace `split()` if present (removed in PHP 7)
- [ ] Replace `create_function()` if present (removed in PHP 7.2)
- [ ] Replace `preg_replace()` with `/e` modifier (removed in PHP 5.5)

### Phase 3: Error Handling
- [ ] Review all `@` error suppression operators
- [ ] Replace with proper try/catch blocks where appropriate
- [ ] Update `unserialize()` calls to handle errors
- [ ] Check file operations for proper error handling

### Phase 4: Type Safety
- [ ] Add type declarations to function signatures where applicable
- [ ] Review null comparisons (use `===` not `==`)
- [ ] Fix implicit type conversions
- [ ] Test with `strict_types=1` in critical files

### Phase 5: Testing
- [ ] Set up PHP 8.0+ environment
- [ ] Enable all error reporting
- [ ] Test login flow
- [ ] Test database queries
- [ ] Test module system
- [ ] Test admin configuration page

---

## File-by-File Status

| File | Status | Priority | Notes |
|------|--------|----------|-------|
| `common.php` | ⚠️ Needs Review | HIGH | Uses `each()`, error suppression, global refs |
| `configuration.php` | ⚠️ Needs Review | HIGH | Multiple `each()` + `list()` calls |
| `lib/dbwrapper.php` | 🔴 CRITICAL | CRITICAL | MySQL ext wrapper, needs MySQLi |
| `lib/dbwrapper_mysql.php` | 🔴 CRITICAL | CRITICAL | Calls deprecated functions |
| Other `.php` files | ❓ Unknown | MEDIUM | Need systematic code review |

---

## Testing Strategy

1. **Static Analysis**
   - Run PHPStan with PHP 8 rules
   - Run PHP_CodeSniffer with PHP 8 standards

2. **Integration Testing**
   - Create test database
   - Test user login
   - Test game initialization
   - Test admin configuration panel

3. **Compatibility**
   - Minimum: PHP 8.0
   - Recommended: PHP 8.2+
   - Test on production-like environment

---

## Resources

- [PHP 8.0 Migration Guide](https://www.php.net/manual/en/migration80.php)
- [PHP 8.1 Migration Guide](https://www.php.net/manual/en/migration81.php)
- [MySQLi Documentation](https://www.php.net/manual/en/book.mysqli.php)
- [Removed Functions Reference](https://www.php.net/manual/en/migration70.removed-exts-sapis-functions.php)
