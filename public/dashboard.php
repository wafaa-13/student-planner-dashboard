<?php
// Use existing database connection
require_once '../config/db.php';

// Show small feedback messages after delete or edit actions
$successMessage = '';
$errorMessage = '';

if (isset($_GET['message'])) {
    if ($_GET['message'] === 'deleted') {
        $successMessage = 'Assignment deleted successfully.';
    } elseif ($_GET['message'] === 'updated') {
        $successMessage = 'Assignment updated successfully.';
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'not_found') {
        $errorMessage = 'Assignment not found.';
    } elseif ($_GET['error'] === 'invalid_id') {
        $errorMessage = 'Invalid assignment ID.';
    } else {
        $errorMessage = 'Something went wrong. Please try again.';
    }
}

// Get all assignments ordered by nearest due date first
$sql = 'SELECT id, title, subject, priority, due_date FROM assignments ORDER BY due_date ASC';
$result = $conn->query($sql);

// Build date objects once for status comparison
$today = new DateTime('today');
$tomorrow = new DateTime('tomorrow');

/**
 * Convert due date into a simple status label.
 */
function getDueStatus(string $dueDate, DateTime $today, DateTime $tomorrow): string
{
    $due = new DateTime($dueDate);

    if ($due->format('Y-m-d') === $today->format('Y-m-d')) {
        return 'Due Today';
    }

    if ($due->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
        return 'Due Tomorrow';
    }

    // Difference in whole days from today to due date
    $daysLeft = (int) $today->diff($due)->format('%r%a');

    // Keep wording simple for past and future dates
    if ($daysLeft < 0) {
        $daysPast = abs($daysLeft);
        return $daysPast . ' day' . ($daysPast === 1 ? '' : 's') . ' overdue';
    }

    return $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left';
}

/**
 * Return a CSS class for each status text.
 */
function getStatusClass(string $statusText): string
{
    if ($statusText === 'Due Today') {
        return 'status-red';
    }

    if ($statusText === 'Due Tomorrow') {
        return 'status-orange';
    }

    return 'status-green';
}

/**
 * Estimate study time in hours using assignment difficulty and type.
 * The final estimate is: base time (difficulty) + extra time (type).
 */
function getEstimatedTime(string $difficulty, string $type): string
{
    // Normalize values so comparisons work even if data has mixed letter casing
    $difficulty = strtolower(trim($difficulty));
    $type = strtolower(trim($type));

    // Base time from difficulty level
    switch ($difficulty) {
        case 'low':
            $baseHours = 1;
            break;
        case 'medium':
            $baseHours = 3;
            break;
        case 'high':
            $baseHours = 5;
            break;
        default:
            // Safe fallback if difficulty is missing or unexpected
            $baseHours = 1;
            break;
    }

    // Additional time from assignment type
    switch ($type) {
        case 'exam':
            $extraHours = 2;
            break;
        case 'project':
            $extraHours = 4;
            break;
        case 'homework':
        default:
            // Homework adds no extra time; also used as fallback
            $extraHours = 0;
            break;
    }

    $totalHours = $baseHours + $extraHours;
    return $totalHours . ' hours';
}

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="dashboard-card">
        <h1>Assignments Dashboard</h1>

        <?php if ($successMessage !== ''): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Difficulty</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Estimated Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $statusText = getDueStatus($row['due_date'], $today, $tomorrow);
                        $statusClass = getStatusClass($statusText);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['priority'])); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>">
                                    <?php echo htmlspecialchars($statusText); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(getEstimatedTime($row['priority'], $row['subject'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <!-- Edit opens a page with current values pre-filled -->
                                    <a class="btn-action btn-edit" href="edit_assignment.php?id=<?php echo urlencode($row['id']); ?>">Edit</a>

                                    <!-- Delete uses POST so accidental URL visits do not delete records -->
                                    <form method="POST" action="delete_assignment.php" onsubmit="return confirm('Delete this assignment?');">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $row['id']); ?>">
                                        <button type="submit" class="btn-action btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-row">No assignments found yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Free result resources when finished
if ($result instanceof mysqli_result) {
    $result->free();
}

require_once '../includes/footer.php';
?>
