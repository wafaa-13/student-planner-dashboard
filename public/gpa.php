<?php
require_once '../config/db.php';

$classRows = [];
$errorMessage = '';

$classQuery = $conn->query('SELECT id, class_name FROM classes ORDER BY class_name ASC');

if ($classQuery instanceof mysqli_result) {
    $gradeStmt = $conn->prepare('SELECT score, weight FROM grades WHERE class_id = ?');

    if ($gradeStmt) {
        while ($classRow = $classQuery->fetch_assoc()) {
            $classId = (int) $classRow['id'];
            $weightedTotal = 0;

            $gradeStmt->bind_param('i', $classId);
            $gradeStmt->execute();
            $gradeResult = $gradeStmt->get_result();

            if ($gradeResult instanceof mysqli_result) {
                while ($grade = $gradeResult->fetch_assoc()) {
                    $weightedTotal += ((float) $grade['score'] * (float) $grade['weight']);
                }
                $gradeResult->free();
            }

            $classRow['final_grade'] = $weightedTotal / 100;
            $classRows[] = $classRow;
        }

        $gradeStmt->close();
    } else {
        $errorMessage = 'Could not prepare grade query.';
    }

    $classQuery->free();
} else {
    $errorMessage = 'Could not read classes table. Please create classes and grades first.';
}

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="dashboard-card">
        <!-- Navigation only: this link changes page, not database data -->
        <a class="top-back-link" href="dashboard.php">← Back to Dashboard</a>
        <h1>GPA Calculator</h1>

        <div class="gpa-explain-box">
            <h2>How Final Grade Is Calculated</h2>
            <p><strong>final grade = sum(score × weight) / 100</strong></p>
            <p>Example: Homework 80 (40%), Exam 90 (60%) → (80×40 + 90×60)/100 = 86.</p>
        </div>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Final Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($classRows) > 0): ?>
                    <?php foreach ($classRows as $class): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                            <td><?php echo htmlspecialchars((string) number_format((float) $class['final_grade'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="empty-row">No class data found yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
