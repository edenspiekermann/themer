<?php
# (c) Chris O'Hara <cohara87@gmail.com> (MIT License)
# http://github.com/chriso/klein.php

$__routes = array();

//Add a route callback
function respond($method, $route, $callback = null) {
    global $__routes;
    $__routes[] = array($method, $route, $callback);
    return $callback;
}

//Dispatch the request to the approriate route(s)
function dispatch($uri = null, $req_method = null, array $params = null, $capture = false) {
    global $__routes;

    //Pass three parameters to each callback, $request, $response, and a blank object for sharing scope
    $request  = new _Request;
    $response = new _Response;
    $app      = new StdClass;

    //Get/parse the request URI and method
    if (null === $uri) {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }
    if (false !== strpos($uri, '?')) {
        $uri = strstr($uri, '?', true);
    }
    if (null === $req_method) {
        $req_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    //Force request_order to be GP
    //http://www.mail-archive.com/internals@lists.php.net/msg33119.html
    $_REQUEST = array_merge($_GET, $_POST);
    if (null !== $params) {
        $_REQUEST = array_merge($_REQUEST, $params);
    }

    $matched = false;
    $apc = function_exists('apc_fetch');

    ob_start();

    foreach ($__routes as $handler) {
        list($method, $_route, $callback) = $handler;

        //Was a method specified? If so, check it against the current request method
        if (is_callable($_route)) {
            $callback = $_route;
            $_route = $method;
        } elseif (is_array($method)) {
            $method_match = false;
            foreach ($method as $test) {
                if (strcasecmp($req_method, $test) === 0) {
                    $method_match = true;
                    continue;
                }
            }
            if (false === $method_match) {
                continue;
            }
        } elseif (strcasecmp($req_method, $method) !== 0) {
            continue;
        }

        //! is used to negate a match
        if ($_route[0] === '!') {
            $negate = true;
            $i = 1;
        } else {
            $negate = false;
            $i = 0;
        }

        //Check for a wildcard (match all)
        if ($_route === '*') {
            $match = true;

        //@ is used to specify custom regex
        } elseif ($_route[$i] === '@') {
            $match = preg_match('`' . substr($_route, $i + 1) . '`', $uri, $params);

        //Compiling and matching regular expressions is relatively
        //expensive, so try and match by a substring first
        } else {
            $route = null;
            $regex = false;
            $j = 0;
            $n = isset($_route[$i]) ? $_route[$i] : null;

            //Find the longest non-regex substring and match it against the URI
            while (true) {
                if (!isset($_route[$i])) {
                    break;
                } elseif (false === $regex) {
                    $c = $n;
                    $regex = $c === '[' || $c === '(' || $c === '.';
                    if (false === $regex && false !== isset($_route[$i+1])) {
                        $n = $_route[$i + 1];
                        $regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                    }
                    if (false === $regex && $c !== '/' && (!isset($uri[$j]) || $c !== $uri[$j])) {
                        continue 2;
                    }
                    $j++;
                }
                $route .= $_route[$i++];
            }

            //Check if there's a cached regex string
            if (false !== $apc) {
                $regex = apc_fetch("route:$route");
                if (false === $regex) {
                    $regex = compile_route($route);
                    apc_store("route:$route", $regex);
                }
            } else {
                $regex = compile_route($route);
            }

            $match = preg_match($regex, $uri, $params);
        }

        if ($match ^ $negate) {
            if (null !== $params) {
                $_REQUEST = array_merge($_REQUEST, $params);
            }
            try {
                $callback($request, $response, $app, $matched);
            } catch (Exception $e) {
                $response->error($e);
            }
            $matched = true;
        }
    }
    if (false === $matched) {
        $response->code(404);
    }
    if ($capture) {
        return ob_get_clean();
    } elseif ($response->isChunked()) {
        $response->chunk();
    } else {
        ob_end_flush();
    }
}

//Compiles a route string to a regular expression
function compile_route($route) {
    if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
        $match_types = array(
            'i'  => '[0-9]++',
            'a'  => '[0-9A-Za-z]++',
            'h'  => '[0-9A-Fa-f]++',
            '*'  => '.+?',
            '**' => '.++',
            ''   => '[^/]++'
        );
        foreach ($matches as $match) {
            list($block, $pre, $type, $param, $optional) = $match;

            if (isset($match_types[$type])) {
                $type = $match_types[$type];
            }
            if ($pre === '.') {
                $pre = '\.';
            }
            //Older versions of PCRE require the 'P' in (?P<named>)
            $pattern = '(?:'
                     . ($pre !== '' ? $pre : null)
                     . '('
                     . ($param !== '' ? "?P<$param>" : null)
                     . $type
                     . '))'
                     . ($optional !== '' ? '?' : null);

            $route = str_replace($block, $pattern, $route);
        }
        $route = "$route/?";
    }
    return "`^$route$`";
}

class _Request {

    //Returns all parameters (GET, POST, named) that match the mask
    public function params($mask = null) {
        $params = $_REQUEST;
        if (null !== $mask) {
            if (!is_array($mask)) {
                $mask = func_get_args();
            }
            $params = array_intersect_key($params, array_flip($mask));
            //Make sure each key in $mask has at least a null value
            foreach ($mask as $key) {
                if (!isset($params[$key])) {
                    $params[$key] = null;
                }
            }
        }
        return $params;
    }

    //Return a request parameter, or $default if it doesn't exist
    public function param($param, $default = null) {
        return isset($_REQUEST[$param]) && $_REQUEST[$param] !== '' ? $_REQUEST[$param] : $default;
    }

    public function __isset($param) {
        return isset($_REQUEST[$param]);
    }

    public function __get($param) {
        return isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;
    }

    public function __set($param, $value) {
        $_REQUEST[$param] = $value;
    }

    public function __unset($param) {
        unset($_REQUEST[$param]);
    }

    //Is the request secure? If $required then redirect to the secure version of the URL
    public function isSecure($required = false) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        if (!$secure && $required) {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $url);
        }
        return $secure;
    }

    //Gets a request header
    public function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-','_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    //Gets a request cookie
    public function cookie($cookie) {
        return isset($_COOKIE[$cookie]) ? $_COOKIE[$cookie] : null;
    }

    //Gets the request method, or checks it against $is - e.g. method('post') => true
    public function method($is = null) {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if (null !== $is) {
            return strcasecmp($method, $is) === 0;
        }
        return $method;
    }

    //Start a validator chain for the specified parameter
    public function validate($param, $err = null) {
        return new _Validator($this->param($param), $err);
    }

    //Gets a unique ID for the request
    public function id() {
        return sha1(mt_rand() . microtime(true) . mt_rand());
    }

    //Gets a session variable associated with the request
    public function session($key, $default = null) {
        if (session_id() === '') {
            session_start();
        }
        return isset($_SESSION[$key]) ? $key : $default;
    }

    //Gets the request IP address
    public function ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    //Gets the request user agent
    public function userAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    //Gets the request URI
    public function uri() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }
}

class _Response extends StdClass {

    protected $_chunked = false;
    protected $_errorCallbacks = array();

    //Enable response chunking. See: http://bit.ly/hg3gHb
    public function chunk($str = null) {
        if (false === $this->_chunked) {
            $this->_chunked = true;
            header('Transfer-encoding: chunked');
            flush();
        }
        if (null !== $str) {
            printf("%x\r\n", strlen($str));
            echo "$str\r\n";
            flush();
        } elseif (($ob_length = ob_get_length()) > 0) {
            printf("%x\r\n", $ob_length);
            ob_flush();
            echo "\r\n";
            flush();
        }
    }

    public function isChunked() {
        return $this->_chunked;
    }

    //Sets a response header
    public function header($key, $value = '') {
        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)));
        header("$key: $value");
    }

    //Sets a response cookie
    public function cookie($key, $value = '', $expiry = null, $path = '/',
            $domain = null, $secure = false, $httponly = false) {
        if (null === $expiry) {
            $expiry = time() + (3600 * 24 * 30);
        }
        return setcookie($key, $value, $expiry, $path, $domain, $secure, $httponly);
    }

    //Stores a flash message of $type
    public function flash($msg, $type = 'error', $params = null) {
        if (session_id() === '') {
            session_start();
        }
        if (is_array($type)) {
            $params = $type;
            $type = 'error';
        }
        if (!isset($_SESSION["__flash_$type"])) {
            $_SESSION["__flash_$type"] = array();
        }
        $_SESSION["__flash_$type"][] = $this->markdown($msg, $params);
    }

    //Support basic markdown syntax
    public function markdown($str/*, $arg1, $arg2, ...*/) {
        $args = func_get_args();
        $md = array(
            '/\[([^\]]++)\]\(([^\)]++)\)/' => '<a href="$2">$1</a>',
            '/\*\*([^\*]++)\*\*/'          => '<strong>$1</strong>',
            '/\*([^\*]++)\*/'              => '<em>$1</em>'
        );
        $str = array_shift($args);
        if (is_array($args[0])) {
            $args = $args[0];
        }
        foreach ($args as &$arg) {
            $arg = htmlentities($arg, ENT_QUOTES);
        }
        return vsprintf(preg_replace(array_keys($md), $md, $str), $args);
    }

    //Sends an object or file
    public function send($object, $type = 'json', $filename = null) {
        $this->discard();
        set_time_limit(1200);
        header("Pragma: no-cache");
        header('Cache-Control: no-store, no-cache');

        switch ($type) {
        case 'json':
            $json = json_encode($object);
            header('Content-Type: text/javascript; charset=utf-8');
            echo $json;
            exit;
        case 'csv':
            header('Content-type: text/csv; charset=utf-8');
            if (null === $filename) {
                $filename = 'output.csv';
            }
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            $columns = false;
            $escape = function ($value) { return str_replace('"', '""', $value); };
            foreach ($object as $row) {
                $row = (array)$row;
                if (!$columns && !isset($row[0])) {
                    echo '"' . implode('","', array_keys($row)) . '"' . "\n";
                    $columns = true;
                }
                echo '"' . implode('","', array_map($escape, array_values($row))) . '"' . "\n";
            }
            exit;
        case 'file':
            header('Content-type: ' . finfo_file(finfo_open(FILEINFO_MIME_TYPE), $object));
            header('Content-length: ' . filesize($object));
            if (null === $filename) {
                $filename = basename($object);
            }
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            fpassthru($object);
            exit;
        }
    }

    //Sends a HTTP response code
    public function code($code) {
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header("$protocol $code");
    }

    //Redirects the request to another URL
    public function redirect($url, $code = 302) {
        $this->code($code);
        header("Location: $url");
        exit;
    }

    //Redirects the request to the current URL
    public function refresh() {
        $this->redirect(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
    }

    //Redirects the request back to the referrer
    public function back() {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
        $this->refresh();
    }

    //Sets response properties/helpers
    public function set($key, $value = null) {
        if (!is_array($key)) {
            return $this->$key = $value;
        }
        foreach ($key as $k => $value) {
            $this->$k = $value;
        }
    }

    //Adds to or modifies the current query string
    public function query($new, $value = null) {
        $query = array();
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $query);
        }
        if (is_array($new)) {
            $query = array_merge($query, $new);
        } else {
            $query[$new] = $value;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        if (strpos($request_uri, '?') !== false) {
            $request_uri = strstr($request_uri, '?', true);
        }
        return $request_uri . (!empty($query) ? '?' . http_build_query($query) : null);
    }

    //Renders a view
    public function render($view, array $data = array(), $chunk = false) {
        if (!file_exists($view) || !is_readable($view)) {
            throw new ErrorException("Cannot render $view");
        }
        if (!empty($data)) {
            $this->set($data);
        }
        require $view;
        if (false !== $chunk) {
            $this->chunk();
        }
    }

    //Sets a session variable
    public function session($key, $value = null) {
        if (session_id() === '') {
            session_start();
        }
        return $_SESSION[$key] = $value;
    }

    //Adds an error callback to the stack of error handlers
    public function onError($callback) {
        $this->_errorCallbacks[] = $callback;
    }

    //Routes an exception through the error callbacks
    public function error(Exception $err) {
        $type = get_class($err);
        $msg = $err->getMessage();

        if (count($this->_errorCallbacks) > 0) {
            foreach (array_reverse($this->_errorCallbacks) as $callback) {
                if (is_callable($callback)) {
                    if ($callback($this, $msg, $type)) {
                        return;
                    }
                } else {
                    $this->flash($err);
                    $this->redirect($callback);
                }
            }
        } else {
            $this->code(500);
            throw new ErrorException($err);
        }
    }

    //Returns an escaped request paramater
    public function param($param, $default = null) {
        return isset($_REQUEST[$param]) ?  htmlentities($_REQUEST[$param], ENT_QUOTES) : $default;
    }

    //Returns (and clears) all flashes of $type
    public function getFlashes($type = 'error') {
        if (session_id() === '') {
            session_start();
        }
        if (isset($_SESSION["__flash_$type"])) {
            $flashes = $_SESSION["__flash_$type"];
            foreach ($flashes as $k => $flash) {
                $flashes[$k] = htmlentities($flash, ENT_QUOTES);
            }
            unset($_SESSION["__flash_$type"]);
            return $flashes;
        }
        return array();
    }

    //Escapes a string
    public function escape($str) {
        return htmlentities($str, ENT_QUOTES);
    }

    //Discards the current output buffer
    public function discard() {
        ob_end_clean();
    }

    //Flushes the current output buffer
    public function flush() {
        ob_end_flush();
    }

    //Return the current otuput buffer as a string (and optionally discard)
    public function outputBuffer($discard = false) {
        return $discard ? ob_get_clean() : ob_get_contents();
    }

    //Dump a variable
    public function dump($obj) {
        if (is_array($obj) || is_object($obj)) {
            $obj = print_r($obj, true);
        }
        $obj = htmlentities($obj, ENT_QUOTES);
        echo "<pre>$obj</pre><br />\n";
    }

    //Allow callbacks to be assigned as properties and called like normal methods
    public function __call($method, $args) {
        if (!isset($this->$method) || !is_callable($this->$method)) {
            throw new ErrorException("Unknown method $method()");
        }
        $callback = $this->$method;
        switch (count($args)) {
            case 1:  return $callback($args[0]);
            case 2:  return $callback($args[0], $args[1]);
            case 3:  return $callback($args[0], $args[1], $args[2]);
            case 4:  return $callback($args[0], $args[1], $args[2], $args[3]);
            default: return call_user_func_array($callback, $args);
        }
    }
}

function addValidator($method, $callback) {
    _Validator::$_methods[strtolower($method)] = $callback;
}

class ValidatorException extends Exception {}

class _Validator {

    public static $_methods = array();

    protected $_str = null;
    protected $_err = null;

    //Sets up the validator chain with the string and optional error message
    public function __construct($str, $err = null) {
        $this->_str = $str;
        $this->_err = $err;
        if (empty(static::$_defaultAdded)) {
            static::addDefault();
        }
    }

    //Adds default validators on first use. See README for usage details
    public static function addDefault() {
        static::$_methods['null'] = function($str) {
            return $str === null || $str === '';
        };
        static::$_methods['len'] = function($str, $min, $max = null) {
            $len = strlen($str);
            return null === $max ? $len === $min : $len >= $min && $len <= $max;
        };
        static::$_methods['int'] = function($str) {
            return (string)$str === ((string)(int)$str);
        };
        static::$_methods['float'] = function($str) {
            return (string)$str === ((string)(float)$str);
        };
        static::$_methods['email'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
        };
        static::$_methods['url'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_URL) !== false;
        };
        static::$_methods['ip'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_IP) !== false;
        };
        static::$_methods['alnum'] = function($str) {
            return ctype_alnum($str);
        };
        static::$_methods['alpha'] = function($str) {
            return ctype_alpha($str);
        };
        static::$_methods['contains'] = function($str, $needle) {
            return strpos($str, $needle) !== false;
        };
        static::$_methods['regex'] = function($str, $pattern) {
            return preg_match($pattern, $str);
        };
        static::$_methods['chars'] = function($str, $chars) {
            return preg_match("`^[$chars]++$`i", $str);
        };
    }

    public function __call($method, $args) {
        $reverse = false;
        $validator = $method;
        $method_substr = substr($method, 0, 2);

        if ($method_substr === 'is') {       //is<$validator>()
            $validator = substr($method, 2);
        } elseif ($method_substr === 'no') { //not<$validator>()
            $validator = substr($method, 3);
            $reverse = true;
        }
        $validator = strtolower($validator);

        if (!$validator || !isset(static::$_methods[$validator])) {
            throw new ErrorException("Unknown method $method()");
        }
        $validator = static::$_methods[$validator];
        array_unshift($args, $this->_str);

        switch (count($args)) {
            case 1:  $result = $validator($args[0]); break;
            case 2:  $result = $validator($args[0], $args[1]); break;
            case 3:  $result = $validator($args[0], $args[1], $args[2]); break;
            case 4:  $result = $validator($args[0], $args[1], $args[2], $args[3]); break;
            default: $result = call_user_func_array($validator, $args); break;
        }

        $result = (bool)($result ^ $reverse);
        if (false === $this->_err) {
            return $result;
        } elseif (false === $result) {
            throw new ValidatorException($this->_err);
        }
        return $this;
    }
}
