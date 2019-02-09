<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\route;

use think\Container;
use think\Request;
use think\Response;
use think\Route;
use think\route\dispatch\Callback as CallbackDispatch;
use think\route\dispatch\Controller as ControllerDispatch;
use think\route\dispatch\Redirect as RedirectDispatch;
use think\route\dispatch\Response as ResponseDispatch;
use think\route\dispatch\View as ViewDispatch;

abstract class Rule
{
    /**
     * 路由标识
     * @var string
     */
    protected $name;

    /**
     * 路由对象
     * @var Route
     */
    protected $router;

    /**
     * 路由所属分组
     * @var RuleGroup
     */
    protected $parent;

    /**
     * 路由规则
     * @var mixed
     */
    protected $rule;

    /**
     * 路由地址
     * @var string|\Closure
     */
    protected $route;

    /**
     * 请求类型
     * @var string
     */
    protected $method;

    /**
     * 路由变量
     * @var array
     */
    protected $vars = [];

    /**
     * 路由参数
     * @var array
     */
    protected $option = [];

    /**
     * 路由变量规则
     * @var array
     */
    protected $pattern = [];

    /**
     * 需要和分组合并的路由参数
     * @var array
     */
    protected $mergeOptions = ['after', 'model', 'header', 'response', 'append', 'middleware'];

    /**
     * 是否需要后置操作
     * @var bool
     */
    protected $doAfter = false;

    abstract public function check(Request $request, string $url, bool $completeMatch = false);

    /**
     * 设置路由参数
     * @access public
     * @param  array  $option  参数
     * @return $this
     */
    public function option(array $option)
    {
        $this->option = array_merge($this->option, $option);

        return $this;
    }

    /**
     * 设置单个路由参数
     * @access public
     * @param  string  $name  参数名
     * @param  mixed   $value 值
     * @return $this
     */
    public function setOption(string $name, $value)
    {
        $this->option[$name] = $value;

        return $this;
    }

    /**
     * 注册变量规则
     * @access public
     * @param  array  $pattern 变量规则
     * @return $this
     */
    public function pattern(array $pattern)
    {
        $this->pattern = array_merge($this->pattern, $pattern);

        return $this;
    }

    /**
     * 设置标识
     * @access public
     * @param  string  $name 标识名
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 获取路由对象
     * @access public
     * @return Route
     */
    public function getRouter(): Route
    {
        return $this->router;
    }

    /**
     * 获取Name
     * @access public
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取当前路由规则
     * @access public
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * 获取当前路由地址
     * @access public
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * 获取当前路由的变量
     * @access public
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * 获取Parent对象
     * @access public
     * @return $this|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * 获取路由所在域名
     * @access public
     * @return string
     */
    public function getDomain(): string
    {
        return $this->parent->getDomain();
    }

    /**
     * 获取路由参数
     * @access public
     * @param  string  $name 变量名
     * @return mixed
     */
    public function getConfig(string $name = '')
    {
        return $this->router->config($name);
    }

    /**
     * 获取变量规则定义
     * @access public
     * @param  string  $name 变量名
     * @return mixed
     */
    public function getPattern(string $name = '')
    {
        if ('' === $name) {
            return $this->pattern;
        }

        return $this->pattern[$name] ?? null;
    }

    /**
     * 获取路由参数定义
     * @access public
     * @param  string  $name 参数名
     * @return mixed
     */
    public function getOption(string $name = '')
    {
        if ('' === $name) {
            return $this->option;
        }

        return $this->option[$name] ?? null;
    }

    /**
     * 获取当前路由的请求类型
     * @access public
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($this->method);
    }

    /**
     * 路由是否有后置操作
     * @access public
     * @return bool
     */
    public function doAfter(): bool
    {
        return $this->doAfter;
    }

    /**
     * 设置路由请求类型
     * @access public
     * @param  string     $method
     * @return $this
     */
    public function method(string $method)
    {
        return $this->setOption('method', strtolower($method));
    }

    /**
     * 设置路由前置行为
     * @access public
     * @param  array|\Closure    $before
     * @return $this
     */
    public function before($before)
    {
        return $this->setOption('before', $before);
    }

    /**
     * 设置路由后置行为
     * @access public
     * @param  array|\Closure     $after
     * @return $this
     */
    public function after($after)
    {
        return $this->setOption('after', $after);
    }

    /**
     * 检查后缀
     * @access public
     * @param  string     $ext
     * @return $this
     */
    public function ext(string $ext = '')
    {
        return $this->setOption('ext', $ext);
    }

    /**
     * 检查禁止后缀
     * @access public
     * @param  string     $ext
     * @return $this
     */
    public function denyExt(string $ext = '')
    {
        return $this->setOption('deny_ext', $ext);
    }

    /**
     * 检查域名
     * @access public
     * @param  string     $domain
     * @return $this
     */
    public function domain(string $domain)
    {
        return $this->setOption('domain', $domain);
    }

    /**
     * 设置参数过滤检查
     * @access public
     * @param  array     $filter
     * @return $this
     */
    public function filter(array $filter)
    {
        $this->option['filter'] = $name;

        return $this;
    }

    /**
     * 绑定模型
     * @access public
     * @param  array|string      $var  路由变量名 多个使用 & 分割
     * @param  string|\Closure   $model 绑定模型类
     * @param  bool              $exception 是否抛出异常
     * @return $this
     */
    public function model($var, $model = null, bool $exception = true)
    {
        if ($var instanceof \Closure) {
            $this->option['model'][] = $var;
        } elseif (is_array($var)) {
            $this->option['model'] = $var;
        } elseif (is_null($model)) {
            $this->option['model']['id'] = [$var, true];
        } else {
            $this->option['model'][$var] = [$model, $exception];
        }

        return $this;
    }

    /**
     * 附加路由隐式参数
     * @access public
     * @param  array     $append
     * @return $this
     */
    public function append(array $append = [])
    {
        $this->option['append'] = $append;

        return $this;
    }

    /**
     * 绑定验证
     * @access public
     * @param  mixed    $validate 验证器类
     * @param  string   $scene 验证场景
     * @param  array    $message 验证提示
     * @param  bool     $batch 批量验证
     * @return $this
     */
    public function validate($validate, string $scene = null, array $message = [], bool $batch = false)
    {
        $this->option['validate'] = [$validate, $scene, $message, $batch];

        return $this;
    }

    /**
     * 绑定Response对象
     * @access public
     * @param  mixed     $response
     * @return $this
     */
    public function response($response)
    {
        $this->option['response'][] = $response;
        return $this;
    }

    /**
     * 设置Response Header信息
     * @access public
     * @param  array $header  头信息
     * @return $this
     */
    public function header(array $header)
    {
        $this->option['header'] = $header;

        return $this;
    }

    /**
     * 指定路由中间件
     * @access public
     * @param  string|array|\Closure    $middleware
     * @param  mixed                    $param
     * @return $this
     */
    public function middleware($middleware, $param = null)
    {
        if (is_null($param) && is_array($middleware)) {
            $this->option['middleware'] = $middleware;
        } else {
            foreach ((array) $middleware as $item) {
                $this->option['middleware'][] = [$item, $param];
            }
        }

        return $this;
    }

    /**
     * 设置路由缓存
     * @access public
     * @param  array|string     $cache
     * @return $this
     */
    public function cache($cache)
    {
        return $this->setOption('cache', $cache);
    }

    /**
     * 检查URL分隔符
     * @access public
     * @param  string     $depr
     * @return $this
     */
    public function depr(string $depr)
    {
        return $this->setOption('param_depr', $depr);
    }

    /**
     * 设置需要合并的路由参数
     * @access public
     * @param  array     $option
     * @return $this
     */
    public function mergeOptions(array $option = [])
    {
        $this->mergeOptions = array_merge($this->mergeOptions, $option);
        return $this;
    }

    /**
     * 检查是否为HTTPS请求
     * @access public
     * @param  bool     $https
     * @return $this
     */
    public function https(bool $https = true)
    {
        return $this->setOption('https', $https);
    }

    /**
     * 检查是否为AJAX请求
     * @access public
     * @param  bool     $ajax
     * @return $this
     */
    public function ajax(bool $ajax = true)
    {
        return $this->setOption('ajax', $ajax);
    }

    /**
     * 检查是否为PJAX请求
     * @access public
     * @param  bool     $pjax
     * @return $this
     */
    public function pjax(bool $pjax = true)
    {
        return $this->setOption('pjax', $pjax);
    }

    /**
     * 当前路由到一个模板地址 当使用数组的时候可以传入模板变量
     * @access public
     * @param  bool|array     $view
     * @return $this
     */
    public function view($view = true)
    {
        return $this->setOption('view', $view);
    }

    /**
     * 当前路由为重定向
     * @access public
     * @param  bool   $redirect 是否为重定向
     * @return $this
     */
    public function redirect(bool $redirect = true)
    {
        return $this->setOption('redirect', $redirect);
    }

    /**
     * 设置路由完整匹配
     * @access public
     * @param  bool     $match
     * @return $this
     */
    public function completeMatch(bool $match = true)
    {
        return $this->setOption('complete_match', $match);
    }

    /**
     * 是否去除URL最后的斜线
     * @access public
     * @param  bool     $remove
     * @return $this
     */
    public function removeSlash(bool $remove = true)
    {
        return $this->setOption('remove_slash', $remove);
    }

    /**
     * 设置是否允许跨域
     * @access public
     * @param  bool     $allow
     * @param  array    $header
     * @return $this
     */
    public function allowCrossDomain(bool $allow = true, array $header = [])
    {
        if (!empty($header)) {
            $this->header($header);
        }

        if ($allow && $this->parent) {
            $this->parent->addRuleItem($this, 'options');
        }

        return $this->setOption('cross_domain', $allow);
    }

    /**
     * 检查OPTIONS请求
     * @access public
     * @param  Request     $request
     * @return Dispatch|void
     */
    protected function checkCrossDomain(Request $request)
    {
        if (!empty($this->option['cross_domain'])) {

            $header = [
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
            ];

            if (!empty($this->option['header'])) {
                $header = array_merge($header, $this->option['header']);
            }

            $this->option['header'] = $header;

            if ($request->method(true) == 'OPTIONS') {
                return new ResponseDispatch($request, $this, Response::create()->code(204)->header($header));
            }
        }
    }

    /**
     * 设置路由规则全局有效
     * @access public
     * @return $this
     */
    public function crossDomainRule()
    {
        if ($this instanceof RuleGroup) {
            $method = '*';
        } else {
            $method = $this->method;
        }

        $this->router->setCrossDomainRule($this, $method);

        return $this;
    }

    /**
     * 合并分组参数
     * @access public
     * @return array
     */
    public function mergeGroupOptions(): array
    {
        $parentOption = $this->parent->getOption();
        // 合并分组参数
        foreach ($this->mergeOptions as $item) {
            if (isset($parentOption[$item]) && isset($this->option[$item])) {
                $this->option[$item] = array_merge($parentOption[$item], $this->option[$item]);
            }
        }

        $this->option = array_merge($parentOption, $this->option);

        return $this->option;
    }

    /**
     * 解析匹配到的规则路由
     * @access public
     * @param  Request   $request 请求对象
     * @param  string    $rule 路由规则
     * @param  string    $route 路由地址
     * @param  string    $url URL地址
     * @param  array     $option 路由参数
     * @param  array     $matches 匹配的变量
     * @return Dispatch
     */
    public function parseRule(Request $request, string $rule, $route, string $url, array $option = [], array $matches = []): Dispatch
    {
        if (is_string($route) && isset($option['prefix'])) {
            // 路由地址前缀
            $route = $option['prefix'] . $route;
        }

        // 替换路由地址中的变量
        if (is_string($route) && !empty($matches)) {
            $search = $replace = [];

            foreach ($matches as $key => $value) {
                $search[]  = '<' . $key . '>';
                $replace[] = $value;

                $search[]  = ':' . $key;
                $replace[] = $value;
            }

            $route = str_replace($search, $replace, $route);
        }

        // 解析额外参数
        $count = substr_count($rule, '/');
        $url   = array_slice(explode('|', $url), $count + 1);
        $this->parseUrlParams(implode('|', $url), $matches);

        $this->route   = $route;
        $this->vars    = $matches;
        $this->option  = $option;
        $this->doAfter = true;

        // 发起路由调度
        return $this->dispatch($request, $route, $option);
    }

    /**
     * 发起路由调度
     * @access protected
     * @param  Request   $request Request对象
     * @param  mixed     $route  路由地址
     * @param  array     $option 路由参数
     * @return Dispatch
     */
    protected function dispatch(Request $request, $route, array $option): Dispatch
    {
        if ($route instanceof Dispatch) {
            $result = $route;
        } elseif ($route instanceof \Closure) {
            // 执行闭包
            $result = new CallbackDispatch($request, $this, $route);
        } elseif ($route instanceof Response) {
            $result = new ResponseDispatch($request, $this, $route);
        } elseif (isset($option['view']) && false !== $option['view']) {
            $result = new ViewDispatch($request, $this, $route, is_array($option['view']) ? $option['view'] : []);
        } elseif (!empty($option['redirect']) || 0 === strpos($route, '/') || strpos($route, '://')) {
            // 路由到重定向地址
            $result = new RedirectDispatch($request, $this, $route, [], isset($option['status']) ? $option['status'] : 301);
        } elseif (false !== strpos($route, '\\')) {
            // 路由到类的方法
            $result = $this->dispatchMethod($request, $route);
        } else {
            // 路由到控制器/操作
            $result = $this->dispatchController($request, $route);
        }

        return $result;
    }

    /**
     * 解析URL地址为 模块/控制器/操作
     * @access protected
     * @param  Request   $request Request对象
     * @param  string    $route 路由地址
     * @return CallbackDispatch
     */
    protected function dispatchMethod(Request $request, string $route): CallbackDispatch
    {
        list($path, $var) = $this->parseUrlPath($route);

        $route  = str_replace('/', '@', implode('/', $path));
        $method = strpos($route, '@') ? explode('@', $route) : $route;

        return new CallbackDispatch($request, $this, $method, $var);
    }

    /**
     * 解析URL地址为 模块/控制器/操作
     * @access protected
     * @param  Request   $request Request对象
     * @param  string    $route 路由地址
     * @return ControllerDispatch
     */
    protected function dispatchController(Request $request, string $route): ControllerDispatch
    {
        list($path, $var) = $this->parseUrlPath($route);

        $action     = array_pop($path);
        $controller = !empty($path) ? array_pop($path) : null;

        // 路由到模块/控制器/操作
        return new ControllerDispatch($request, $this, [$controller, $action], $var);
    }

    /**
     * 路由检查
     * @access protected
     * @param  array     $option 路由参数
     * @param  Request   $request Request对象
     * @return bool
     */
    protected function checkOption(array $option, Request $request): bool
    {
        // 请求类型检测
        if (!empty($option['method'])) {
            if (is_string($option['method']) && false === stripos($option['method'], $request->method())) {
                return false;
            }
        }

        // AJAX PJAX 请求检查
        foreach (['ajax', 'pjax'] as $item) {
            if (isset($option[$item])) {
                $call = 'is' . $item;
                if ($option[$item] && !$request->$call() || !$option[$item] && $request->$call()) {
                    return false;
                }
            }
        }

        // 伪静态后缀检测
        if ($request->url() != '/' && ((isset($option['ext']) && false === stripos('|' . $option['ext'] . '|', '|' . $request->ext() . '|'))
            || (isset($option['deny_ext']) && false !== stripos('|' . $option['deny_ext'] . '|', '|' . $request->ext() . '|')))) {
            return false;
        }

        // 域名检查
        if ((isset($option['domain']) && !in_array($option['domain'], [$request->host(true), $request->subDomain()]))) {
            return false;
        }

        // HTTPS检查
        if ((isset($option['https']) && $option['https'] && !$request->isSsl())
            || (isset($option['https']) && !$option['https'] && $request->isSsl())) {
            return false;
        }

        // 请求参数检查
        if (isset($option['filter'])) {
            foreach ($option['filter'] as $name => $value) {
                if ($request->param($name, '', null) != $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 解析URL地址中的参数Request对象
     * @access protected
     * @param  string    $rule 路由规则
     * @param  array     $var 变量
     * @return void
     */
    protected function parseUrlParams(string $url, array &$var = []): void
    {
        if ($url) {
            preg_replace_callback('/(\w+)\|([^\|]+)/', function ($match) use (&$var) {
                $var[$match[1]] = strip_tags($match[2]);
            }, $url);
        }
    }

    /**
     * 解析URL的pathinfo参数和变量
     * @access public
     * @param  string    $url URL地址
     * @return array
     */
    public function parseUrlPath(string $url): array
    {
        // 分隔符替换 确保路由定义使用统一的分隔符
        $url = str_replace('|', '/', $url);
        $url = trim($url, '/');
        $var = [];

        if (false !== strpos($url, '?')) {
            // [控制器/操作?]参数1=值1&参数2=值2...
            $info = parse_url($url);
            $path = explode('/', $info['path']);
            parse_str($info['query'], $var);
        } elseif (strpos($url, '/')) {
            // [控制器/操作]
            $path = explode('/', $url);
        } elseif (false !== strpos($url, '=')) {
            // 参数1=值1&参数2=值2...
            parse_str($url, $var);
            $path = [];
        } else {
            $path = [$url];
        }

        return [$path, $var];
    }

    /**
     * 生成路由的正则规则
     * @access protected
     * @param  string    $rule 路由规则
     * @param  array     $match 匹配的变量
     * @param  array     $pattern   路由变量规则
     * @param  array     $option    路由参数
     * @param  bool      $completeMatch   路由是否完全匹配
     * @param  string    $suffix   路由正则变量后缀
     * @return string
     */
    protected function buildRuleRegex(string $rule, array $match, array $pattern = [], array $option = [], bool $completeMatch = false, string $suffix = ''): string
    {
        foreach ($match as $name) {
            $replace[] = $this->buildNameRegex($name, $pattern, $suffix);
        }

        // 是否区分 / 地址访问
        if ('/' != $rule) {
            if (!empty($option['remove_slash'])) {
                $rule = rtrim($rule, '/');
            } elseif (substr($rule, -1) == '/') {
                $rule     = rtrim($rule, '/');
                $hasSlash = true;
            }
        }

        $regex = str_replace(array_unique($match), array_unique($replace), $rule);
        $regex = str_replace([')?/', ')/', ')?-', ')-', '\\\\/'], [')\/', ')\/', ')\-', ')\-', '\/'], $regex);

        if (isset($hasSlash)) {
            $regex .= '\/';
        }

        return $regex . ($completeMatch ? '$' : '');
    }

    /**
     * 生成路由变量的正则规则
     * @access protected
     * @param  string    $name      路由变量
     * @param  array     $pattern   变量规则
     * @param  string    $suffix    路由正则变量后缀
     * @return string
     */
    protected function buildNameRegex(string $name, array $pattern, string $suffix): string
    {
        $optional = '';
        $slash    = substr($name, 0, 1);

        if (in_array($slash, ['/', '-'])) {
            $prefix = '\\' . $slash;
            $name   = substr($name, 1);
            $slash  = substr($name, 0, 1);
        } else {
            $prefix = '';
        }

        if ('<' != $slash) {
            return $prefix . preg_quote($name, '/');
        }

        if (strpos($name, '?')) {
            $name     = substr($name, 1, -2);
            $optional = '?';
        } elseif (strpos($name, '>')) {
            $name = substr($name, 1, -1);
        }

        if (isset($pattern[$name])) {
            $nameRule = $pattern[$name];
            if (0 === strpos($nameRule, '/') && '/' == substr($nameRule, -1)) {
                $nameRule = substr($nameRule, 1, -1);
            }
        } else {
            $nameRule = $this->router->config('default_route_pattern');
        }

        return '(' . $prefix . '(?<' . $name . $suffix . '>' . $nameRule . '))' . $optional;
    }

    /**
     * 分析路由规则中的变量
     * @access protected
     * @param  string    $rule 路由规则
     * @return array
     */
    protected function parseVar(string $rule): array
    {
        // 提取路由规则中的变量
        $var = [];

        if (preg_match_all('/<\w+\??>/', $rule, $matches)) {
            foreach ($matches[0] as $name) {
                $optional = false;

                if (strpos($name, '?')) {
                    $name     = substr($name, 1, -2);
                    $optional = true;
                } else {
                    $name = substr($name, 1, -1);
                }

                $var[$name] = $optional ? 2 : 1;
            }
        }

        return $var;
    }

    /**
     * 设置路由参数
     * @access public
     * @param  string    $method     方法名
     * @param  array     $args       调用参数
     * @return $this
     */
    public function __call($method, $args)
    {
        if (count($args) > 1) {
            $args[0] = $args;
        }
        array_unshift($args, $method);

        return call_user_func_array([$this, 'option'], $args);
    }

    public function __sleep()
    {
        return ['name', 'rule', 'route', 'method', 'vars', 'option', 'pattern', 'doAfter'];
    }

    public function __wakeup()
    {
        $this->router = Container::pull('route');
    }
}
