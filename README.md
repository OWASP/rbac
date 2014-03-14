#PHP-RBAC v2.x

PHP-RBAC is the de-facto authorization library for PHP. It provides developers with NIST Level 2 Hierarchical Role Based Access Control and more, in the fastest implementation yet.

**Current Stable Release:** [PHP-RBAC v2.0]()

* Home Page: [http://phprbac.net/](http://phprbac.net/)
* OWASP Project Page: [https://www.owasp.org/index.php/Phprbac](https://www.owasp.org/index.php/Phprbac)
* User Documentation: [http://phprbac.net/docs_contents.php](http://phprbac.net/docs_contents.php)
* Development Documentation: [https://github.com/OWASP/rbac/wiki](https://github.com/OWASP/rbac/wiki)
* Support: [https://github.com/OWASP/rbac/issues?state=open](https://github.com/OWASP/rbac/issues?state=open)

##What is an Rbac System?

Take a look at the "[Before You Begin](http://phprbac.net/docs_before_you_begin.php)" section of our [Documentation](http://phprbac.net/docs_contents.php) to learn what an RBAC system is and what PHP-RBAC has to offer you and your project.

##NIST Level 2 Compliance

For information regarding NIST RBAC Levels, please see [This Paper](http://csrc.nist.gov/rbac/sandhu-ferraiolo-kuhn-00.pdf).

For more great resources see the [NIST RBAC Group Page](http://csrc.nist.gov/groups/SNS/rbac/).

##Installation

You can now use [Composer](https://getcomposer.org/) to install the PHP-RBAC code base.

For Installation Instructions please refer to the "[Getting Started](http://phprbac.net/docs_getting_started.php)" section of our [Documentation](http://phprbac.net/docs_contents.php).

##Usage##

**Instantiating a PHP-RBAC Object**
    
With a 'use' statement:

    use PhpRbac;

    $rbac = new Rbac();

Without a 'use' statement, outside of a namespace:

    $rbac = new PhpRbac\Rbac();

Without a 'use' statement, inside of another namespace (notice the leading backslash):

    $rbac = new \PhpRbac\Rbac();
    
##PHP-RBAC and PSR

PHP-RBAC's Public API is now fully PSR-0, PSR-1 and PSR-2 compliant.

You can now:

* Use Composer to install/update PHP-RBAC
* Use any PSR-0 compliant autoloader with PHP-RBAC
* Use the included autoloader to load PHP-RBAC

**If you notice any conflicts with PSR compliance please [Submit an Issue](https://github.com/OWASP/rbac/issues/new).**

##The future of PHP-RBAC

We are in the process of refactoring the PHP-RBAC internals. We have two goals in mind while doing this:

* To meet modern PHP OOP "Best Practices"
* To meet PHP-FIG PSR compliance: http://www.php-fig.org/

With a PSR compliant Public API already in place we can continue to work towards our goals one piece at a
time without altering the Public API that developers are working with and rely on, making the transition as
seamless and invisible as possible.

##Contributing##

We welcome all contributions that will help make PHP-RBAC even better tomorrow than it is today!

**How You Can Help:**

* Report Bugs, Enhancement Requests or Documentation errors using our [Issue Tracker](https://github.com/OWASP/rbac/issues?state=open)
* [Choose a Bug](https://github.com/OWASP/rbac/issues?state=open) to work on and submit a Pull Request
* Make helpful suggestions and contributions to the [Documentation](http://phprbac.net/docs_contents.php) using our [Issue Tracker](https://github.com/OWASP/rbac/issues?state=open)
* Spread the word about PHP-RBAC by:
    * Creating Tutorials
    * Blogging
    * [Tweeting](https://twitter.com/)
    * [Facebooking](https://www.facebook.com/)
    * [Google+ing](https://plus.google.com/)
    * Talking to friends and colleagues about us