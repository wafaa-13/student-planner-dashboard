<?php
require_once '../config/db.php';

$successMessage = '';
$errorMessage = '';

// Handle form submit with POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim values to avoid spaces-only input
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $difficulty = trim($_POST['difficulty'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    // Basic required field validation
    if ($title === '' || $type === '' || $difficulty === '' || $dueDate === '') {
        $errorMessage = 'Please fill in all required fields.';
    } else {
        // Map UI values to current DB columns in assignments table
        // type -> subject, difficulty -> priority
        $insertSql = 'INSERT INTO assignments (title, subject, due_date, priority) VALUES (?, ?, ?, ?)';
        $statement = $conn->prepare($insertSql);

        if ($statement) {
            $statement->bind_param('ssss', $title, $type, $dueDate, $difficulty);

            if ($statement->execute()) {
                $successMessage = 'Assignment added successfully';
            } else {
                $errorMessage = 'Could not save assignment. Please try again.';
            }

            $statement->close();
        } else {
            $errorMessage = 'Could not prepare database query.';
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
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" required>
                    <option value="">Select type</option>
                    <option value="Exam">Exam</option>
                    <option value="Homework">Homework</option>
                    <option value="Project">Project</option>
                </select>
            </div>

            <div class="form-group">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty" required>
                    <option value="">Select difficulty</option>
                    <option value="low">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="high">Hard</option>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>

            <button type="submit">Add Assignment</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
