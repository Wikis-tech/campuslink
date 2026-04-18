<?php defined('CAMPUSLINK') or die(); ?>

<style>
    .profile-edit-hero {
        background: linear-gradient(135deg, #0b3d91 0%, #1e5bb8 50%, #1a4fa8 100%);
        color: #ffffff;
        padding: 3rem 2rem;
        border-radius: 24px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(11, 61, 145, 0.12);
    }

    .profile-edit-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08), transparent 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .profile-edit-hero > * {
        position: relative;
        z-index: 1;
    }

    .profile-edit-title {
        font-size: 2rem;
        font-weight: 800;
        margin: 0 0 0.5rem;
        letter-spacing: -0.01em;
    }

    .profile-edit-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0 0 1rem;
    }

    .profile-preview-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        padding: 0.75rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease-out;
    }

    .profile-preview-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }
</style>

<div class="profile-edit-hero">
    <h1 class="profile-edit-title">Edit Your Profile</h1>
    <p class="profile-edit-subtitle">Keep your business information accurate and compelling — updates take effect immediately.</p>
    <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>" 
       target="_blank" 
       class="profile-preview-btn">
        <i data-lucide="eye" style="width:16px;height:16px;"></i>
        View Public Profile
    </a>
</div>

<form action="<?= SITE_URL ?>/vendor/profile"
      method="POST"
      enctype="multipart/form-data"
      class="vendor-profile-form"
      novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="dashboard-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">

        <!-- Left Column: Business Information -->
        <div>
            <div class="dash-card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 100%); backdrop-filter: blur(10px); border: 1px solid rgba(11, 61, 145, 0.08); border-radius: 20px; padding: 2rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04); transition: all 0.3s ease;">
                <div class="dash-card-header" style="margin-bottom: 1.5rem;">
                    <div class="dash-card-title" style="display: flex; align-items: center; gap: 0.75rem; font-size: 1.1rem; font-weight: 700; color: #0b3d91; margin: 0;">
                        <i data-lucide="briefcase" style="width: 20px; height: 20px; color: #1ea952;"></i>
                        Business Information
                    </div>
                </div>
                <div class="dash-card-body">

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" for="business_name" style="display: block; font-size: 0.9rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                            Business Name <span class="required" style="color: #ef4444;">*</span>
                        </label>
                        <input type="text"
                               id="business_name"
                               name="business_name"
                               class="form-control"
                               style="width: 100%; padding: 0.875rem 1rem; border: 2px solid #e9ecef; border-radius: 12px; font-size: 0.95rem; transition: all 0.3s ease;"
                               value="<?= e($vendor['business_name']) ?>"
                               required minlength="3">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" for="category_id" style="display: block; font-size: 0.9rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                            Category <span class="required" style="color: #ef4444;">*</span>
                        </label>
                        <select id="category_id" name="category_id"
                                class="form-control"
                                style="width: 100%; padding: 0.875rem 1rem; border: 2px solid #e9ecef; border-radius: 12px; font-size: 0.95rem; transition: all 0.3s ease;"
                                required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"
                                <?= $vendor['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="description" style="display: block; font-size: 0.9rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                            Business Description <span class="required" style="color: #ef4444;">*</span>
                        </label>
                        <textarea id="description"
                                  name="description"
                                  class="form-control"
                                  style="width: 100%; padding: 0.875rem 1rem; border: 2px solid #e9ecef; border-radius: 12px; font-size: 0.95rem; min-height: 120px; resize: vertical; transition: all 0.3s ease;"
                                  placeholder="Describe your services and what makes you unique..."
                                  required minlength="10"
                                  maxlength="1000"><?= e($vendor['description']) ?></textarea>
                    </div>

                </div>
            </div>
        </div>

                    <?php if ($vendor['vendor_type'] === 'student'): ?>
                    <div class="auth-form-row">
                        <div class="form-group">
                            <label class="form-label" for="level">Current Level</label>
                            <select id="level" name="level" class="form-control">
                                <?php foreach (['100','200','300','400','500','600','PG'] as $lvl): ?>
                                <option value="<?= $lvl ?>"
                                    <?= ($vendor['level'] ?? '') === $lvl ? 'selected' : '' ?>>
                                    <?= $lvl ?> Level
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="years_experience">
                                Years of Experience
                            </label>
                            <input type="number"
                                   id="years_experience"
                                   name="years_experience"
                                   class="form-control"
                                   value="<?= (int)($vendor['years_experience'] ?? 0) ?>"
                                   min="0" max="20">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="operating_location">
                            Operating Location
                        </label>
                        <input type="text"
                               id="operating_location"
                               name="operating_location"
                               class="form-control"
                               value="<?= e($vendor['operating_location'] ?? '') ?>"
                               placeholder="e.g. Block C Hostel, Faculty of Science area">
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <label class="form-label" for="business_address">
                            Business Address
                        </label>
                        <input type="text"
                               id="business_address"
                               name="business_address"
                               class="form-control"
                               value="<?= e($vendor['business_address'] ?? '') ?>"
                               placeholder="Physical address of your business">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="years_operation">
                            Years in Operation
                        </label>
                        <input type="number"
                               id="years_operation"
                               name="years_operation"
                               class="form-control"
                               value="<?= (int)($vendor['years_operation'] ?? 0) ?>"
                               min="0" max="50">
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">

            <!-- Contact Info -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span class="dash-card-title-icon"><i data-lucide="phone"></i></span>
                        Contact Information
                    </div>
                </div>
                <div class="dash-card-body">

                    <div class="form-group">
                        <label class="form-label" for="phone">
                            Primary Phone <span class="required">*</span>
                        </label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               class="form-control"
                               value="<?= e($vendor['phone']) ?>"
                               required
                               data-phone="1">
                        <span class="form-hint">Shown as "Call" button on your profile</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="whatsapp_number">
                            WhatsApp Number
                        </label>
                        <input type="tel"
                               id="whatsapp_number"
                               name="whatsapp_number"
                               class="form-control"
                               value="<?= e($vendor['whatsapp_number'] ?? '') ?>"
                               data-phone="1"
                               placeholder="08012345678">
                        <span class="form-hint">Leave blank to hide WhatsApp button</span>
                    </div>

                </div>
            </div>

            <!-- Logo Upload -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span class="dash-card-title-icon"><i data-lucide="image"></i></span>
                        Business Logo
                    </div>
                </div>
                <div class="dash-card-body">

                    <?php if (!empty($vendor['logo'])): ?>
                    <div style="text-align:center;margin-bottom:1rem;">
                        <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($vendor['logo']) ?>"
                             alt="Current logo"
                             class="current-logo-preview"
                             style="width:100px;height:100px;object-fit:cover;border-radius:var(--radius-xl);border:2px solid var(--divider);">
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:0.5rem;">
                            Current logo
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="file-upload-area">
                        <input type="file"
                               name="logo"
                               accept="image/jpeg,image/png,image/webp"
                               data-max-mb="2">
                        <div class="file-upload-icon">
                            <i data-lucide="image" style="width:32px;height:32px;color:var(--text-muted);"></i>
                        </div>
                        <div class="file-upload-text">
                            <strong>Click to upload</strong> or drag & drop
                        </div>
                        <div class="file-upload-hint">
                            JPG, PNG or WebP · Max 2MB · Square image recommended
                        </div>
                        <div class="file-preview">
                            <img alt="New logo preview">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Save Button -->
            <div style="display:flex;gap:1rem;">
                <a href="<?= SITE_URL ?>/vendor/dashboard"
                   class="btn btn-outline-primary" style="flex:1;text-align:center;">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" style="flex:2;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                    <i data-lucide="save" style="width:16px;height:16px;"></i> Save Changes
                </button>
            </div>

        </div>
    </div>
</form>