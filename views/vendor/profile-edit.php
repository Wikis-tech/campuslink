<?php defined('CAMPUSLINK') or die(); ?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Edit Profile</h1>
        <p class="dashboard-page-subtitle">
            Update your business info — changes are live immediately.
        </p>
    </div>
    <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
       class="btn btn-outline-primary" target="_blank">
        👁️ Preview Public Profile
    </a>
</div>

<form action="<?= SITE_URL ?>/vendor/profile"
      method="POST"
      enctype="multipart/form-data"
      class="vendor-profile-form"
      novalidate>
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

    <div class="dashboard-grid-2">

        <!-- Left Column -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">

            <!-- Business Info -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <span class="dash-card-title-icon">🏪</span>
                        Business Information
                    </div>
                </div>
                <div class="dash-card-body">

                    <div class="form-group">
                        <label class="form-label" for="business_name">
                            Business Name <span class="required">*</span>
                        </label>
                        <input type="text"
                               id="business_name"
                               name="business_name"
                               class="form-control"
                               value="<?= e($vendor['business_name']) ?>"
                               required data-min="3">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category_id">
                            Category <span class="required">*</span>
                        </label>
                        <select id="category_id" name="category_id"
                                class="form-control" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"
                                <?= $vendor['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['icon']) ?> <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">
                            Business Description <span class="required">*</span>
                        </label>
                        <textarea id="description"
                                  name="description"
                                  class="form-control"
                                  rows="5"
                                  placeholder="Describe your services, what makes you unique..."
                                  required
                                  data-min="50"
                                  data-max-chars="1000"><?= e($vendor['description']) ?></textarea>
                        <div class="review-char-counter"
                             data-counter-for="description"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price_range">Price Range</label>
                        <input type="text"
                               id="price_range"
                               name="price_range"
                               class="form-control"
                               value="<?= e($vendor['price_range'] ?? '') ?>"
                               placeholder="e.g. ₦500 – ₦5,000">
                        <span class="form-hint">Give students an idea of your pricing</span>
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
                        <span class="dash-card-title-icon">📞</span>
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
                        <span class="dash-card-title-icon">🖼️</span>
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
                        <div class="file-upload-icon">🖼️</div>
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
                <button type="submit" class="btn btn-primary" style="flex:2;">
                    💾 Save Changes
                </button>
            </div>

        </div>
    </div>
</form>