<?php
require_once '../includes/header.php';
?>

<div class="page-wrapper">
    <div class="form-card">
        <h1>Student Planner Dashboard</h1>
        <p>Welcome! Use this project to keep track of your coursework and GPA progress.</p>
        <p>
            Start by adding your first assignment:
            <a href="add_assignment.php">Add Assignment</a>
        </p>
        <p>
            Manage GPA data:
            <a href="add_class.php">Add Class</a> |
            <a href="add_grade.php">Add Grade</a>
        </p>
        <p>
            View your full dashboard (assignments + class final grades):
            <a href="dashboard.php">Open Dashboard</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
