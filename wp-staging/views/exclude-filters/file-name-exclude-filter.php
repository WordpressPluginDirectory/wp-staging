<?php

use WPStaging\Framework\Facades\Sanitize;
use WPStaging\Framework\Filesystem\Filters\ExcludeFilter;

/**
 * @var string $rule
 * @var string $name
 *
 * @see \WPStaging\Framework\Filesystem\Filters\ExcludeFilter::renderExclude For details on $rule and $name.
 */
?>
<tr>
    <td class="wpstg-exclude-filter-name-column"><?php esc_html_e('File Name', 'wp-staging') ?></td>
    <td class="wpstg-exclude-filter-exclusion-column">
        <select class="wpstg-exclude-rule-input wpstg-path-exclude-select" name="wpstgFileNameExcludeRulePos[]">
            <option value="<?php echo ExcludeFilter::NAME_BEGINS_WITH ?>" <?php echo !empty($rule) && $rule === ExcludeFilter::NAME_BEGINS_WITH ? 'selected' : '' ?>><?php esc_html_e('BEGINS WITH', 'wp-staging') ?></option>
            <option value="<?php echo ExcludeFilter::NAME_ENDS_WITH ?>" <?php echo !empty($rule) && $rule === ExcludeFilter::NAME_ENDS_WITH ? 'selected' : '' ?>><?php esc_html_e('ENDS WITH', 'wp-staging') ?></option>
            <option value="<?php echo ExcludeFilter::NAME_EXACT_MATCHES ?>" <?php echo !empty($rule) && $rule === ExcludeFilter::NAME_EXACT_MATCHES ? 'selected' : '' ?>><?php esc_html_e('EXACT MATCHES', 'wp-staging') ?></option>
            <option value="<?php echo ExcludeFilter::NAME_CONTAINS ?>" <?php echo !empty($rule) && $rule === ExcludeFilter::NAME_CONTAINS ? 'selected' : '' ?>><?php esc_html_e('CONTAINS', 'wp-staging') ?></option>
        </select>
        <input type="text" class="wpstg-exclude-rule-input" name="wpstgFileNameExcludeRulePath[]" value="<?php echo !empty($name) ? Sanitize::sanitizeString($name) : '' ?>" />
    </td>
    <td class="wpstg-exclude-filter-action-column wpstg-exclude-rule-action">
        <div class="wpstg--tooltip wpstg--exclude-rules--tooltip">
            <img class="wpstg--dashicons" src="<?php echo esc_url(trailingslashit(WPSTG_PLUGIN_URL)) . 'assets/'; ?>svg/info-outline.svg" alt="info" />
            <div class='wpstg--tooltiptext has-top-arrow'>
                <?php echo sprintf(esc_html__('Exclude files by name. For example to exclude all files which have %s at the end of the name, select %s and type %s in the input box.', 'wp-staging'), '<code class="wpstg-code">-class</code>', '<code class="wpstg-code">' . esc_html__('ENDS WITH', 'wp-staging') . '</code>', '<code class="wpstg-code">-class</code>') ?>
            </div>
        </div>
        <div>
            <img class="wpstg-remove-exclude-rule" src="<?php echo esc_url(trailingslashit(WPSTG_PLUGIN_URL)) . 'assets/'; ?>img/trash.svg" alt="delete">
        </div>
    </td>
</tr>
