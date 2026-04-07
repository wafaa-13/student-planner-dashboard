<?php
require_once '../config/db.php';

$successMessage = '';
$errorMessage = '';
$classId = '';
$componentName = '';
$score = '';
$weight = '';
$classOptions = [];

// Load class list for dropdown.
$classResult = $conn->query('SELECT id, class_name FROM classes ORDER BY class_name ASC');
if ($classResult instanceof mysqli_result) {
    while ($classRow = $classResult->fetch_assoc()) {
        $classOptions[] = $classRow;
    }
    $classResult->free();
}

// Handle form submit to add a grade component.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = trim($_POST['class_id'] ?? '');
    $componentName = trim($_POST['component_name'] ?? '');
    $score = trim($_POST['score'] ?? '');
    $weight = trim($_POST['weight'] ?? '');

    if ($classId === '' || $componentName === '' || $score === '' || $weight === '') {
        $errorMessage = 'Please fill in all fields.';
    } elseif (!is_numeric($score) || !is_numeric($weight)) {
        $errorMessage = 'Score and weight must be numbers.';
    } else {
        $scoreNumber = (float) $score;
        $weightNumber = (float) $weight;

        if ($scoreNumber < 0 || $scoreNumber > 100 || $weightNumber < 0 || $weightNumber > 100) {
            $errorMessage = 'Score and weight must be between 0 and 100.';
        } else {
            // Prepared statement with class_id from dropdown.
            $stmt = $conn->prepare(
                'INSERT INTO grades (class_id, component_name, score, weight) VALUES (?, ?, ?, ?)'
            );

            if ($stmt) {
                $classIdNumber = (int) $classId;
                $stmt->bind_param('isdd', $classIdNumber, $componentName, $scoreNumber, $weightNumber);

                if ($stmt->execute()) {
                    $successMessage = 'Grade component added successfully.';
                    $classId = '';
                    $componentName = '';
                    $score = '';
                    $weight = '';
                } else {
                    $errorMessage = 'Could not save grade component. Please try again.';
                }

                $stmt->close();
            } else {
                $errorMessage = 'Could not prepare database query.';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="form-card">
        <h1>Add Grade</h1>

        <?php if ($successMessage !== ''): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" required>
                    <option value="">Select class</option>
                    <?php foreach ($classOptions as $classOption): ?>
                        <option value="<?php echo htmlspecialchars((string) $classOption['id']); ?>" <?php echo $classId === (string) $classOption['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($classOption['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="component_name">Component Name</label>
                <input type="text" id="component_name" name="component_name" value="<?php echo htmlspecialchars($componentName); ?>" required>
            </div>

            <div class="form-group">
                <label for="score">Score</label>
                <input type="number" id="score" name="score" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($score); ?>" required>
            </div>

            <div class="form-group">
                <label for="weight">Weight (%)</label>
                <input type="number" id="weight" name="weight" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($weight); ?>" required>
            </div>

            <button type="submit">Add Grade</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
