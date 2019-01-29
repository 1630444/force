<?php
/**
 * @version    $Id$
 * @package    JSN_PageBuilder3
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Configuration model.
 *
 * @package  JSN_PageBuilder3
 * @since    1.0.0
 */
class JSNPageBuilder3ModelConfiguration extends JSNConfigModel
{
    /**
     * Method to do additional instant update according config change
     *
     * @param   string $name Name of changed config parameter
     * @param   mixed $value Recent config parameter value
     *
     * @return  void
     */
    protected function instantUpdate($name, $value)
    {
        if ($name == 'disable_all_messages') {
            // Get name of messages table
            $table = '#__jsn_' . substr(JFactory::getApplication()->input->getCmd('option'), 4) . '_messages';

            // Enable/disable all messages
            $db = JFactory::getDbo();
            $db->setQuery("UPDATE `{$table}` SET published = " . (1 - $value) . " WHERE 1");
            $db->execute();
        } else {
            return parent::instantUpdate($name, $value);
        }

        return true;
    }

    /**
     * Save the submitted configuration data to database.
     *
     * @param   array $config Parsed XML declaration.
     * @param   array $data The data to save.
     * @param   bool $validate Whether to validate submitted configuration data?
     *
     * @return  void
     */
    public function save($config, $data, $validate = false)
    {
        // Validate submitted form data
        try {
            $validate AND $this->validate($config, $data);
        } catch (Exception $e) {
            throw $e;
        }

        //Save token key to plugin parameter
        if (isset($data['token'])) {
            JSNUtilsExtension::updateExtensionParams(array('token' => $data['token']), 'component', 'com_pagebuilder3');
            unset($data['token']);
        }
        // Get name of config data table
        $table = '#__jsn_' . preg_replace('/^com_/i', '', JFactory::getApplication()->input->getCmd('option')) . '_config';

        // Get database object
        $db = JFactory::getDbo();

        // Update config data table
        foreach ($data AS $k => $v) {
            if (!isset($this->params[$k]) OR json_encode($v) != json_encode($this->params[$k])) {
                if ($v !== 'JSN_CONFIG_SKIP_SAVING') {
                    // Encode value if it is either an array or object
                    if (is_array($v) OR is_object($v)) {
                        $v = json_encode($v);
                    }

                    // Set query then execute
                    $q = $db->getQuery(true);

                    if (isset($this->params[$k])) {
                        $q->update($table);
                        $q->set('value = ' . $q->quote($v));
                        $q->where("name = '{$k}'");
                    } else {
                        $q->insert($table);
                        $q->columns('name, value');
                        $q->values("'{$k}', " . $q->quote($v));
                    }

                    $db->setQuery($q);

                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        throw $e;
                    }
                }

                // Do additional instant update according to config change
                try {
                    $this->instantUpdate($k, $data[$k]);
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }
    }
}
