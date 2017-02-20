<?php

namespace Capco\AppBundle\Behat;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Tester\Result\TestResult;
use Capco\AppBundle\Behat\Traits\CommentStepsTrait;
use Capco\AppBundle\Behat\Traits\IdeaStepsTrait;
use Capco\AppBundle\Behat\Traits\OpinionStepsTrait;
use Capco\AppBundle\Behat\Traits\ProjectStepsTrait;
use Capco\AppBundle\Behat\Traits\ProposalStepsTrait;
use Capco\AppBundle\Behat\Traits\ReportingStepsTrait;
use Capco\AppBundle\Behat\Traits\SharingStepsTrait;
use Capco\AppBundle\Behat\Traits\SynthesisStepsTrait;
use Capco\AppBundle\Behat\Traits\QuestionnaireStepsTrait;
use Capco\AppBundle\Behat\Traits\ThemeStepsTrait;
use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;
use WebDriver\Exception\ElementNotVisible;
use Docker\Docker;
use Docker\Http\Client;
use Docker\Container;
use Docker\Exception\UnexpectedStatusCodeException;
use Symfony\Component\Process\Process;
use Behat\Gherkin\Node\TableNode;

class ApplicationContext extends UserContext
{
    protected $headers;
    protected $dbContainer;
    protected $currentPage = 'home page';

    use CommentStepsTrait;
    use IdeaStepsTrait;
    use OpinionStepsTrait;
    use ProjectStepsTrait;
    use ProposalStepsTrait;
    use QuestionnaireStepsTrait;
    use ReportingStepsTrait;
    use SharingStepsTrait;
    use SynthesisStepsTrait;
    use ThemeStepsTrait;

    /**
     * @BeforeScenario
     */
    public function reset($scope)
    {
        // Let's stick with the old way for now
        $jobs = [
            new Process('curl -sS -XDELETE \'http://elasticsearch:9200/_all\''),
            new Process('curl -sS -XBAN http://capco.test/'),
            new Process('redis-cli -h redis FLUSHALL'),
        ];

        $scenario = $scope->getScenario();
        if ($scenario->hasTag('elasticsearch')) {
            $jobs[] = new Process('SYMFONY_ROUTER__REQUEST_CONTEXT__HOST=capco.test php bin/console fos:elastica:populate -e test -n');
        }
        foreach ($jobs as $job) {
            $job->mustRun();
        }
    }

    /**
     * @AfterScenario
     */
    public function resetDatabase($scope)
    {
        $scenario = $scope->getScenario();
        if ($scenario->hasTag('database')) {
            (new Process('mysql -h database -u root symfony < var/db.backup'))->mustRun();
        }
    }

    public function resetUsingDocker()
    {
        // This is the real docker way, but not that easy
        // We need to use something like https://github.com/jwilder/nginx-proxy
        // To reload containers, because we can't do reload on runtime with links
        // So we have to make sure it's supported on Circle-CI...
        $docker = new Docker(new Client('unix:///run/docker.sock'));
        $manager = $docker->getContainerManager();

        if (null !== $this->dbContainer && $this->dbContainer->exists()) {
            try {
                $manager->stop($this->dbContainer)->remove($this->dbContainer, true, true);
            } catch (UnexpectedStatusCodeException $e) {
                if (!strpos($e->getMessage(), 'Driver btrfs failed to remove root filesystem')) {
                    throw $e;
                }
                // We don't care about this error that happen only because of Circle-CI bad support of Docker
            }
        }

        $this->dbContainer = new Container(['Image' => 'capco/fixtures']);
        $manager->create($this->dbContainer)->start($this->dbContainer);
    }

     /**
      * @BeforeScenario @javascript
      */
     public function maximizeWindow()
     {
         $this->getSession()->getDriver()->maximizeWindow();
     }

     /**
      * @Given I visited :pageName
      */
     public function iVisitedPage(string $pageName)
     {
         if ($this->getSession()) {
             $this->navigationContext->iVisitedPage('HomePage');
             $this->getSession()->setCookie('displayCookieConsent', 'y');
         }
         $this->navigationContext->iVisitedPage($pageName);
     }

     /**
      * @Given I should see the shield
      */
      public function iShouldSeeTheShield()
      {
        $this->assertSession()->elementExists('css', '#shield-mode');
      }

      /**
       * @Given I should not see the shield
       */
       public function iShouldNotSeeTheShield()
       {
         $this->assertSession()->elementNotExists('css', '#shield-mode');
       }

     /**
      * @Given I visited :pageName with:
      */
     public function iVisitedPageWith($pageName, TableNode $parameters)
     {
         if ($this->getSession()) {
             $this->navigationContext->iVisitedPage('HomePage');
             $this->getSession()->setCookie('displayCookieConsent', 'y');
         }
         $this->navigationContext->iVisitedPageWith($pageName, $parameters);
     }

    /**
     * @AfterScenario @javascript
     */
    public function clearLocalStorage()
    {
        $this->getSession()->getDriver()->evaluateScript('window.sessionStorage.clear();');
        $this->getSession()->getDriver()->evaluateScript('window.localStorage.clear();');
    }

    /**
     * @AfterSuite
     */
    public static function notifyEnd(AfterSuiteScope $suiteScope)
    {
        $suiteName = $suiteScope->getSuite()->getName();
        $resultCode = $suiteScope->getTestResult()->getResultCode();
        if ($notifier = NotifierFactory::create()) {
            $notification = new Notification();
            if ($resultCode === TestResult::PASSED) {
                $notification
                    ->setTitle('Behat suite ended successfully')
                    ->setBody('Suite "'.$suiteName.'" has ended without errors (for once). Congrats !')
                ;
            } elseif ($resultCode === TestResult::SKIPPED) {
                $notification
                    ->setTitle('Behat suite ended with skipped steps')
                    ->setBody('Suite "'.$suiteName.'" has ended successfully but some steps have been skipped.')
                ;
            } else {
                $notification
                    ->setTitle('Behat suite ended with errors')
                    ->setBody('Suite "'.$suiteName.'" has ended with errors. Go check it out you moron !')
                ;
            }
            $notifier->send($notification);
        }
    }

     /**
      * @Then I should be redirected to :url
      */
     public function assertRedirect($url)
     {
         $this->getSession()->wait(1000);

         $this->assertPageAddress($url);
     }

    /**
     * @Given all features are enabled
     */
    public function allFeaturesAreEnabled()
    {
        $this->getService('capco.toggle.manager')->activateAll();
    }

    /**
     * @Given feature :featureA is enabled
     * @Given features :featureA, :featureB are enabled
     * @Given features :featureA, :featureB, :featureC are enabled
     */
    public function featureIsEnabled($featureA, $featureB = null, $featureC = null)
    {
        $this->getService('capco.toggle.manager')->activate($featureA);
        if ($featureB) {
            $this->getService('capco.toggle.manager')->activate($featureB);
            if ($featureC) {
                $this->getService('capco.toggle.manager')->activate($featureC);
            }
        }
    }

    /**
     * @When I print html
     */
    public function printHtml()
    {
        echo $this->getSession()->getPage()->getHtml();
    }

    /**
     * @Then I should see :element on :page
     */
    public function iShouldSeeElementOnPage($element, $page)
    {
        expect($this->navigationContext->getPage($page)->containsElement($element))->toBe(true);
    }

    /**
     * @Then I should not see :element on :page
     */
    public function iShouldNotSeeElementOnPage($element, $page)
    {
        expect($this->navigationContext->getPage($page)->containsElement($element))->toBe(false);
    }

    /**
     * @Then I should see :element on :page disabled
     */
    public function iShouldSeeElementOnPageDisabled(string $element, string $pageSlug)
    {
        $page = $this->navigationContext->getPage($pageSlug);
        $this->getSession()->wait(2000, "$('".$page->getSelector($element)."').length > 0");
        expect($page->getElement($element)->hasAttribute('disabled'))->toBe(true);
    }

    /**
     * @Then I should see :nb :element on current page
     */
    public function iShouldSeeNbElementOnPage(int $nb, string $element)
    {
        expect(count($this->getSession()->getPage()->find('css', $element)))->toBe($nb);
    }

    /**
     * @Then :first should be before :second for selector :cssQuery
     */
    public function element1ShouldBeBeforeElement2ForSelector($first, $second, $cssQuery)
    {
        $items = array_map(
            function ($element) {
                return $element->getText();
            },
            $this->getSession()->getPage()->findAll('css', $cssQuery)
        );
        if (!in_array($first, $items)) {
            throw new ElementNotFoundException($this->getSession(), 'Element "'.$first.'"');
        }
        if (!in_array($second, $items)) {
            throw new ElementNotFoundException($this->getSession(), 'Element "'.$second.'"');
        }
        \PHPUnit_Framework_TestCase::assertTrue(array_search($first, $items) < array_search($second, $items));
    }

    /**
     * @When I click the :element element
     */
    public function iClickElement($selector)
    {
        $element = $this->getSession()->getPage()->find('css', $selector);
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession(), 'element', 'css', $selector);
        }
        $element->click();
    }

    /**
     * @When I hover over the :selector element
     */
    public function iHoverOverTheElement($selector)
    {
        $element = $this->getSession()->getPage()->find('css', $selector);

        if (null === $element) {
            throw new ElementNotFoundException($this->getSession(), 'element', 'css', $selector);
        }

        $element->mouseOver();
    }

    /**
     * Fills in form field with specified id|name|label|value.
     * Overrided to fill wysiwyg fields as well.
     */
    public function fillField($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        try {
            $this->getSession()->getPage()->fillField($field, $value);
        } catch (ElementNotFoundException $e) {
            // Try to get corresponding wysiwyg field
            // Works only with quill editor for now
            $wrapper = $this->getSession()->getPage()->find('named', array('id_or_name', $field));
            if (!$wrapper || !$wrapper->hasClass('editor') || !$wrapper->has('css', '.ql-editor')) {
                throw $e;
            }
            $field = $wrapper->find('css', '.ql-editor');
            $field->setValue($value);
        } catch (ElementNotVisible $e) {
            // Ckeditor case
            $wrapper = $this->getSession()->getPage()->find('named', array('id_or_name', 'cke_'.$field));
            if (!$wrapper || !$wrapper->hasClass('cke')) {
                throw $e;
            }
            $this->getSession()->getDriver()->executeScript('
                CKEDITOR.instances["'.$field.'"].setData("'.$value.'");
            ');
        }
    }

    /**
     * @When I wait :seconds seconds
     */
    public function iWait($seconds)
    {
        $this->getSession()->wait((int) ($seconds * 1000));
    }

    /**
     * @When I try to download :path
     */
    public function iTryToDownload($path)
    {
        $url = $this->getSession()->getCurrentUrl().$path;
        $this->headers = get_headers($url);
        $this->getSession()->visit($url);
    }

    /**
     * @Then /^I should see response status code "([^"]*)"$/
     */
    public function iShouldSeeResponseStatusCode($statusCode)
    {
        $responseStatusCode = $this->getSession()->getStatusCode();
        if (!$responseStatusCode == (int) $statusCode) {
            throw new \Exception(sprintf('Did not see response status code %s, but %s.', $statusCode, $responseStatusCode));
        }
    }

    /**
     * @Then /^I should see in the header "([^"]*)"$/
     */
    public function iShouldSeeInTheHeader($header)
    {
        assert(in_array($header, $this->headers), "Did not see \"$header\" in the headers.");
    }

    /**
     * Checks if an element has a class
     * Copyright neemzy https://github.com/neemzy/patchwork-core.
     *
     * @Then /^"([^"]*)" element should have class "([^"]*)"$/
     */
    public function elementShouldHaveClass($selector, $class)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->find('css', $selector);
        if (!$element) {
            throw new ElementNotFoundException($session, 'Element "'.$selector.'"');
        }
        \PHPUnit_Framework_TestCase::assertTrue($element->hasClass($class));
    }

    /**
     * Checks if an element doesn't have a class
     * Copyright neemzy https://github.com/neemzy/patchwork-core.
     *
     * @Then /^"([^"]*)" element should not have class "([^"]*)"$/
     */
    public function elementShouldNotHaveClass($selector, $class)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->find('css', $selector);
        if (!$element) {
            throw new ElementNotFoundException($session, 'Element "'.$selector.'"');
        }
        \PHPUnit_Framework_TestCase::assertFalse($element->hasClass($class));
    }

    /**
     * Checks that a button is disabled.
     *
     * @Then /^the button "([^"]*)" should be disabled$/
     */
    public function buttonShouldBeDisabled($locator)
    {
        $locator = $this->fixStepArgument($locator);
        $button = $this->getSession()->getPage()->findButton($locator);

        if (null === $button) {
            throw new ElementNotFoundException($this->getSession(), 'button', 'id|name|title|alt|value', $locator);
        }

        \PHPUnit_Framework_TestCase::assertTrue($button->hasAttribute('disabled'));
    }

    /**
     * Checks that an element has an attribute.
     *
     * @Then /^the element "([^"]*)" should have attribute :attribute $/
     */
    public function elementHasAttribute($selector, $attribute)
    {
        $element = $this->getSession()->getPage()->find('css', $selector);

        if (null === $element) {
            throw new ElementNotFoundException($this->getSession(), 'element', 'css', $selector);
        }

        \PHPUnit_Framework_TestCase::assertTrue($element->hasAttribute($attribute));
    }

    /**
     * Checks that option from select with specified id|name|label|value is selected.
     *
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected/
     * @Then /^the option "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     * @Then /^"(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     */
    public function optionIsSelectedInSelect($option, $select)
    {
        $selectField = $this->getSession()->getPage()->findField($select);
        if (null === $selectField) {
            throw new ElementNotFoundException($this->getSession(), 'select field', 'id|name|label|value', $select);
        }

        $optionField = $selectField->find('named', array(
            'option',
            $option,
        ));

        if (null === $optionField) {
            throw new ElementNotFoundException($this->getSession(), 'select option field', 'id|name|label|value', $option);
        }

        if (!$optionField->isSelected()) {
            throw new ExpectationException('Select option field with value|text "'.$option.'" is not selected in the select "'.$select.'"', $this->getSession());
        }
    }

    private function visitPageWithParams($page, $params = [])
    {
        $this->currentPage = $page;
        $this->navigationContext->getPage($page)->open($params);
        $this->iWait(2);
    }

    /**
     * @override Given /^(?:|I )am on (?:|the )homepage$/
     * @override When /^(?:|I )go to (?:|the )homepage$/
     */
    public function iAmOnHomepage()
    {
        $this->visitPageWithParams('home page');
    }

    private function getCurrentPage()
    {
        if ($this->currentPage) {
            return $this->navigationContext->getPage($this->currentPage);
        }

        return;
    }
}
