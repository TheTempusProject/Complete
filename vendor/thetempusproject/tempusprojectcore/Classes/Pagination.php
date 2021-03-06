<?php
/**
 * Classes/Pagination.php
 *
 * This class is used to generate and manipulate pagination for our database interactions.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class Pagination
{
    //The settings that will not change
    public static $paginationSettings = [];

    //The instance for each generation
    public static $instance = null;

    //The total number of returned results.
    private $totalResults = 0;

    //The total number of pages for the results
    private $totalPages = 1;

    /**
     * [__construct description]
     * @param [type] $start [description]
     * @param [type] $end   [description]
     * @param [type] $total [description]
     */
    private function __construct($start, $end, $total)
    {
        if (empty(self::$paginationSettings['limit'])) {
            $this->loadSettings();
        }

        //check for user settings
        if (empty(self::$paginationSettings['perPage'])) {
            self::$paginationSettings['perPage'] = Config::get('main/pageDefault');
            if ((!empty(self::$paginationSettings['userPerPage'])) && (self::$paginationSettings['userPerPage'] <= self::$paginationSettings['maxPerPage'])) {
                self::$paginationSettings['perPage'] = self::$paginationSettings['userPerPage'];
            }
        }

        // The query minimum and maximum based on current page and page limit
        if (self::$paginationSettings['currentPage'] == 1) {
            self::$paginationSettings['min'] = 0;
            self::$paginationSettings['max'] = self::$paginationSettings['perPage'];
        } else {
            self::$paginationSettings['min'] = ((self::$paginationSettings['currentPage'] - 1) * self::$paginationSettings['perPage']);
            self::$paginationSettings['max'] = self::$paginationSettings['perPage'];
        }

        // The query limit based on our settings here
        self::$paginationSettings['limit'] = [self::$paginationSettings['min'], self::$paginationSettings['max']];
    }

    /**
     * [load_settings description]
     * @return [type] [description]
     */
    private static function loadSettings()
    {
        Debug::log('Loading Pagination Settings.');
        // hard cap built into system for displaying results
        self::$paginationSettings['maxPerPage'] = Config::get('main/pageLimit');

        // hard cap built into system retrieving results
        self::$paginationSettings['maxQuery'] = Config::get('database/dbMaxQuery');

        // Set max query to the lowest of the three settings since this will modify how many results are possible.
        if (self::$paginationSettings['maxQuery'] <= self::$paginationSettings['maxPerPage']) {
            self::$paginationSettings['maxPerPage'] = self::$paginationSettings['maxQuery'];
        }

        // Check for results request to set/modify the perPage setting
        if (Input::exists("results")) {
            if (Check::ID(Input::get("results"))) {
                if (Input::get("results") <= self::$paginationSettings['maxPerPage']) {
                    self::$paginationSettings['perPage'] = Input::get("results");
                }
            }
        }
        if (empty(self::$paginationSettings['perPage'])) {
            self::$paginationSettings['perPage'] = self::$paginationSettings['maxPerPage'];
        }
        

        // Check for pagination in get
        if (Input::exists("page")) {
            if (Check::ID(Input::get("page"))) {
                self::$paginationSettings['currentPage'] = (int) Input::get("page");
            } else {
                self::$paginationSettings['currentPage'] = 1;
            }
        } else {
            self::$paginationSettings['currentPage'] = 1;
        }

        if ((self::$paginationSettings['currentPage'] - 3) > 1) {
            self::$paginationSettings['firstPage'] = (self::$paginationSettings['currentPage'] - 2);
        } else {
            self::$paginationSettings['firstPage'] = 1;
        }
    }

    /**
     * [generate description]
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @param  [type] $total [description]
     * @return [type]        [description]
     */
    public static function generate($start = null, $end = null, $total = null)
    {
        // account for empty values here instead of inside the script.
        Debug::log('Creating new Pagination.');
        if (empty($start)) {
            $start = 0;
        }
        if (empty($end)) {
            $end = Config::get('main/pageDefault');
        }
        if (empty($total)) {
            $total = 0;
        }
        Debug::log('Creating new Pagination Instance.');
        self::$instance = new self($start, $end, $total);
        return self::$instance;
    }

    /**
     * [updatePrefs description]
     * @param  [type] $pageLimit [description]
     * @return [type]             [description]
     */
    public static function updatePrefs($pageLimit)
    {
        if (Check::id($pageLimit)) {
            Debug::log('Pagination: Updating user pref');
            self::$paginationSettings['userPerPage'] = $pageLimit;
        } else {
            Debug::info('Pagination: User pref update failed.');
        }
    }

    /**
     * [getMin description]
     * @return [type] [description]
     */
    public static function getMin()
    {
        if (isset(self::$paginationSettings['min'])) {
            return self::$paginationSettings['min'];
        } else {
            Debug::info('Pagination: Min not found');
        }
    }

    /**
     * [perPage description]
     * @return [type] [description]
     */
    public static function perPage()
    {
        if (!empty(self::$paginationSettings['perPage'])) {
            return self::$paginationSettings['perPage'];
        }
    }

    /**
     * [getMax description]
     * @return [type] [description]
     */
    public static function getMax()
    {
        if (!empty(self::$paginationSettings['max'])) {
            return self::$paginationSettings['max'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }
    
    /**
     * [firstPage description]
     * @return [type] [description]
     */
    public static function firstPage()
    {
        if (!empty(self::$paginationSettings['firstPage'])) {
            return self::$paginationSettings['firstPage'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }

    /**
     * [lastPage description]
     * @return [type] [description]
     */
    public static function lastPage()
    {
        if (!empty(self::$paginationSettings['lastPage'])) {
            return self::$paginationSettings['lastPage'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }

    /**
     * [totalPages description]
     * @return [type] [description]
     */
    public static function totalPages()
    {
        if (!empty(self::$paginationSettings['totalPages'])) {
            return self::$paginationSettings['totalPages'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }

    /**
     * [update_results description]
     * @param  [type] $results [description]
     * @return [type]          [description]
     */
    public static function updateResults($results)
    {
        if (empty(self::$paginationSettings)) {
            self::generate();
        }
        if (Check::id($results)) {
            Debug::log('Pagination: Updating results count');
            self::$paginationSettings['results'] = $results;
            self::$paginationSettings['totalPages'] = ceil((self::$paginationSettings['results'] / self::$paginationSettings['perPage']));
            if ((self::$paginationSettings['currentPage'] + 3) < self::$paginationSettings['totalPages']) {
                self::$paginationSettings['lastPage'] = self::$paginationSettings['currentPage'] + 3;
            } else {
                self::$paginationSettings['lastPage'] = self::$paginationSettings['totalPages'];
            }
        } else {
            Debug::info('Pagination: results update failed.');
        }
    }

    /**
     * [currentPage description]
     * @return [type] [description]
     */
    public static function currentPage()
    {
        if (!empty(self::$paginationSettings['currentPage'])) {
            return self::$paginationSettings['currentPage'];
        } else {
            Debug::info('Pagination: currentPage not found');
        }
    }
}
