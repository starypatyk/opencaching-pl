<?php

namespace lib\Controllers;

/**
 * Description of medals
 *
 * @author Łza
 */
class MedalsController
{
    const MEDAL_TYPE_REGION = 1;
    const MEDAL_TYPE_CACHES = 2;
    const MEDAL_TYPE_GEOPATHCOMPLETED = 3;
    const MEDAL_TYPE_MAXALTITUDE = 4;
    const MEDAL_TYPE_HIGHLAND = 5;
    const MEDAL_TYPE_OLDGEOCACHER = 6;

    public $config;

    public function __construct()
    {
        $this->config = \lib\Objects\OcConfig\OcConfig::instance();
    }

    public function checkMedalConditions(\lib\Objects\User\User $user)
    {
        // $cache = new \lib\Objects\GeoCache(array('cacheId' => $params['cacheId']));
        $allPossibleMedals = $this->allMedals();
        foreach ($allPossibleMedals as $medal) {
            $medal->checkConditionsForUser($user);
        }
    }

    /**
     * get all today's active users
     */
    public function checkAllUsersMedals()
    {
        $query = 'SELECT user_id, username, founds_count, notfounds_count, hidden_count, latitude, longitude, country, email FROM `user` WHERE (`last_login` BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()) ';
        /* @var $db \dataBase */
        $db = \lib\Database\DataBaseSingleton::Instance();
        $db->simpleQuery($query);
        d($db->rowCount());
        $timeStart = microtime();
        $usersToCheck = $db->dbResultFetchAll();
        foreach ($usersToCheck as $userDbRow) {
            $user = new \lib\Objects\User\User(array('userDbRow' => $userDbRow));
            $user->loadMedalsFromDb();
            $this->checkMedalConditions($user);
        }
        $timeEnd = microtime() - $timeStart;
        d($timeEnd);
    }

    public static function getMedalConfigByMedalId($medalId)
    {
        $medalConfig = self::getConfig();
        return $medalConfig[$medalId];
    }

    public static function getConfig()
    {
        return include __DIR__ . '/config/medals.config.php';
    }

    private function allMedals()
    {
        foreach (self::getConfig() as $medalId => $medalDetails) {
            $medalDetails['medalId'] = $medalId;
            $medals[] = $this->buildMedalObject($medalDetails);
        }
        return $medals;
    }

    /**
     * medal factory.
     * @param array $medalDetails
     * @return \lib\Objects\Medals\MedalGeopathCompleted|\lib\Objects\Medals\MedalMaxAltitude|\lib\Objects\Medals\MedalCachefound|\lib\Objects\Medals\MedalHighlandCaches|\lib\Objects\Medals\MedalOldGeocacher|\lib\Objects\Medals\MedalGeographical
     */
    private function buildMedalObject($medalDetails)
    {
        switch ($medalDetails['type']) {
            case self::MEDAL_TYPE_REGION:
                return new \lib\Objects\Medals\MedalGeographical($medalDetails);
            case self::MEDAL_TYPE_CACHES:
                return new \lib\Objects\Medals\MedalCachefound($medalDetails);
            case self::MEDAL_TYPE_GEOPATHCOMPLETED:
                return new \lib\Objects\Medals\MedalGeopathCompleted($medalDetails);
            case self::MEDAL_TYPE_MAXALTITUDE:
                return new \lib\Objects\Medals\MedalMaxAltitude($medalDetails);
            case self::MEDAL_TYPE_HIGHLAND:
                return new \lib\Objects\Medals\MedalHighlandCaches($medalDetails);
            case self::MEDAL_TYPE_OLDGEOCACHER:
                return new \lib\Objects\Medals\MedalOldGeocacher($medalDetails);
            default:
                d('error - undefined medal, please add your medal to factory type here.');
                break;
        }
    }

    public function getMedal($medalDetails)
    {
        $medalConfig = self::getMedalConfigByMedalId($medalDetails['medalId']);
        $medalDetails['type'] = $medalConfig['type'];
        return $this->buildMedalObject($medalDetails);
    }

}
