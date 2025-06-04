<?php
require_once __DIR__ . '/header.php';

$changelogPath = __DIR__ . '/../GSHQ/changelog.json';
$changelogData = [];

if (file_exists($changelogPath)) {
    $jsonContent = file_get_contents($changelogPath);
    $changelogData = json_decode($jsonContent, true);
    if (!is_array($changelogData)) {
        $changelogData = [];
    }
}

$versions = [];
if (!empty($changelogData['versions']) && is_array($changelogData['versions'])) {
    foreach ($changelogData['versions'] as $versionGroup) {
        if (is_array($versionGroup)) {
            foreach ($versionGroup as $version => $data) {
                $versions[$version] = $data;
            }
        }
    }

    // Trier par version décroissante
    uksort($versions, 'version_compare');
    $versions = array_reverse($versions, true);
}
?>
<div class="col-md-12">
    <div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-light">
            <h5 class="mb-0">🚀 GSHQ - Changelog</h5>
        </div>
        <div class="card-body">
            <?php if (empty($versions)) : ?>
                <p>No changelog entries found.</p>
            <?php else : ?>
                <div class="accordion" id="changelogAccordion">
                    <?php
                    $i = 0;
                    foreach ($versions as $version => $data):
                        $collapseId = "collapseVersion$i";
                        $headingId = "headingVersion$i";
                        $date = $data['date'] ?? 'Date inconnue';
                        $changes = $data['changes'] ?? [];
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="<?= $headingId ?>">
                            <button class="accordion-button <?= $i !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
                                Version <?= htmlspecialchars($version) ?> – <small class="text-muted ms-2"><?= htmlspecialchars($date) ?></small>
                            </button>
                        </h2>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" aria-labelledby="<?= $headingId ?>" data-bs-parent="#changelogAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <?php foreach ($changes as $change): ?>
                                        <li><?= htmlspecialchars($change) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>