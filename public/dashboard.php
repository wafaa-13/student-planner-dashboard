<?php
// Use existing database connection
require_once '../config/db.php';

// Get all assignments ordered by nearest due date first
$sql = 'SELECT title, subject, priority, due_date FROM assignments ORDER BY due_date ASC';
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

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="dashboard-card">
        <h1>Assignments Dashboard</h1>

        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Difficulty</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['priority'])); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td><?php echo htmlspecialchars(getDueStatus($row['due_date'], $today, $tomorrow)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-row">No assignments found yet.</td>
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
