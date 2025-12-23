<?php
require_once __DIR__ . '/../../controllers/PersonaController.php';
$controller = new PersonaController();
$personas = $controller->index();

// Include common header and sidebar
include __DIR__ . '/../../../../includes/header.php';
include __DIR__ . '/../../../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-navbar">
        <h2>Manage AI Personas</h2>
    </div>

    <div class="card-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Personas List</h3>
            <a href="form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Persona</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Premium</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personas as $p): ?>
                    <tr>
                        <td>#<?php echo $p['PersonaID']; ?></td>
                        <td class="fs-4"><?php echo $p['Icon']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($p['PersonaName']); ?></td>
                        <td><?php echo htmlspecialchars($p['Description']); ?></td>
                        <td>
                            <?php if ($p['IsPremium']): ?>
                                <span class="badge bg-warning text-dark">Premium</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Free</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="form.php?id=<?php echo $p['PersonaID']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete.php?id=<?php echo $p['PersonaID']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>