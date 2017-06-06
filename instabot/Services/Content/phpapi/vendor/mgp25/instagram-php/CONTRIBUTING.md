# GENERAL PROJECT RULES

Important! Your contributions MUST follow ALL of these rules, for a consistent and bug-free project. Otherwise WE will have to fix YOUR BUGS every time you contribute something. And that hurts us and it hurts every user and every other developer.

Write. Your. Code. **Carefully.** Otherwise we don't want it.

Please don't dump some sloppy, idiotic mess on us. It's like walking into our house and taking a shit on our floor and saying "here, merge this steaming, brown pile of shit".

Guess what? We don't want your sloppy 1-second edits that break everything. If you _can't_ do it properly, then ask _someone else_ to do the changes.

This is a document which, among other things, describes PSR-4 class autoloading, clean commits, how to handle/document exceptions, how to structure function arguments, naming clear variables, always adding/updating PHPdoc blocks in all affected functions, and how to verify that your changes don't _break everything_ when performing big changes to existing functions or adding brand new functions and classes.

This document is _not_ some soft "recommendation". It's a _strong requirement_, to prevent turning this project into a broken, buggy mess again, the way it was in its wild and buggy past.

Everyone who _ever_ wants to contribute needs to familiarize themselves with _everything_ in this document. Nobody wants to go back to the broken, buggy mess that was version 1.x of this library.

Thank you.


## COMMITS

- You **MUST** try to **separate** work into smaller commits. So if you `"changed Utils.php to fix getSeconds and changed Request.php to fix Request()"`, then make **TWO SEPARATE COMMITS** for those totally unrelated changes!
- If you stupidly insist on grouping everything into 1 blobby mess, it HURTS us, because it means that WE can't revert broken changes without also reverting tons of other unintentional changes. It also makes the commit history impossible to read/understand. So don't be a stupid idiot. Separate your work into smaller commits, with 1 task per commit!
- Use the detailed description field of your commit, to document why you did certain changes if it's not an obvious change (such as a typo fix). Your description gets added to the history of the project so that we can know what you were thinking, if we need to check the change again later.
- **Name** your single-file commits `"AffectedClassName: Change description"` and keep the total length of the summary line at 50 characters or less in total (the Git/GitHub standard).
- If your change NEEDS to affect multiple files, then it is SOMETIMES okay to leave out the classname part from the summary, but in that case you _must_ write a very clear and descriptive summary to explain it. _But_ usually you _should_ still include the classname even for multi-file edits. For example, renaming a function in Utils and having to change the name of it in other files is still a `"Utils: Renamed bleh() to bloop()"` commit.

Examples of BAD commit summaries (you are a _total moron_ if you write like this and you aren't just hurting us, you are hurting your own project if this is your idiotic "style"):

```
Edit something
Fix this
Utils+request changes
```

Examples of GOOD commit summaries:

```
Utils: Fix formatting of getSeconds
Request: Send cropping information
Changed all number_format() to round() everywhere
Response: Parse timestamps as floats
```


## MODIFYING -ANYTHING- IN THE EXISTING CODE -WHATSOEVER-

- If you want to change a public API function's parameters (particularly in `src/Instagram.php`), think EXTREMELY hard about NOT doing it. And if you absolutely MUST do it (such as adding an important new parameter), then you MUST add it in a BACKWARDS-COMPATIBLE WAY, by adding it as the LAST parameter of the argument list AND providing a sensible DEFAULT VALUE for it so that people's CURRENT projects based on this library continue working and DON'T BREAK due to your function definition changes!
- In programming, EVERYTHING you do MATTERS. EVERY word MATTERS. EVERY variable type-change MATTERS. EVERY new/deleted exception MATTERS. And it matters a LOT. Because with 1 "innocent" _idiotic_ edit, you can BADLY BREAK a LOT of other code.
- Do NOT look at your changes in isolation. Look at the BIG PICTURE of what your change now does to the REST of the codebase.
- For example, if you change the returned variable type from a function (such as from an `"int"` to a `"string"`), then you MUST update the function's PHPdoc block to document the new return value (with `@return string`), and you MUST also slowly and carefully check ALL OTHER CODE that relied on the OLD behavior of that function. There's a command-line tool called "grep" to search for text in files. USE IT to check ALL other code locations that called your modified function, and make sure that you didn't break ANY of them AT ALL!
- In fact, ANY TIME that you change a function's ARGUMENTS, RETURN VALUE or THROWN EXCEPTIONS (new/deleted ones), then you MUST update the function's PHPdoc block to match the new truth. And you MUST check ALL other functions that CALL your function, and update those too. For example let's say they DON'T handle a new exception you're now throwing, in which case you MUST now update THEIR `@throws` documentation to document the fact that THEY now let yet another exception bubble up. And then you MUST search for the functions that called THOSE updated functions and update THEIR documentation TOO, all the way until you've reached the top and have FULLY documented what exceptions are being thrown in the whole chain of function calls. This requirement is NOT negotiable. You MUST do this work. **OTHERWISE YOU HAVE TOTALLY DESTROYED THE PROJECT FOR ANYONE TRYING TO FIGURE OUT WHAT EXCEPTIONS TO CATCH TO AVOID SUDDEN EXCEPTIONS KILLING THE ENTIRE PROGRAM!**
- This needs repeating because idiots always forget this: You MUST ALWAYS update the PHPdoc blocks EVERYWHERE that's affected by your changes (whenever you've changed a functions ARGUMENTS or RETURN type or what it THROWS (any new/deleted exceptions)), because imagine if we NEVER update the blocks EVERYWHERE that's affected by your change. Well, after just one month of such moronic contributions, NOTHING IN THE CODEBASE WOULD BE RELIABLE ANYMORE, and TONS of bugs would be added due to the terrible documentation. We would then have a horrible mess like `/** @param $a, $b, $c */ function foo ($x)` and sudden, critical exceptions that aren't getting handled and thus KILL the program, which means that your idiotic and sloppy code changes have **DESTROYED and BROKEN the WHOLE project. Don't. Do. That! You must ALWAYS keep the documentation up-to-date with YOUR damn changes so that WE and the USERS can always TRUST it to remain correct and stable.**
- **If you can't keep the documentation up to date, then we don't want your code.** Because BROKEN documentation BADLY HURTS EVERYONE WHO IS WORKING ON THE PROJECT. It's not clever. It's not nice. It's just BAD. **It's like walking into our house and shitting on our nice, clean floor.**

Here's a checklist for what you MUST do to ENSURE totally correct documentation EVERY TIME that you make a CHANGE to a function's ARGUMENTS or RETURN TYPE or EXCEPTIONS of an existing function, OR when you introduce a NEW function whose use is being added to any existing functions.

1. You MUST ALWAYS use grep. Find EVERY other code location that uses your new/modified function.
2. Check: Did your changes just BREAK EVERYTHING somewhere else, which now has to be updated to match? Almost GUARANTEED the answer is YES, and you MUST update the other locations that expected the old function behavior/return type/parameters/exceptions.
3. Check: Do you need to also UPDATE the PHPdoc for those OTHER functions too? OFTEN the answer is YES. For example, if you've changed the exceptions that a deep, internal function throws, then you MUST either catch it in all higher functions and do something with it, OR let it pass through them upwards. And if you do let it pass through and bubble up then you MUST ALSO update the PHPdoc for THAT higher function to say `"@throws TheNewException"` (or delete something in case you changed a subfunction to no longer throw).
4. If your updates to the affected higher-level functions in steps 2/3 means that THOSE other functions now ALSO behave differently (meaning THEY have new return values/exceptions/parameters), then you MUST also do ANOTHER grep to check for anything that uses THOSE functions, and update all of those code locations TOO.
5. Repeat that process ALL THE WAY for EVERY FUNCTION until you reach the top level of the code.
6. Now everything is bug-free and up to date. Yes, it's a lot of work, and **YES YOU _MUST_ DO IT.**


## CRITICALLY IMPORTANT CODE STYLE

### Namespaces

- Organize all classes into logical namespaces. Look at our current organization and ask yourself "Am I an idiot, or am I properly following their code organization?".
- We follow the PSR-4 Autoloading standard, which means that you MUST only have ONE class per source code `.php` file. And the namespace AND classname MUST match BOTH its disk path AND its `.php` filename. In our project, the `src/` folder is the `InstagramAPI` namespace, and everything under that is for its subnamespaces (folders) and our classes (PHP files).
- Again, you MUST only have a SINGLE class per source code `.php` file. Adding multiple classes into one file does NOT work and BREAKS the PSR-4 autoloader!

Example of a proper class in our top-level namespace:

```php
src/Something.php:
<?php

namespace InstagramAPI;

class Something
{
    ....
}
```

Example of a proper class in a sub-namespace:

```php
src/Wonderful/Things/Something.php:
<?php

namespace InstagramAPI\Wonderful\Things;

class Something
{
    ....
}
```

Failure to follow this rule means that your class cannot be autoloaded. Don't be an idiot. Follow the STRICT RULE above.

### Functions and Variables

- You MUST split all function declaration/definition arguments onto separate lines, but only IF the function takes arguments. This is done for clarity, and to avoid unintentional bugs by contributors. It ensures that any changes to function arguments will be diffed as a single-line change. It's more readable. And it's the style _we_ use.

Example of a function that takes no arguments:

```php
public function doSomething()
{
    ...
}
```

Example of a function that takes one argument:

```php
public function doSomething(
    $foo = null)
{
    ...
}
```

Example of a function that takes multiple arguments:

```php
public function doSomething(
    $foo,
    $bar = '',
    $baz = null)
{
    ...
}
```

- All properties and functions MUST be named using `camelCase`, NOT `snake_case`. (The _only_ exception to this rule is  Instagram's server response property objects, meaning everything in the `src/Response/` folder, since _their_ server replies use underscores.)
- Private and protected properties/functions MUST be _prefixed_ with an underscore, to clearly show that they are internal inside the class and _cannot_ be used from the outside. But the PUBLIC properties/functions MUST _NEVER_ be prefixed with an underscore (_except_ the _obvious_, specially named, built-in PHP double-underscore ones such as `public function __construct()`). And also note that function _arguments_ (such as in setters) MUST NEVER be prefixed by underscores.

Example of a class with proper function and property underscore prefixes for private/protected members, and with the proper function argument style:

```php
class Something
{
    public $publicProperty;
    protected $_protectedProperty;
    private $_privateProperty;
    
    public function getProtectedProperty()
    {
        return $this->_protectedProperty;
    }
    
    public function setProtectedProperty(
        $protectedProperty)
    {
        $this->_protectedProperty = $protectedProperty;
    }

    public function getPublicProperty()
    {
        return $this->_publicProperty;
    }
    
    public function setPublicProperty(
        $publicProperty)
    {
        $this->publicProperty = $publicProperty;
    }
    
    protected function _somethingInternal()
    {
        ...
    }
    
    ...
}
```

- All functions and variables MUST have descriptive names that document their purpose automatically. NO `$x` bullshit. Use names like `$videoFilename` and `$deviceInfo` and so on, so that the code documents itself instead of needing tons of comments to explain what each step is doing.

Examples of TERRIBLE variable names:

```php
$x = $po + $py;
$w = floor($h * $ar);
```

Examples of GOOD variable names:

```php
$endpoint = $this->url.'?'.http_build_query($this->params);
$this->_aspectRatio = $this->_width / $this->_height;
$width = floor($this->_height * $this->_maxAspectRatio);
```

- All functions MUST have occasional comments that explain what they're doing in their various substeps. Look at our codebase and follow our commenting style.
- Our comments start with a capital letter and end in punctuation, and describe the purpose in as few words as possible, such as: `// Default request options (immutable after client creation).`
- All functions MUST do as little work as possible, so that they are easy to maintain and bugfix. Huge algorithms MUST therefore be broken into smaller functions, to AVOID SPAGHETTI CODE. We DON'T want a 500-line function. Instead, we want a clean 10-line function which in turn calls four separate "task-oriented" functions to cleanly achieve the same work that the 500-line function achieved. With separation into smaller tasks, the code becomes maintainable and can easily be re-used to do other jobs. If you just dump it all into a single function like a moron, then it becomes impossible to maintain or re-use for other purposes.

Example of a GOOD function layout:

```php
function requestVideoURL(...);

function uploadVideoChunks(...);

function configureVideo(...);

function uploadVideo(...)
{
    $url = $this->requestVideoURL();
    if (...handle any errors from the previous function)
    
    $uploadResult = $this->uploadVideoChunks($url, ...);
    
    $this->configureVideo($uploadResult, ...);
}
```

Example of a TERRIBLE function layout which totally locks us into a monolithic, idiotic function which can barely be maintained and can never, ever be re-used for another purpose:

```php
function uploadVideo(...)
{
    // Request upload URL.
    // Upload video data to URL.
    // Configure its location property.
    // Post it to a timeline.
    // Call your grandmother.
    // Make some tea.
    // ...and 500 other lines of code.
}
```

- Do NOT hardcode illogical behaviors into functions. For example, if the function is called `getVideoLength($videoFilename)` and returns the length of a video, and you needed to create that function because you wanted to output a "length" string with 4 decimals somewhere, then DO NOT put the `number_format()` "4 decimal"-STRING FORMATTING inside of `getVideoLength()`. Put THAT where the actual formatting is NEEDED. That way you DON'T hardcode BAD behaviors which we can never get rid of later. It's MUCH more useful to have a function called `getVideoLength()` which returns a numeric FLOAT which we can FULLY RELY ON and can then FORMAT ANY WAY WE WANT _anywhere_ that we need different formatting.
- All function parameter lists MUST be well-thought out so that they list the most important arguments FIRST and so that they are as SIMPLE as possible to EXTEND in the FUTURE, since Instagram's API changes occasionally.

We DON'T want you to give us a function like this:

```php
function uploadVideo($videoFilename, $filter, $url, $caption, $userTags, $hashTags);
```

That would be an absolute HELL to extend later, and it's HELL for users to _use_ (if they only wanted to provide `$hashTags`, they would need to call that function like `uploadVideo($videoFilename, null, null, null, null, $hashTags);` and must count all arguments carefully.

DON'T design functions like that. Don't be an idiot!

Make such multi-argument functions take future-extensible option-arrays instead, especially if you expect that more properties may be added in the future.

Furthermore, its `uploadVideo` name is too generic. What if we _later_ need to be able to upload Story videos, when Instagram added its Story feature? And Album videos? Suddenly, the existing `uploadVideo` function name would be a huge problem for us.

So the above would instead be PROPERLY designed as follows:

```php
function uploadTimelineVideo($videoFilename, array $metadata);
```

Now users can just say `uploadTimelineVideo($videoFilename, ['hashtags'=>$hashTags]);`, and we can easily add more metadata fields in the future without ever breaking backwards-compatibility with projects that are using our function! And since the function name is good and _specific_, it also means that we can easily add _other_ kinds of "video" upload functions for any _future features_ Instagram introduces, simply by creating new functions such as `uploadStoryVideo`, which gives us total freedom to implement Instagram's new features without breaking backwards-compatibility with anyone using the _other_ functions.

### Function Documentation

- All functions MUST have _COMPLETE_ PHPdoc doc-blocks. The critically important information is the single-sentence `summary-line` (ALWAYS), then the `detailed description` (if necessary), then the `@param` descriptions (if any), then the `@throws` (one for EVERY type of exception that it throws, even uncaught ones thrown from DEEPER functions called within this function), then the `@return` (if the function returns something), and lastly one or more `@see` if there's any need for a documentation reference to a URL or another function or class.

Example of a properly documented function:

```php
    /**
     * Generates a User Agent string from a Device (<< that is the REQUIRED ONE-SENTENCE summary-line).
     *
     * [All lines after that are the optional description. This function didn't need any,
     *  but you CAN use this area to provide extra information describing things worth knowing.]
     *
     * @param \InstagramAPI\Devices\Device $device The Android device.
     * @param string[]|null                $names (optional) Array of name-strings.
     *
     * @throws \InvalidArgumentException                  If the device parameter is invalid.
     * @throws \InstagramAPI\Exception\InstagramException In case of invalid or failed API response.
     *
     * @return string
     *
     * @see otherFunction()
     * @see http://some-url...
     */
    public static function buildUserAgent(
        Device $device,
        $names = null)
    {
        ...
    }
```

- You MUST take EXTREMELY GOOD CARE to ALWAYS _perfectly_ document ALL parameters, the EXACT return-type, and ALL thrown exceptions. All other project developers RELY on the function-documentation ALWAYS being CORRECT! With incorrect documentation, other developers would make incorrect assumptions and _severe_ bugs would be introduced!

### Exceptions (EXTREMELY IMPORTANT)

- ALL thrown exceptions that can happen inside a function or in ANY of its SUB-FUNCTION calls MUST be documented as `@throws`, so that we get a COMPLETE OVERVIEW of ALL exceptions that may be thrown when we call the function. YES, that EVEN means exceptions that come from deeper function calls, whose exceptions are NOT being caught by your function and which will therefore bubble up if they're thrown by those deeper sub-functions!
- Always remember that Exceptions WILL CRITICALLY BREAK ALL OTHER CODE AND STOP PHP'S EXECUTION if not handled or documented properly! They are a LOT of responsibility! So you MUST put a LOT OF TIME AND EFFORT into PROPERLY handling (_catching and doing something_) for ALL exceptions that your function should handle, AND adding PHPdoc _documentation_ about the ones that your function DOESN'T catch/handle internally and which WILL therefore bubble upwards and would possibly BREAK other code (which is EXACTLY what would happen if an exception ISN'T documented by you and someone then uses your bad function and doesn't "catch" your exception since YOU didn't tell them that it can be thrown)!
- All of our internal exceptions derive from `\InstagramAPI\Exception\InstagramException`, so it's always safe to declare that one as a `@throws \InstagramAPI\Exception\InstagramException` if you're calling anything that throws exceptions based on our internal `src/Exception/*.php` system. But it's even better if you can pinpoint which exact exceptions are thrown, by looking at the functions you're calling and seeing their `@throws` documentation, WHICH OF COURSE DEPENDS ON PEOPLE HAVING WRITTEN PROPER `@throws` FOR THOSE OTHER FUNCTIONS SO THAT _YOU_ KNOW WHAT THE FUNCTIONS YOU'RE CALLING WILL THROW. DO YOU SEE _NOW_ HOW IMPORTANT IT IS TO DECLARE EXCEPTIONS PROPERLY AND TO _ALWAYS_ KEEP THAT LIST UP TO DATE?!
- Whenever you are using an EXTERNAL LIBRARY that throws its own custom exceptions (meaning NOT one of the standard PHP ones such as `\Exception` or `\InvalidArgumentException`, etc), then you MUST ALWAYS re-wrap the exception into some appropriate exception from our own library instead, otherwise users will not be able to say `catch (\InstagramAPI\Exception\InstagramException $e)`, since the 3rd party exceptions wouldn't be derived from our base exception and wouldn't be caught, thus breaking the user's program. To solve that, look at the design of our `src/Exception/NetworkException.php`, which we use in `src/Client.php` to re-wrap all Guzzle exceptions into our own exception type instead. Read the source-code of our NetworkException and it will explain how to properly re-wrap 3rd party exceptions and how to ensure that your re-wrapped exception will give users helpful messages and helpful stack traces.


# CONTRIBUTING NEW ENDPOINTS

In order to add endpoints to the API you will need to capture the requests first. For that, you can use any HTTPS proxy you want. You can find a lot of information about this on the internet. Remember that you need to install a root CA (Certificate Authority) in your device so that the proxy can decrypt the requests and show them to you.


Once you have the endpoint and necessary parameters, how do you add them to this library? Easy, you can follow this example:

```php
    public function getAwesome()
    {
        return $this->request('awesome/endpoint/')
        ->setSignedPost(false)
        ->addPost('_uuid', $this->uuid)
        ->addPost('user_ids', implode(',', $userList))
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\AwesomeResponse());
    }
```

In the example above you can see `('awesome/endpoint/')` which is the endpoint you captured. We are simulating a POST request, so you can add POST parameters easily by doing `->addPost('_uuid', $this->uuid)`.

Which is basically:

```php
->addPost(key, value)
```

Where key is the name of the POST param, and value is whatever value the server requires for that parameter.

Some of the requests are signed. This means there is a hash concatenated to the JSON. In order to make a signed request, we can enable or disable signing with the following line:

```php
->setSignedPost($isSigned)
```

`$isSigned` is boolean, if you want a signed request, you simply set it to `true`.

If the request is a GET request, you can add the GET query parameters like this (instead of using `addPost`):

```php
->addParams(key, value)
```

And finally, we always end with the `getResponse` function call, which will read the response and return an object with all of the server response values:

```php
->getResponse(new Response\AwesomeResponse());
```

Now you might be wondering how to create that response class? But there is nothing to worry about, it's very simple.

Imagine that you have the following response:

```json
{"items": [{"user": {"is_verified": false, "has_anonymous_profile_picture": false, "is_private": false, "full_name": "awesome", "username": "awesome", "pk": "uid", "profile_pic_url": "profilepic"}, "large_urls": [], "caption": "", "thumbnail_urls": ["thumb1", "thumb2", "thumb3", "thumb4"]}], "status": "ok"}
```

You can use [http://jsoneditoronline.org](http://jsoneditoronline.org/) for a better visualization:

<img src="https://s29.postimg.org/3xyopcbg7/insta_help.jpg" width="300">

So your new `src/Response/AwesomeResponse.php` class should contain one public var named `items`. Our magical JSONMapper object mapping system also needs a PHPdoc comment to tell us if the property is another class, an array, a string, a string array, etc. By default, if you don't specify any comment, it will read the JSON value as whatever type PHP detected it as internally (such as a string, int, float, bool, etc).

In this scenario:

```php
    /**
     * @var Model\Suggestion[]
     */
    public $items;
 ```
 
The `$items` property will contain an array of Suggestion model objects. And `src/Response/Model/Suggestion.php` will look like this:

```php
<?php

namespace InstagramAPI\Response\Model;

class Suggestion extends \InstagramAPI\Response
{
    public $media_infos;
    public $social_context;
    public $algorithm;
    /**
     * @var string[]
     */
    public $thumbnail_urls;
    public $value;
    public $caption;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string[]
     */
    public $large_urls;
    public $media_ids;
    public $icon;
}
```

Here in this `Suggestion` class you can see many variables that didn't appear in our example endpoint's response, but that's because many other requests _re-use_ the same object, and depending the request, the response variables may differ. Also note that unlike the AwesomeResponse class, the actual Model objects (the files in in `src/Response/Model/`) _don't_ have to use the "Model\" prefix when referring to other model objects, since they are in the same namespace already.

Note that any Model objects relating to Media IDs, PKs, User PKs, etc, _must_ be declared as a `/** @var string */`, otherwise they may be handled as a float/int which won't fit on 32-bit CPUs and will truncate the number, leading to the wrong data. Just look at all other Model objects that are already in this project, and be sure that any ID/PK fields in your new Model object are properly tagged as `string` type!

Lastly, our `src/Response/AwesomeResponse.php` should look as follows:

```php
<?php

namespace InstagramAPI\Response;

class AwesomeResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\Suggestion[]
     */
    public $items;
}
```

Now you can test your new endpoint, in order to see the response object:

```
$a = $i->getAwesome();
var_dump($a); // this will print the response object
```

And finally, how do you access the object's data? Via the magical `AutoPropertyHandler` which you inherited from thanks to always extending from the `\InstagramAPI\Response` object. It automatically creates getters and setters for all properties.

```php
$items = $a->getItems();
$user = $items[0]->getUser();
```

Hope you find this useful.
