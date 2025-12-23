<?php
require_once __DIR__ . '/../../controllers/PersonaController.php';
$controller = new PersonaController();

// Xử lý submit form
$controller->save();

// Nếu có ID -> Lấy dữ liệu để sửa
$persona = null;
if (isset($_GET['id'])) {
    $persona = $controller->edit($_GET['id']);
}

// Include common header and sidebar
include __DIR__ . '/../../../../includes/header.php';
include __DIR__ . '/../../../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-navbar">
        <h2><?php echo $persona ? 'Edit Persona' : 'Create New Persona'; ?></h2>
    </div>

    <div class="card-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><?php echo $persona ? 'Edit Persona Details' : 'Add New Persona'; ?></h3>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>

        <form method="POST">
            <?php if ($persona): ?>
                <input type="hidden" name="PersonaID" value="<?php echo $persona['PersonaID']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Persona Name</label>
                    <input type="text" class="form-control" name="PersonaName" value="<?php echo $persona['PersonaName'] ?? ''; ?>" required placeholder="e.g. Friendly Bot">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Icon (Emoji)</label>
                    <input type="text" class="form-control" name="Icon" value="<?php echo $persona['Icon'] ?? '🤖'; ?>" required placeholder="e.g. 🤖">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Short Description</label>
                <input type="text" class="form-control" name="Description" value="<?php echo $persona['Description'] ?? ''; ?>" required placeholder="Brief description for user selection">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-primary">System Prompt (The Brain)</label>
                <textarea class="form-control" name="SystemPrompt" rows="6" required placeholder="Define how the AI should behave..."><?php echo $persona['SystemPrompt'] ?? ''; ?></textarea>
                <div class="form-text">This is the most important part. Instruct the AI on its tone, style, and constraints.</div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="IsPremium" id="isPremium" <?php echo ($persona['IsPremium'] ?? 0) ? 'checked' : ''; ?>>
                <label class="form-check-label fw-bold" for="isPremium">Is Premium Feature?</label>
            </div>

            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save"></i> Save Persona</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>