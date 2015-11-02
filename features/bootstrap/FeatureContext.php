<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Zend\Mvc\Application;
use Behat\Behat\Exception\BehaviorException;
use Behat\Behat\Exception\PendingException;
use Rhumsaa\Uuid\Uuid;

require __DIR__ . '/../../init_autoloader.php';

/**
 * Feature context.
 */
class FeatureContext extends BehatContext //MinkContext if you want to test web page
{
    /** @var array */
    private $placeholders = [];

    /** @var array The query string to add (in URI Template format) */
    protected $queryString = [];

    private $zf2MvcApplication;
    private $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        ini_set('memory_limit', '-1');

        $this->zf2MvcApplication = \Zend\Mvc\Application::init(require __DIR__ . '/../../config/application.config.php');

        $this->parameters = $parameters;
        $this->useContext('RestContext', new RestContext('http://localhost'));
    }

    public function getServiceManager()
    {
        return $this->zf2MvcApplication->getServiceManager();
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $purger = new ORMPurger($this->getEntityManager());
        $this->getEntityManager()->getConnection()->exec('SET foreign_key_checks = 0;');
        $purger->purge();
        $this->getEntityManager()->getConnection()->exec('SET foreign_key_checks = 1;');
    }

    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceManager()->get('entity_manager');
    }

    /**
     * @Then /^the database should contain a user with the following data$/
     */
    public function theDatabaseShouldContainAUserWithTheFollowingData(TableNode $data)
    {
        $data = $data->getRows();

        /** @var \Application\Mapper\User $mapperUser */
        $mapperUser = $this->getServiceManager()->get('mapper.user');
        $allusers   = $mapperUser->findAll();

        $hit = false;

        foreach ($allusers as $user) {
            $found = true;

            foreach ($data as $row) {
                $getter = 'get' . ucfirst($row[0]);

                if ($user->$getter() != $row[1]) {
                    $found = false;
                    break;
                }

                break 2;
            }

            if ($found) {
                break;
            }
        }

        if (!$found) {
            throw new BehaviorException("The user hasn't been created.");
        }
    }
}
