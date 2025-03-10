<?php
class Routing
{
    private $params = ["url" => ""];

    function process($qs = "")
    {
        $params = [];
        parse_str($qs, $params);
        $this->params = array_merge($this->params, $params);
        $url = rtrim($this->params["url"], "/");
        $path = explode("/", $url);
        $this->match($path);
    }

    private function match($path)
    {
        $controller = ($path[0] != "" ? ucfirst($path[0]) :   "Main") . "Controller";

        $action = $path[1] ?? "index";

        if (class_exists($controller)) {

            $controller_object = new $controller();

            if (is_callable([$controller_object, $action])) {
                $controller_object->$action();
            } else {
                print_r($path);
                throw new \Exception("Method \"$action\" in \"$controller\" not found", 404);
            }
        } else {
            throw new \Exception("path does not exist", 404);
        }
    }
    
}
