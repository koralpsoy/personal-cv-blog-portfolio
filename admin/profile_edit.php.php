<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        // Allgemeine Einstellungen speichern
        $settings_to_save = ['profile_name', 'profile_title', 'hero_kicker', 'hero_headline', 'hero_text'];
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($settings_to_save as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([':key' => $key, ':value' => $_POST[$key]]);
            }
        }

        // Bild verarbeiten
        if (!empty($_POST['cropped_image'])) {
            $data = $_POST['cropped_image'];
            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
            $data = base64_decode($data);
            $upload_dir = __DIR__ . '/../assets/img/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
            $filename = 'profile-' . uniqid() . '.png';
            $target_file = $upload_dir . $filename;
            if (file_put_contents($target_file, $data)) {
                $new_image_path = 'assets/img/' . $filename;
                $img_stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('profile_image_url', :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $img_stmt->execute([':value' => $new_image_path]);
            }
        }

        // Social Links verarbeiten
        if (isset($_POST['links'])) {
            $update_stmt = $pdo->prepare("UPDATE social_links SET name = :name, value = :value, url = :url, sort_order = :sort_order WHERE id = :id");
            $insert_stmt = $pdo->prepare("INSERT INTO social_links (name, value, url, sort_order) VALUES (:name, :value, :url, :sort_order)");
            $delete_stmt = $pdo->prepare("DELETE FROM social_links WHERE id = :id");
            foreach ($_POST['links'] as $link) {
                $id = $link['id'] ?? 0; $name = trim($link['name'] ?? '');
                if (isset($link['delete']) && $id) { $delete_stmt->execute([':id' => $id]); }
                elseif (!empty($name)) {
                    $params = [':name' => $name, ':value' => trim($link['value'] ?? ''), ':url' => trim($link['url'] ?? ''), ':sort_order' => (int)($link['sort_order'] ?? 0)];
                    if ($id) { $params[':id'] = $id; $update_stmt->execute($params); }
                    else { $insert_stmt->execute($params); }
                }
            }
        }
        header('Location: profile_edit.php?msg=saved');
        exit;
    }
}

$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$social_links = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll();
$all_icons = get_all_icons_categorized();

include __DIR__ . '/../includes/header.php';
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">

<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-semibold text-white mb-6">Profil & Startseite bearbeiten</h1>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
        <p class="mb-4 text-sm text-emerald-300">Änderungen erfolgreich gespeichert.</p>
    <?php endif; ?>
    <?php if($error): ?><p class="mb-4 text-sm text-rose-300"><?= h($error) ?></p><?php endif; ?>

    <form method="post" class="space-y-8" enctype="multipart/form-data" id="profile-form">
        <?= csrf_token_field() ?>
        <input type="hidden" name="cropped_image" id="cropped_image_data">

        <div class="panel p-6">
            <h2 class="text-xl font-semibold">Sidebar & Kontakt</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label for="profile_name" class="label">Dein Name</label><input id="profile_name" name="profile_name" type="text" value="<?= h($settings_raw['profile_name'] ?? '') ?>" class="input-field"></div>
                <div><label for="profile_title" class="label">Titel/Berufsbezeichnung</label><input id="profile_title" name="profile_title" type="text" value="<?= h($settings_raw['profile_title'] ?? '') ?>" class="input-field"></div>
            </div>
            
            <div class="mt-4">
                <label class="label">Profilbild</label>
                <div class="flex items-center gap-4">
                    <img id="image-preview" src="../<?= h($settings_raw['profile_image_url'] ?? 'assets/koralp.jpg') ?>" alt="Vorschau" class="h-16 w-16 rounded-full object-cover">
                    <input id="image-input" type="file" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-zinc-800 file:text-zinc-300 hover:file:bg-zinc-700">
                </div>
            </div>

            <h3 class="text-lg font-semibold mt-6 mb-3">Kontakt & Social Media Links</h3>
            <div id="social-links-container" class="space-y-3">
                <div class="grid grid-cols-[100px,1fr,1fr,80px,80px] gap-2 text-xs text-zinc-400 px-1 pb-1">
                    <label>Icon</label><label>Anzeigetext</label><label>URL</label><label>Sort.</label><label>Löschen</label>
                </div>
                <?php foreach ($social_links as $link): ?>
                    <div class="link-row grid grid-cols-[100px,1fr,1fr,80px,80px] gap-2 items-center">
                        <input type="hidden" class="icon-name-input" name="links[<?= $link['id'] ?>][id]" value="<?= $link['id'] ?>">
                        <input type="hidden" class="icon-name-input" name="links[<?= $link['id'] ?>][name]" value="<?= h($link['name']) ?>">
                        
                        <button type="button" class="choose-icon-btn flex items-center justify-center gap-2 p-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 transition">
                            <span class="icon-preview text-zinc-300"><?= get_social_icon_svg(h($link['name']), 'w-5 h-5') ?></span>
                        </button>

                        <input type="text" name="links[<?= $link['id'] ?>][value]" placeholder="z.B. soy@koralp.de" value="<?= h($link['value']) ?>" class="input-field">
                        <input type="url" name="links[<?= $link['id'] ?>][url]" placeholder="mailto:soy@koralp.de" value="<?= h($link['url']) ?>" class="input-field">
                        <input type="number" name="links[<?= $link['id'] ?>][sort_order]" value="<?= h($link['sort_order']) ?>" class="input-field text-center">
                        <label class="flex justify-center items-center"><input type="checkbox" name="links[<?= $link['id'] ?>][delete]" class="h-4 w-4 bg-zinc-700 border-zinc-600 rounded"></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-social-link" class="mt-4 text-sm text-blue-400 hover:text-blue-300">+ Neuen Link hinzufügen</button>
        </div>

        <div class="panel p-6">
            <h2 class="text-xl font-semibold">Hero-Sektion (Startseite)</h2>
            <div class="mt-4 space-y-4">
                <div><label for="hero_kicker" class="label">Kicker</label><input id="hero_kicker" name="hero_kicker" type="text" value="<?= h($settings_raw['hero_kicker'] ?? '') ?>" class="input-field"></div>
                <div><label for="hero_headline" class="label">Headline</label><textarea id="hero_headline" name="hero_headline" class="input-field min-h-[80px]"><?= h($settings_raw['hero_headline'] ?? '') ?></textarea></div>
                <div><label for="hero_text" class="label">Text darunter (HTML erlaubt)</label><textarea id="hero_text" name="hero_text" class="input-field min-h-[120px]"><?= h($settings_raw['hero_text'] ?? '') ?></textarea></div>
            </div>
        </div>
        
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow">Alle Änderungen speichern</button>
    </form>
</div>

<!-- Modal für Icon-Auswahl -->
<div id="icon-picker-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="bg-zinc-900 rounded-xl shadow-2xl p-4 sm:p-6 w-full max-w-2xl max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold">Icon auswählen</h3>
            <button type="button" id="close-icon-modal" class="p-2 rounded-full hover:bg-zinc-700">&times;</button>
        </div>
        <div class="border-b border-zinc-700 -mx-4 sm:-mx-6 px-4 sm:px-6">
            <div id="icon-tabs" class="flex items-center gap-1 overflow-x-auto">
                <?php $is_first_cat = true; foreach ($all_icons as $category => $icons): ?>
                    <button type="button" class="icon-tab-btn whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 <?= $is_first_cat ? 'border-blue-500 text-white' : 'border-transparent text-zinc-400 hover:text-white' ?>" data-category="<?= h(str_replace(' ', '-', strtolower($category))) ?>">
                        <?= h($category) ?>
                    </button>
                <?php $is_first_cat = false; endforeach; ?>
            </div>
        </div>
        <div id="icon-grids" class="flex-grow overflow-y-auto pt-4 pr-2">
            <?php $is_first_cat = true; foreach ($all_icons as $category => $icons): ?>
                <div class="icon-grid grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2 <?= $is_first_cat ? '' : 'hidden' ?>" id="grid-<?= h(str_replace(' ', '-', strtolower($category))) ?>">
                    <?php foreach ($icons as $icon): ?>
                        <div class="icon-item aspect-square flex flex-col items-center justify-center p-2 rounded-lg bg-zinc-800/50 hover:bg-zinc-700/80 cursor-pointer transition" 
                             data-icon-name="<?= h($icon['name']) ?>" title="<?= h($icon['label']) ?>">
                            <?= get_social_icon_svg($icon['name'], 'w-7 h-7 text-zinc-300') ?>
                            <span class="text-xs text-zinc-400 mt-2 text-center truncate w-full"><?= h($icon['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php $is_first_cat = false; endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal für den Bild-Editor -->
<div id="cropper-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="bg-zinc-900 rounded-xl p-6 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Bild zuschneiden</h3>
        <div class="w-full h-80 bg-black"><img id="image-to-crop" src=""></div>
        <div class="mt-4 flex justify-end gap-3">
            <button type="button" id="cancel-crop" class="px-4 py-2 rounded-lg bg-zinc-700 hover:bg-zinc-600">Abbrechen</button>
            <button type="button" id="confirm-crop" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500">Zuschneiden & Übernehmen</button>
        </div>
    </div>
</div>

<style>
.input-field{background-color:rgba(39,39,42,.6);border:1px solid #3f3f46;border-radius:8px;padding:8px 12px;width:100%}
.label{display:block;font-size:14px;font-weight:500;color:#d4d4d8;margin-bottom:4px}
/* Scrollbar-Styling für Icon-Picker */
#icon-grids::-webkit-scrollbar { width: 8px; }
#icon-grids::-webkit-scrollbar-track { background: #27272a; }
#icon-grids::-webkit-scrollbar-thumb { background: #52525b; border-radius: 4px; }
#icon-grids::-webkit-scrollbar-thumb:hover { background: #71717a; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Bild-Editor Logik ---
    const cropperModal=document.getElementById('cropper-modal'),imageInput=document.getElementById('image-input'),imageToCrop=document.getElementById('image-to-crop'),confirmBtn=document.getElementById('confirm-crop'),cancelBtn=document.getElementById('cancel-crop'),preview=document.getElementById('image-preview'),hiddenInput=document.getElementById('cropped_image_data');
    let cropper;
    imageInput.addEventListener('change',e=>{const files=e.target.files;if(files&&files.length>0){const reader=new FileReader;reader.onload=()=>{imageToCrop.src=reader.result,cropperModal.classList.remove('hidden'),cropperModal.classList.add('flex'),cropper=new Cropper(imageToCrop,{aspectRatio:1,viewMode:1,background:!1})},reader.readAsDataURL(files[0])}});
    confirmBtn.addEventListener('click',()=>{if(cropper){const canvas=cropper.getCroppedCanvas({width:512,height:512}),dataUrl=canvas.toDataURL('image/png');preview.src=dataUrl,hiddenInput.value=dataUrl,closeCropperModal()}});
    cancelBtn.addEventListener('click',closeCropperModal);
    function closeCropperModal(){if(cropper){cropper.destroy();cropper=null;} cropperModal.classList.add('hidden');cropperModal.classList.remove('flex');imageInput.value='';}

    // --- Icon-Picker Logik ---
    const iconModal = document.getElementById('icon-picker-modal');
    const linksContainer = document.getElementById('social-links-container');
    let activeLinkRow = null;

    // Event Delegation für "Icon auswählen"-Buttons
    linksContainer.addEventListener('click', function(e) {
        const button = e.target.closest('.choose-icon-btn');
        if (button) {
            activeLinkRow = button.closest('.link-row');
            iconModal.classList.remove('hidden');
            iconModal.classList.add('flex');
        }
    });

    // Schließen des Icon-Modals (X und Overlay)
    document.getElementById('close-icon-modal').addEventListener('click', closeIconModal);
    iconModal.addEventListener('click', function(e){ if(e.target === iconModal){ closeIconModal(); } });
    function closeIconModal(){ iconModal.classList.add('hidden'); iconModal.classList.remove('flex'); activeLinkRow = null; }

    // Tab-Wechsel im Icon-Modal
    document.getElementById('icon-tabs').addEventListener('click', function(e) {
        const tabButton = e.target.closest('.icon-tab-btn');
        if (tabButton) {
            document.querySelectorAll('.icon-tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-white');
                btn.classList.add('border-transparent', 'text-zinc-400');
            });
            tabButton.classList.add('border-blue-500', 'text-white');
            tabButton.classList.remove('border-transparent', 'text-zinc-400');

            document.querySelectorAll('.icon-grid').forEach(grid => grid.classList.add('hidden'));
            document.getElementById('grid-' + tabButton.dataset.category).classList.remove('hidden');
        }
    });

    // Icon-Auswahl im Grid (FIX: unterstützt SVG *und* IMG)
    document.getElementById('icon-grids').addEventListener('click', function(e) {
        const iconItem = e.target.closest('.icon-item');
        if (iconItem && activeLinkRow) {
            const iconName = iconItem.dataset.iconName;

            // Hidden input updaten
            const nameInput = activeLinkRow.querySelector('.icon-name-input[name$="[name]"]');
            if (nameInput) nameInput.value = iconName;
            
            // Preview updaten – svg ODER img akzeptieren
            const el = iconItem.querySelector('svg, img');
            const preview = activeLinkRow.querySelector('.icon-preview');
            if (preview) preview.innerHTML = el ? el.outerHTML : '';
            
            // Modal schließen
            closeIconModal();
        }
    });

    // --- Neuen Link hinzufügen ---
    document.getElementById('add-social-link').addEventListener('click', function() {
        const timestamp = Date.now();
        const newRow = document.createElement('div');
        newRow.className = 'link-row grid grid-cols-[100px,1fr,1fr,80px,80px] gap-2 items-center';
        
        newRow.innerHTML = `
            <input type="hidden" class="icon-name-input" name="links[${timestamp}][name]" value="link">
            <button type="button" class="choose-icon-btn flex items-center justify-center gap-2 p-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 transition">
                <span class="icon-preview text-zinc-300"><?= get_social_icon_svg('link', 'w-5 h-5') ?></span>
            </button>
            <input type="text" name="links[${timestamp}][value]" placeholder="Anzeigetext" class="input-field">
            <input type="url" name="links[${timestamp}][url]" placeholder="https://..." class="input-field">
            <input type="number" name="links[${timestamp}][sort_order]" value="99" class="input-field text-center">
            <div class="flex justify-center items-center text-transparent">_</div>
        `;
        linksContainer.appendChild(newRow);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
