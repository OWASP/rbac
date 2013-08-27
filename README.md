#PhpRbac v2.0-beta Release

Home Page: [http://phprbac.net/](http://phprbac.net/)

Github Project Page: [https://github.com/OWASP/rbac](https://github.com/OWASP/rbac)

##PhpRbac's Move Towards PSR Compliance

We are in the process of refactoring PhpRbac in order to meet PHP-FIG PSR compliance: http://www.php-fig.org/

**Steps Towards Full PSR Compliance**

* **(Completed)** Create a PSR-0 compliant wrapper around the existing PhpRbac v1.0 code base

    * We now have a PSR-0 compliant wrapper which can be autoloaded using a PSR-0 Autoloader
    
    * The PSR wrapper has it's own PHPUnit Test suites for both Mysql and Sqlite implementations

* Continue working on refactoring the back end code to meet PSR recommendations

	* With the PSR wrapper in place we can continue to work towards full PSR compliance one piece at a
	time without altering the public interface that developers are working with, making the transition
	towards PSR compliance as seamless and invisible as possible
	
##Installation

**Using a PSR-0 Compliant Autoloader**

Point your Autoloader to 'PhpRbac/src' using the 'PhpRbac' namespace:

Example:
    
    $loader->add('PhpRbac\\', '/path/to/PhpRbac/src');
    
**Using Composer**

Coming Soon...

**Manually Loading PhpRbac**

Include autoload.php:

	require '/path/to/PhpRbac/autoload.php';
	
##Usage##

**Instantiating a PhpRbac Object**

Example:

	$rbac = new PhpRbac\Rbac();
	
##Documentation##

We are still in the process of updating our documentation, but the interface is very similar to the
interface described in the existing PhpRbac tutorial ([http://phprbac.net/tutorial.php](http://phprbac.net/tutorial.php)).

Right now the best way to become familiar with the public interface is to browse the unit tests in the
'PhpRbac/tests/src/' folder.