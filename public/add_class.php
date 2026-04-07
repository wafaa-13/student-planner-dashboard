<?php
require_once '../config/db.php';

$successMessage = '';
$errorMessage = '';
$className = '';

// Handle form submit to create a new class.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');

    if ($className === '') {
        $errorMessage = 'Please enter a class name.';
    } else {
        // Prepared statement keeps SQL safe.
        $stmt = $conn->prepare('INSERT INTO classes (class_name) VALUES (?)');

        if ($stmt) {
            $stmt->bind_param('s', $className);

            if ($stmt->execute()) {
                // Keep success text exactly as requested.
                $successMessage = 'Class added successfully';
                $className = '';
            } else {
                $errorMessage = 'Could not save class. Please try again.';
            }

            $stmt->close();
        } else {
            $errorMessage = 'Could not prepare database query.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="form-card">
        <!-- Navigation only: this link changes page, not database data -->
        <a class="top-back-link" href="gpa.php">← Back to GPA</a>
        <h1>Add Class</h1>

        <?php if ($successMessage !== ''): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="class_name">Class Name</label>
                <input type="text" id="class_name" name="class_name" value="<?php echo htmlspecialchars($className); ?>" required>
            </div>

            <button type="submit">Add Class</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
