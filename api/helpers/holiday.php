<?php
/**
 * holiday.php — Holiday detection helper for ECOZONE Attendance System
 *
 * Usage:  require_once __DIR__ . '/helpers/holiday.php';
 *         $holiday = check_holiday($connection, '2026-05-01');
 *         // Returns: ['holiday_name' => 'Labor Day', 'holiday_type' => 'REGULAR'] or NULL
 */

/**
 * Check whether a given date (YYYY-MM-DD) falls on a holiday.
 *
 * Rules:
 *  1. Year-specific holidays match by exact date + year_specific = $year.
 *  2. Recurring holidays match by month-day portion (any year).
 *  3. Year-specific entries take precedence over recurring ones.
 *
 * @param  mysqli  $connection  Active DB connection
 * @param  string  $date        Date string in YYYY-MM-DD format
 * @return array|null  Associative array with 'holiday_name' and 'holiday_type', or NULL
 */
function check_holiday($connection, $date) {
    $year = (int)date('Y', strtotime($date));

    $stmt = mysqli_prepare($connection,
        "SELECT holiday_name, holiday_type
         FROM holidays
         WHERE
           (holiday_date = ? AND year_specific = ?)
           OR
           (is_recurring = TRUE AND year_specific IS NULL
            AND DATE_FORMAT(holiday_date, '%m-%d') = DATE_FORMAT(?, '%m-%d'))
         ORDER BY year_specific DESC
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "sis", $date, $year, $date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row ? $row : null;
}

/**
 * Human-readable label for a holiday type, including pay rate info.
 */
function holiday_label($holiday_type) {
    switch ($holiday_type) {
        case 'REGULAR':             return 'Regular Holiday (200% pay)';
        case 'SPECIAL_NON_WORKING': return 'Special Non-Working Holiday (130% pay)';
        case 'SPECIAL_WORKING':     return 'Special Working Day';
        case 'COMPANY':             return 'Company Holiday';
        default:                    return 'Holiday';
    }
}
?>
