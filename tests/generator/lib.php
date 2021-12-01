<?php

/**
 * mod_scratchpad data generator.
 *
 * @package    mod_scratchpad
 * @category   test
 * @copyright  2014 David Monllao <david.monllao@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mod_scratchpad data generator class.
 *
 * @package    mod_scratchpad
 * @category   test
 * @copyright  2014 David Monllao <david.monllao@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_scratchpad_generator extends testing_module_generator {

    /**
     * @var int keep track of how many scratchpads have been created.
     */
    protected $scratchpadcount = 0;

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->scratchpadcount = 0;
        parent::reset();
    }


    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        if (!isset($record->name)) {
            $record->name = 'Test scratchpad name ' . $this->scratchpadcount;
        }
        if (!isset($record->intro)) {
            $record->intro = 'Test scratchpad name ' . $this->scratchpadcount;
        }
        if (!isset($record->days)) {
            $record->days = 0;
        }
        if (!isset($record->grade)) {
            $record->grade = 100;
        }

        $this->scratchpadcount++;

        return parent::create_instance($record, (array)$options);
    }

}
