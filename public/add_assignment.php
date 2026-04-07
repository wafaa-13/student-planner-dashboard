<?php
// Use the existing MySQLi connection
require_once '../config/db.php';

$successMessage = '';
$errorMessage = '';

// Keep old values so the form can refill if validation fails
$title = '';
$type = '';
$difficulty = '';
$dueDate = '';

// Run this block only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and trim form values from POST
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $difficulty = trim($_POST['difficulty'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    // Basic validation: all fields are required
    if ($title === '' || $type === '' || $difficulty === '' || $dueDate === '') {
        $errorMessage = 'Please fill in all fields.';
    } else {
        // assignments table currently has columns: title, subject, due_date, priority
        // so we map:
        // - Type -> subject
        // - Difficulty (Easy/Medium/Hard) -> priority (low/medium/high)
        $priorityMap = [
            'Easy' => 'low',
            'Medium' => 'medium',
            'Hard' => 'high'
        ];

        if (!isset($priorityMap[$difficulty])) {
            $errorMessage = 'Invalid difficulty selected.';
        } else {
            $priority = $priorityMap[$difficulty];

            // Prepared statement helps keep SQL safe and simple
            $sql = 'INSERT INTO assignments (title, subject, due_date, priority) VALUES (?, ?, ?, ?)';
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('ssss', $title, $type, $dueDate, $priority);

                if ($stmt->execute()) {
                    $successMessage = 'Assignment added successfully';
                    // Clear fields after successful insert
                    $title = '';
                    $type = '';
                    $difficulty = '';
                    $dueDate = '';
                } else {
                    $errorMessage = 'Could not save assignment. Please try again.';
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
        <h1>Add Assignment</h1>

        <?php if ($successMessage !== ''): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?php echo htmlspecialchars($title); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" required>
                    <option value="">Select type</option>
                    <option value="Exam" <?php echo $type === 'Exam' ? 'selected' : ''; ?>>Exam</option>
                    <option value="Homework" <?php echo $type === 'Homework' ? 'selected' : ''; ?>>Homework</option>
                    <option value="Project" <?php echo $type === 'Project' ? 'selected' : ''; ?>>Project</option>
                </select>
            </div>

            <div class="form-group">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty" required>
                    <option value="">Select difficulty</option>
                    <option value="Easy" <?php echo $difficulty === 'Easy' ? 'selected' : ''; ?>>Easy</option>
                    <option value="Medium" <?php echo $difficulty === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Hard" <?php echo $difficulty === 'Hard' ? 'selected' : ''; ?>>Hard</option>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input
                    type="date"
                    id="due_date"
                    name="due_date"
                    value="<?php echo htmlspecialchars($dueDate); ?>"
                    required
                >
            </div>

            <button type="submit">Add Assignment</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
