<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Mockrr\Mockrr;
use Mockrr\Resource;
use Mockrr\JsonResource;
use function Mockrr\get;
use function Mockrr\post;
use Mockrr\Cache\SimplePsrFileCache;

// Set includes path to current folder
Mockrr::set_include_path(__DIR__);

// Init Mockrr
$cache = new SimplePsrFileCache();
$mockrr= new Mockrr($cache);

// Examples of routing;
// - GET/text
// - GET/static_xml
// - GET/generate_json
// - GET/include_file
// - GET/dynamic_path_param

// Get with callback
get( '/example/get', fn() => print "Simple GET" );

// Get a static resource
get('/example/static/xml', function () {
    header("Content-Type: application/xml");
    print file_get_contents(__DIR__ . '/res/sample.xml');
});

// Get dynamic json
get('/example/json', function () {

    $data = [
        'user_id' => bin2hex(random_bytes(4)),
        'name'    => 'John',
        'email'   => 'john@example.com'
    ];

    header("Content-Type: application/json");
    print json_encode($data);
});

// Include file
get('/example/include', '/include-file');

// Dynamic path params
get( '/example/country/$country/city/$city', function (string $country, string $city) {
    header("Content-Type: application/json");
    print json_encode(['ip'=> $_SERVER['REMOTE_ADDR'], 'country'=> $country, 'city'=> $city]);
});

// Dynamic path param with include
get('/example/include/$action', '/include-file');

// Examples of using Resource(s);
// - GET/resource_from_file
// - GET/static_xml
// - GET/generate_json
// - GET/include_file
// - GET/dynamic_path_param

// Generate resource from array
get('/example/resource', $mockrr->generate(['isResource'=>TRUE, 'type'=> Resource::DTYPE]));

// Generate resource from file
$resource = JsonResource::fromFile('/res/sample.json');
get('/example/resource/file', $resource);

// Generate resource once, given ID parameter
get('/example/resource/$id', function($id) use($mockrr) {
    $res = $mockrr->once($id, ['id'=> $id, 'checksum'=> random_int(999, 9999)]);
    $res->print();
});

// Generate resources in sequence for each request, for id and sequence
// GET /sequence/1 -> First
// GET /sequence/2 -> Second
// GET /sequence/3 -> Third
// GET /sequence/4 -> First
// GET /sequence/5 -> Second
// GET /sequence/6 -> Third ...
get('/example/sequence/$id', function($id) use($mockrr) {
    $resources = [
        JsonResource::fromString("First resource"),
        JsonResource::fromString("Second resource"),
        JsonResource::fromString("Third resource"),
    ];

    $mockrr
        ->sequence("sequence/$id", "sequence", $resources)
        ->print();
});


// Update cached resource
post('/example/resource/$id', function ($id) use ($mockrr, $resource){
    $mockrr
        ->update($id, ['updated_at'=> time(), 'update_log'=> $_POST['input_via_post']])
        ->print();
});

// Process update logic in another file
post('/example/secret/match', '/include-file-2');

// Display index
?>
<body style="font: inherit; font-size: 100%; font-family: monospace;">
    <style media="screen">
        code{
            padding: 10px;
            border-radius: 3px;
            background: bisque;
            color: black;
        }
    </style>
    <h2>Mockrr - simple API mocking library. In PHP.</h2>
    <h3>Examples index</h3>
    <ul>
        <li>
            <a href="/example/get">Request with callback</a>
            <pre><code>get( '/example/get', fn() => print "Simple GET" );</code></pre>
        </li>
        <li>
            <a href="/example/include/include">Include file on request</a>
            <pre><code>get('/example/include', '/include-file');</code></pre>
        </li>
        <li>
            <a href="/example/static/xml">Serve static resource from file</a><br/>
            <code><br/>
                get('/example/static/xml', function () {<br/>
                    header("Content-Type: application/xml");<br/>
                    print file_get_contents(__DIR__ . '/res/sample.xml');<br/>
                });
            </code>
        </li>
        <li><a href="/example/json">Generated resource</a></li>
        <li><a href="#" onclick="document.forms.postForm1.submit()">Update via <em>POST</em></a></li>
    </ul>
    <form name="postForm1" method="post" action="/example/resource/first">
        <input type="hidden" value="<?php echo bin2hex(random_bytes(8)); ?>" name="input_via_post"/>
    </form>

    <form name="postForm2" method="post" action="/example/secret/match">
        <input type="hidden" value="abcdefgh321" name="secret"/>
        <input type="text" value="<?php echo bin2hex(random_bytes(8)); ?>" name="new_secret"/>
        <button type="submit">replace secret</button>
    </form>
</body>
<?php
