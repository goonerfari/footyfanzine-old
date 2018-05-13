<?php

namespace Drupal\simpletest_example\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensure that the simpletest_example content type provided functions properly.
 *
 * The SimpleTestExampleTest is a functional test case, meaning that it
 * actually exercises a particular sequence of actions through the web UI.
 * The majority of core test cases are done this way, but the SimpleTest suite
 * also provides unit tests as demonstrated in the unit test case example later
 * in this file.
 *
 * Functional test cases are far slower to execute than unit test cases because
 * they require a complete Drupal install to be done for each test.
 *
 * @see Drupal\simpletest\WebTestBase
 * @see SimpleTestUnitTestExampleTestCase
 *
 * @ingroup simpletest_example
 *
 * SimpleTest uses group annotations to help you organize your tests.
 *
 * @group simpletest_example
 * @group examples
 */
class SimpleTestExampleTest extends WebTestBase {

  /**
   * Our module dependencies.
   *
   * In Drupal 8's SimpleTest, we declare module dependencies in a public
   * static property called $modules. WebTestBase automatically enables these
   * modules for us.
   *
   * @var array
   */
  static public $modules = ['simpletest_example'];

  /**
   * The installation profile to use with this test.
   *
   * We use the 'minimal' profile so that there are some reasonable default
   * blocks defined, and so we can see the menu link created by our module.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Test SimpleTest Example menu and page.
   *
   * Enable SimpleTest Example and see if it can successfully return its main
   * page and if there is a link to the simpletest_example in the Tools menu.
   */
  public function testSimpleTestExampleMenu() {
    // Test for a link to the simpletest_example in the Tools menu.
    $this->drupalGet('');
    $this->assertResponse(200, 'The Home page is available.');
    $this->assertLinkByHref('examples/simpletest-example');

    // Verify that anonymous can access the simpletest_examples page.
    $this->drupalGet('examples/simpletest-example');
    $this->assertResponse(200, 'The SimpleTest Example description page is available.');
  }

  /**
   * Test node creation through the user interface.
   *
   * Creates a node using the node/add form and verifies its consistency in
   * the database.
   */
  public function testSimpleTestExampleCreate() {
    // Create a user with the ability to create our content type. This
    // permission is generated by the node module.
    $user = $this->createUser(['create simpletest_example content']);
    // Log in our user.
    $this->drupalLogin($user);

    // Create a node using the node/add form.
    $edit = [];
    $edit['title[0][value]'] = $this->randomMachineName(8);
    $edit['body[0][value]'] = $this->randomMachineName(16);
    $this->drupalPostForm('node/add/simpletest_example', $edit, 'Save');

    // Check that our simpletest_example node has been created.
    $this->assertText(t('@post @title has been created.', [
      '@post' => 'SimpleTest Example Node Type',
      '@title' => $edit['title[0][value]'],
    ]));
    // Check that the node exists in the database.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $this->assertTrue($node, 'Node found in database.');

    // Verify 'submitted by' information. Drupal adds a newline in there, so
    // we have to check for that.
    $username = $this->loggedInUser->getUsername();
    $datetime = format_date($node->getCreatedTime());
    $submitted_by = "Submitted by $username\n on $datetime";

    $this->drupalGet('node/' . $node->id());
    $this->assertText($submitted_by);
  }

  /**
   * Create a simpletest_example node and then see if our user can edit it.
   *
   * Note that some assertions in this test will fail. We do this to show what
   * a failing test looks like. Since we don't want this to interfere with
   * automated tests, however, we jump through some hoops to determine our
   * environment.
   */
  public function testSimpleTestExampleEdit() {
    // Create a user with our special permission.
    $user = $this->drupalCreateUser(['extra special edit any simpletest_example']);
    // Log in our user.
    $this->drupalLogin($user);

    // Create a node with our user as the creator.
    // drupalCreateNode() uses the logged-in user by default.
    $settings = [
      'type' => 'simpletest_example',
      'title' => $this->randomMachineName(32),
    ];
    $node = $this->drupalCreateNode($settings);

    // For debugging, we might output some information using $this->verbose()
    // It will only be output if the testing settings have 'verbose' set.
    $this->verbose('Node created: ' . $node->getTitle());

    // This section demonstrates a failing test. However, we want this test to
    // pass when it's running on the Drupal QA testbot. So we need to determine
    // which environment we're running inside of before we continue.
    if (!$this->runningOnTestbot()) {
      $this->drupalGet('node/' . $node->id() . '/edit');
      // The debug() statement will output information into the test results.
      // It can also be used in Drupal anywhere in code and will come out
      // as a drupal_set_message().
      debug('The following test should fail. Examine the verbose message above it to see why.');
      // Make sure we don't get a 401 unauthorized response:
      $this->assertResponse(200, 'User is allowed to edit the content.');

      // Looking for title text in the page to determine whether we were
      // successful opening edit form.
      $this->assertText(t("@title", ['@title' => $settings['title']]), "Found title in edit form");
    }
  }

  /**
   * Detect if we're running on PIFR testbot.
   *
   * We can skip intentional failure if we're on the testbot. It happens that
   * on the testbot the site under test is in a directory named 'checkout' or
   * 'site_under_test'.
   *
   * @return bool
   *   TRUE if running on testbot.
   */
  public function runningOnTestbot() {
    // @todo: Add this line back once the testbot variable is available.
    // https://www.drupal.org/node/2565181
    // return env('DRUPALCI');
    return TRUE;
  }

}
