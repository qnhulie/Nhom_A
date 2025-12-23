<?php
require_once __DIR__ . '/../../controllers/TopicController.php';
$controller = new TopicController();
$controller->save();

$topic = null;
if (isset($_GET['id'])) {
    $topic = $controller->edit($_GET['id']);
}

// Include common header and sidebar
include __DIR__ . '/../../../../includes/header.php';
include __DIR__ . '/../../../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-navbar">
        <h2><?php echo $topic ? 'Edit Topic' : 'Create New Topic'; ?></h2>
    </div>

    <div class="card-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><?php echo $topic ? 'Edit Topic Details' : 'Add New Topic'; ?></h3>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>

        <form method="POST">
            <?php if ($topic): ?>
                <input type="hidden" name="TopicID" value="<?php echo $topic['TopicID']; ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Topic Name</label>
                <input type="text" class="form-control" name="TopicName" value="<?php echo $topic['TopicName'] ?? ''; ?>" required placeholder="e.g. Exam Stress">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Description</label>
                <textarea class="form-control" name="Description" rows="3" required placeholder="What is this topic about?"><?php echo $topic['Description'] ?? ''; ?></textarea>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Topic</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>