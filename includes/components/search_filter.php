<?php
/**
 * Reusable Search and Filter Component
 * 
 * Usage:
 * include 'includes/components/search_filter.php';
 * render_search_filter($config);
 * 
 * $config example:
 * [
 *     'page' => 'members.php',
 *     'search_placeholder' => 'Search member...',
 *     'search_param' => 'search',
 *     'filters' => [
 *         'status' => [
 *             'label' => 'Status',
 *             'param' => 'status_filter',
 *             'options' => [
 *                 'All' => 'All Status',
 *                 'Active' => 'Active',
 *                 'Inactive' => 'Inactive',
 *                 'Pending' => 'Pending'
 *             ]
 *         ]
 *     ]
 * ]
 */

function render_search_filter($config) {
    // Extract configuration
    $page = $config['page'] ?? '';
    $search_placeholder = $config['search_placeholder'] ?? 'Search...';
    $search_param = $config['search_param'] ?? 'search';
    $filters = $config['filters'] ?? [];
    $add_button = $config['add_button'] ?? null;
    
    // Get current values from GET parameters
    $search_value = $_GET[$search_param] ?? '';
    
    ?>
    <div class="page-actions">
        <div class="search-filter-group">
            <!-- Search Box -->
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" 
                       placeholder="<?= htmlspecialchars($search_placeholder) ?>" 
                       id="globalSearchInput" 
                       value="<?= htmlspecialchars($search_value) ?>"
                       data-page="<?= htmlspecialchars($page) ?>"
                       data-param="<?= htmlspecialchars($search_param) ?>">
            </div>

            <?php if (!empty($filters)): ?>
            <!-- Filter Dropdowns -->
            <?php foreach ($filters as $filter_key => $filter_config): ?>
                <?php
                $filter_label = $filter_config['label'] ?? 'Filter';
                $filter_param = $filter_config['param'] ?? $filter_key;
                $filter_options = $filter_config['options'] ?? [];
                $filter_type = $filter_config['type'] ?? 'radio'; // radio, checkbox, select
                $current_value = $_GET[$filter_param] ?? ($filter_config['default'] ?? 'All');
                $icon = $filter_config['icon'] ?? 'bi-funnel';
                
                // Count active filters (for display)
                $active_count = 0;
                if ($filter_type === 'checkbox' && is_array($current_value)) {
                    $active_count = count($current_value);
                } elseif ($current_value !== 'All' && $current_value !== '') {
                    $active_count = 1;
                }
                ?>
                
                <div class="filter-dropdown">
                    <button class="filter-btn" onclick="toggleFilter('<?= $filter_key ?>')">
                        <i class="bi <?= $icon ?>"></i> 
                        <?= htmlspecialchars($filter_label) ?>
                        <?php if ($active_count > 0): ?>
                            <span class="filter-badge"><?= $active_count ?></span>
                        <?php endif; ?>
                        <i class="bi bi-chevron-down"></i>
                    </button>

                    <div class="filter-dropdown-content" id="filterDropdown_<?= $filter_key ?>">
                        <form method="GET" action="<?= htmlspecialchars($page) ?>" class="filter-form">
                            <!-- Preserve other GET parameters -->
                            <?php foreach ($_GET as $key => $value): ?>
                                <?php if ($key !== $filter_param && $key !== 'action'): ?>
                                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <?php if (isset($_GET['action'])): ?>
                                <input type="hidden" name="action" value="<?= htmlspecialchars($_GET['action']) ?>">
                            <?php endif; ?>

                            <?php if ($filter_type === 'radio'): ?>
                                <!-- Radio buttons -->
                                <?php foreach ($filter_options as $value => $label): ?>
                                    <div class="filter-option">
                                        <input type="radio" 
                                               name="<?= htmlspecialchars($filter_param) ?>" 
                                               value="<?= htmlspecialchars($value) ?>" 
                                               id="filter_<?= $filter_key ?>_<?= htmlspecialchars($value) ?>"
                                               <?= $current_value == $value ? 'checked' : '' ?>
                                               onchange="this.form.submit()">
                                        <label for="filter_<?= $filter_key ?>_<?= htmlspecialchars($value) ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                
                            <?php elseif ($filter_type === 'checkbox'): ?>
                                <!-- Checkboxes -->
                                <?php 
                                $current_values = is_array($current_value) ? $current_value : [$current_value];
                                ?>
                                <?php foreach ($filter_options as $value => $label): ?>
                                    <div class="filter-option">
                                        <input type="checkbox" 
                                               name="<?= htmlspecialchars($filter_param) ?>[]" 
                                               value="<?= htmlspecialchars($value) ?>" 
                                               id="filter_<?= $filter_key ?>_<?= htmlspecialchars($value) ?>"
                                               <?= in_array($value, $current_values) ? 'checked' : '' ?>>
                                        <label for="filter_<?= $filter_key ?>_<?= htmlspecialchars($value) ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="filter-actions">
                                    <button type="submit" class="btn-apply">Apply</button>
                                    <button type="button" class="btn-clear" onclick="clearFilter('<?= $filter_key ?>', '<?= $filter_param ?>')">Clear</button>
                                </div>
                                
                            <?php elseif ($filter_type === 'select'): ?>
                                <!-- Dropdown select -->
                                <select name="<?= htmlspecialchars($filter_param) ?>" onchange="this.form.submit()">
                                    <?php foreach ($filter_options as $value => $label): ?>
                                        <option value="<?= htmlspecialchars($value) ?>" 
                                                <?= $current_value == $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($add_button): ?>
        <!-- Add Button -->
        <button class="btn-add" onclick="<?= htmlspecialchars($add_button['onclick']) ?>">
            <i class="bi <?= htmlspecialchars($add_button['icon'] ?? 'bi-plus-lg') ?>"></i>
            <?= htmlspecialchars($add_button['label']) ?>
        </button>
        <?php endif; ?>
    </div>
    <?php
}
?>