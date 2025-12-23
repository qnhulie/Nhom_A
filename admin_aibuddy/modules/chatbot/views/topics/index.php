<?php
require_once __DIR__ . '/../../controllers/TopicController.php';
$controller = new TopicController();
$topics = $controller->index();

// Include common header and sidebar
include __DIR__ . '/../../../../includes/header.php';
include __DIR__ . '/../../../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-navbar">
        <h2>Manage Topics</h2>
    </div>

    <div class="card-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Topics List</h3>
            <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Topic</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Topic Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topics as $t): ?>
                    <tr>
                        <td>#<?php echo $t['TopicID']; ?></td>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($t['TopicName']); ?></td>
                        <td><?php echo htmlspecialchars($t['Description'] ?? ''); ?></td>
                        <td>
                            <a href="form.php?id=<?php echo $t['TopicID']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $t['TopicID']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>