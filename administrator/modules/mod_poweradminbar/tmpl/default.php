<?php
/**
 * @version    $Id$
 * @package    JSN_PowerAdmin_2
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// If check request is sent from a mobile device?
if (!class_exists('Mobile_Detect'))
{
	require_once dirname(__FILE__) . '/../vendors/Mobile_Detect.php';
}

$detector = new Mobile_Detect();

// If mobile device detected, render the default admin bar of Joomla as well.
if ($detector->isMobile() || $detector->isTablet())
{
    // Define module parameters.
	$params = new JRegistry('{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}');

	// Load module language.
	JFactory::getLanguage()->load('mod_menu', JPATH_BASE, null, false, true);

    // Render module.
    echo '<div id="joomla-admin-menu">';

    include JPATH_ADMINISTRATOR . '/modules/mod_menu/mod_menu.php';

    echo '</div>';
}

// Generate link to get config for admin bar.
$link = JSession::getFormToken();
$link = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getAdminBarConfig&%1$s=1', $link), false);

// @formatter:off
?>
<div id="pa-adminbar" class="jsn-bootstrap4 navbar-fixed-top" data-render="ComponentAdminBar" data-config="<?php echo $link; ?>"></div>
<script type="text/javascript">
    setTimeout(function () {
        // Disable the admin bar of JSN PowerAdmin 2 if on mobile device.
        if ((document.documentElement || document.body).clientWidth <= 768) {
            // Remove the admin bar of JSN PowerAdmin 2.
            var adminBar = document.getElementById('pa-adminbar');

            if (adminBar) {
                adminBar.parentNode.removeChild(adminBar);
            }

            // Move up the default admin bar of Joomla one level.
            var container = document.getElementById('joomla-admin-menu');

            if (container) {
                for (var i = 0; i < container.children.length; i++) {
                    container.parentNode.insertBefore(container.children[i], container);
                }
            }

            container.parentNode.removeChild(container);
        }

        // Otherwise, initialize the admin bar of JSN PowerAdmin 2.
        else {
            // Remove the default admin bar of Joomla.
            var adminBar = document.getElementById('joomla-admin-menu');

            if (adminBar) {
                adminBar.parentNode.removeChild(adminBar);
            }

            // Get admin bar.
            var admin_bar = document.getElementById('pa-adminbar');

            // Get 3rd-party menus.
            var root = document.querySelector('.navbar-fixed-top .nav-collapse');

            if (root && root.children && root.children.length) {
                var tmp = document.createElement('div');

                tmp.style.display = 'none';

                for (var i = 0; i < root.children.length; i++) {
                    if (root.children[i].nodeName != 'UL') {
                        continue;
                    }

                    if (root.children[i].className == 'nav' || root.children[i].id == 'nav-empty') {
                        continue;
                    }

                    if (root.children[i].classList && root.children[i].classList.contains('nav-user')) {
                        continue;
                    }

                    // Add some necessary classes.
                    if (root.children[i].classList) {
                        root.children[i].classList.add('navbar-nav');
                        root.children[i].classList.add('ml-0');
                    } else {
                        root.children[i].className = 'navbar-nav ml-0';
                    }

                    tmp.appendChild(root.children[i]);
                }

                document.body.appendChild(tmp);
            }

            // Replace the JSN PowerAdmin gen. 1 admin bar if exists.
            if (document.getElementById('jsn-adminbar')) {
                document.body.replaceChild(admin_bar, document.getElementById('jsn-adminbar'));
            }

            // Otherwise, replace the default Joomla admin bar.
            else {
                document.body.replaceChild(admin_bar, document.body.querySelector('.navbar-fixed-top'));
            }

            // Wait till the admin bar rendered completely, then append 3rd-party menus into it.
            (function appendMenus() {
                var left_menu = admin_bar.querySelector('.navbar-nav');

                if (!left_menu) {
                    setTimeout(appendMenus, 10);
                } else if (tmp && tmp.children && tmp.children.length) {
                    for (var i = tmp.children.length - 1; i >= 0; i--) {
                        left_menu.parentNode.insertBefore(tmp.children[i], left_menu.nextSibling);
                    }

                    document.body.removeChild(tmp);
                }
            })();
        }
    }, 1);
</script>
