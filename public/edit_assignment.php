<?php
require_once '../config/db.php';

$successMessage = '';
$errorMessage = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
}

if ($id <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

$title = '';
$type = '';
$difficulty = '';
$dueDate = '';

// Convert DB priority to form-friendly difficulty text
function priorityToDifficulty(string $priority): string
{
    $map = [
        'low' => 'Easy',
        'medium' => 'Medium',
        'high' => 'Hard'
    ];

    return $map[strtolower($priority)] ?? 'Easy';
}

// Convert form difficulty text back to DB priority
function difficultyToPriority(string $difficulty): string
{
    $map = [
        'Easy' => 'low',
        'Medium' => 'medium',
        'Hard' => 'high'
    ];

    return $map[$difficulty] ?? 'low';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read submitted values
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $difficulty = trim($_POST['difficulty'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($title === '' || $type === '' || $difficulty === '' || $dueDate === '') {
        $errorMessage = 'Please fill in all fields.';
    } else {
        $priority = difficultyToPriority($difficulty);

        // Update this assignment by ID
        $sql = 'UPDATE assignments SET title = ?, subject = ?, priority = ?, due_date = ? WHERE id = ?';
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ssssi', $title, $type, $priority, $dueDate, $id);

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: dashboard.php?message=updated');
                exit;
            }

            $errorMessage = 'Could not update assignment. Please try again.';
            $stmt->close();
        } else {
            $errorMessage = 'Could not prepare database query.';
        }
    }
}

// Fetch current assignment values (first load or if validation failed)
$sql = 'SELECT id, title, subject, priority, due_date FROM assignments WHERE id = ? LIMIT 1';
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header('Location: dashboard.php?error=query_failed');
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();
$stmt->close();

if (!$assignment) {
    header('Location: dashboard.php?error=not_found');
    exit;
}

// Fill form with DB values on first page load
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $title = $assignment['title'];
    $type = $assignment['subject'];
    $difficulty = priorityToDifficulty($assignment['priority']);
    $dueDate = $assignment['due_date'];
}

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="form-card">
        <h1>Edit Assignment</h1>

        <?php if ($successMessage !== ''): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $id); ?>">

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

            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
