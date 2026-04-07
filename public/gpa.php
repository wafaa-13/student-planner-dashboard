<?php
// Reuse the same MySQLi connection used in other pages.
require_once '../config/db.php';

$classRows = [];
$classNames = [];
$finalGrades = [];
$totalFinalGrades = 0;
$overallAverage = 0;
$errorMessage = '';

// Fetch all class records from the simple classes table.
$sql = 'SELECT id, class_name, score, weight FROM classes ORDER BY class_name ASC';
$result = $conn->query($sql);

if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        // Formula requested: final_grade = (score * weight) / 100
        $finalGrade = ((float) $row['score'] * (float) $row['weight']) / 100;

        $row['final_grade'] = $finalGrade;
        $classRows[] = $row;

        // Build arrays in PHP first. We pass these arrays to JS later for Chart.js.
        $classNames[] = $row['class_name'];
        $finalGrades[] = round($finalGrade, 2);

        $totalFinalGrades += $finalGrade;
    }

    // Keep overall result simple: average of all final grades.
    if (count($classRows) > 0) {
        $overallAverage = $totalFinalGrades / count($classRows);
    }

    $result->free();
} else {
    // Friendly error if table does not exist yet.
    $errorMessage = 'Could not read the classes table. Please create it first in your database.';
}

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="dashboard-card">
        <h1>GPA Calculator</h1>

        <div class="gpa-explain-box">
            <h2>How GPA / Final Grade Works (Simple)</h2>
            <p>
                We use a weighted average. For each class:
                <strong>final grade = (score × weight) / 100</strong>.
            </p>
            <p>
                Example: If your score is 80 and weight is 25%, then final grade = (80 × 25) / 100 = 20.
            </p>
            <p>
                For the chart, PHP prepares arrays (class names and final grades), converts them to JSON,
                and JavaScript reads that JSON to draw the Chart.js bar chart.
            </p>
        </div>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Score</th>
                    <th>Weight (%)</th>
                    <th>Final Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($classRows) > 0): ?>
                    <?php foreach ($classRows as $class): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                            <td><?php echo htmlspecialchars((string) $class['score']); ?></td>
                            <td><?php echo htmlspecialchars((string) $class['weight']); ?></td>
                            <td><?php echo htmlspecialchars((string) number_format((float) $class['final_grade'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-row">No class records found. Add data to the classes table.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="gpa-summary">
            <strong>Overall GPA / Average: <?php echo number_format($overallAverage, 2); ?></strong>
        </div>

        <div class="chart-card">
            <canvas id="gpaBarChart" height="120"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // PHP arrays converted to JSON strings, then used directly in JavaScript.
    const classLabels = <?php echo json_encode($classNames); ?>;
    const classFinalGrades = <?php echo json_encode($finalGrades); ?>;

    const chartContext = document.getElementById('gpaBarChart');

    if (chartContext && classLabels.length > 0) {
        new Chart(chartContext, {
            type: 'bar',
            data: {
                labels: classLabels,
                datasets: [{
                    label: 'Final Grade',
                    data: classFinalGrades,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Final Grade'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Classes'
                        }
                    }
                }
            }
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>
