<?php

// Use your module's testing namespace such as the one below.
namespace Drupal\Tests\lilengine_automated_test\ExampleSelenium2DriverTest;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\User;
use weitzman\DrupalTestTraits\ExistingSiteSelenium2DriverTestBase;
use weitzman\DrupalTestTraits\ScreenShotTrait;

/**
 * A WebDriver test suitable for testing Ajax and client-side interactions.
 */
class ExampleSelenium2DriverTest extends ExistingSiteSelenium2DriverTestBase
{
    use ScreenShotTrait;

    public function testContentCreation()
    {
      // Create a taxonomy term. Will be automatically cleaned up at the end of the test.
        $web_assert = $this->assertSession();
        $vocab = Vocabulary::load('tags');
        $this->createTerm($vocab, ['name' => 'Term 1']);
        $this->createTerm($vocab, ['name' => 'Term 2']);
        $admin = User::load(1);
        $admin->passRaw = 'admin';

        $this->drupalLogin($admin);
      // @codingStandardsIgnoreStart
      // These lines are left here as examples of how to debug requests.
      $this->captureScreenshot();

      // @codingStandardsIgnoreStop

      // Test autocomplete on article creation.
      $this->drupalGet('/node/add/article');
      $page = $this->getCurrentPage();
      $page->fillField('title[0][value]', 'Article Title');
      $tags = $page->findField('field_tags[target_id]');
      $tags->setValue('Ter');
      $tags->keyDown('m');
      $this->captureScreenshot();
      $result = $web_assert->waitForElementVisible('css', '.ui-autocomplete li');
      $this->assertNotNull($result);
      $this->captureScreenshot();



      // Click the autocomplete option
      $result->click();
      // Verify that correct the input is selected.
      $web_assert->pageTextContains('Term 1');
      $submit_button = $page->findButton('Save');
      $submit_button->press();
      // Verify the URL and get the nid.
      $this->assertTrue((bool) preg_match('/.+node\/(?P<nid>\d+)/', $this->getUrl(), $matches));
      $node = Node::load($matches['nid']);
      $this->markEntityForCleanup($node);
      // Verify the text on the page.
      $web_assert->pageTextContains('Article Title');
      $web_assert->pageTextContains('Term 1');
  }
}

